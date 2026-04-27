import fs from 'node:fs/promises';
import path from 'node:path';
import process from 'node:process';
import ExcelJS from 'exceljs';
import puppeteer from 'puppeteer';

function parseArgs(argv) {
  const out = {
    accounts: ['kasyaraa.co', 'kasyaraa.catalog'],
    maxPosts: 120,
    output: path.resolve('tools/ig_catalog/output/katalog_instagram.xlsx'),
    imageDir: path.resolve('tools/ig_catalog/output/images'),
    userDataDir: path.resolve('tools/ig_catalog/chrome-profile'),
    headless: false,
    cookieHeader: '',
  };

  for (let i = 0; i < argv.length; i += 1) {
    const a = argv[i];
    if (a === '--accounts') {
      const next = argv[i + 1] || '';
      out.accounts = next
        .split(',')
        .map((v) => normalizeAccount(v))
        .filter(Boolean);
      i += 1;
    } else if (a === '--max-posts') {
      out.maxPosts = Number(argv[i + 1] || out.maxPosts);
      i += 1;
    } else if (a === '--output') {
      out.output = path.resolve(argv[i + 1] || out.output);
      i += 1;
    } else if (a === '--image-dir') {
      out.imageDir = path.resolve(argv[i + 1] || out.imageDir);
      i += 1;
    } else if (a === '--user-data-dir') {
      out.userDataDir = path.resolve(argv[i + 1] || out.userDataDir);
      i += 1;
    } else if (a === '--headless') {
      out.headless = (argv[i + 1] || 'false').toLowerCase() === 'true';
      i += 1;
    } else if (a === '--cookie-header') {
      out.cookieHeader = argv[i + 1] || '';
      i += 1;
    }
  }

  out.accounts = out.accounts.map((v) => normalizeAccount(v)).filter(Boolean);
  return out;
}

function normalizeAccount(value) {
  if (!value) return '';
  const raw = String(value).trim();
  if (raw.startsWith('http://') || raw.startsWith('https://')) {
    try {
      const u = new URL(raw);
      const p = u.pathname.split('/').filter(Boolean);
      return (p[0] || '').replace('@', '');
    } catch {
      return raw.replace('@', '');
    }
  }
  return raw.replace('@', '');
}

function parseCookieHeader(cookieHeader) {
  if (!cookieHeader || !cookieHeader.trim()) return [];
  return cookieHeader
    .split(';')
    .map((part) => part.trim())
    .filter(Boolean)
    .map((part) => {
      const eq = part.indexOf('=');
      if (eq <= 0) return null;
      const name = part.slice(0, eq).trim();
      const value = part.slice(eq + 1).trim();
      return {
        name,
        value,
        domain: '.instagram.com',
        path: '/',
        secure: true,
      };
    })
    .filter(Boolean);
}

function sleep(ms) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}

function cleanCaption(text = '') {
  return text.replace(/\r/g, '\n').replace(/\n{3,}/g, '\n\n').trim();
}

const CATEGORY_RULES = {
  dress: ['dress', 'gamis', 'abaya', 'tunik'],
  set: ['set', 'setelan', 'one set', '2in1', '2 in 1', 'twinset'],
  atasan: ['blouse', 'kemeja', 'shirt', 'top', 'atasan', 'outer', 'cardigan'],
  bawahan: ['celana', 'rok', 'pants', 'skirt', 'kulot', 'legging'],
  hijab: ['hijab', 'pashmina', 'bergo', 'khimar', 'scarf'],
  aksesoris: ['tas', 'bag', 'sepatu', 'sandal', 'belt', 'bros', 'pin', 'kaos kaki'],
};

function inferCategory(caption = '') {
  const low = caption.toLowerCase();
  let best = 'lainnya';
  let bestScore = 0;

  Object.entries(CATEGORY_RULES).forEach(([category, keywords]) => {
    const score = keywords.reduce((acc, kw) => (low.includes(kw) ? acc + 1 : acc), 0);
    if (score > bestScore) {
      bestScore = score;
      best = category;
    }
  });

  return best;
}

function inferVariant(caption = '') {
  const patterns = [
    /\b(xs|s|m|l|xl|xxl|xxxl)\b/gi,
    /\bsize\s*[:\-]?\s*([a-z0-9, /-]+)/gi,
    /\bwarna\s*[:\-]?\s*([a-z0-9, /-]+)/gi,
    /\bcolor\s*[:\-]?\s*([a-z0-9, /-]+)/gi,
    /\bvarian\s*[:\-]?\s*([a-z0-9, /-]+)/gi,
  ];

  const hits = [];
  patterns.forEach((re) => {
    let m;
    while ((m = re.exec(caption)) !== null) {
      hits.push((m[1] || m[0] || '').trim());
    }
  });

  const unique = [];
  const seen = new Set();
  hits.forEach((h) => {
    const norm = h.replace(/\s+/g, ' ').trim().replace(/^[, .-]+|[, .-]+$/g, '');
    if (!norm) return;
    const key = norm.toLowerCase();
    if (!seen.has(key)) {
      seen.add(key);
      unique.push(norm);
    }
  });

  return unique.length ? unique.slice(0, 5).join(' | ') : '-';
}

function inferProduct(caption = '', fallback = '-') {
  const lines = caption
    .split('\n')
    .map((l) => l.trim())
    .filter(Boolean);
  for (const line of lines) {
    if (line.startsWith('#') || line.startsWith('@')) continue;
    const cleaned = line.replace(/https?:\/\/\S+/g, '').trim();
    if (cleaned.length >= 3) {
      return cleaned.length > 80 ? `${cleaned.slice(0, 77)}...` : cleaned;
    }
  }
  return fallback;
}

async function ensureLoggedIn(page) {
  await page.goto('https://www.instagram.com/', { waitUntil: 'domcontentloaded', timeout: 60000 });
  await sleep(2500);

  for (let i = 0; i < 60; i += 1) {
    const hasLoginForm = await page.$('input[name="username"]');
    if (!hasLoginForm) {
      return true;
    }

    if (i === 0) {
      console.log('Silakan login Instagram di browser Puppeteer yang kebuka. Menunggu sampai login selesai...');
    }
    await sleep(3000);
    await page.reload({ waitUntil: 'domcontentloaded' }).catch(() => {});
  }

  throw new Error('Login Instagram tidak terdeteksi. Coba jalankan ulang script dan login dulu.');
}

function extractMediaNodesDeep(payload) {
  const result = [];
  const stack = [payload];
  const seenObj = new Set();

  while (stack.length) {
    const item = stack.pop();
    if (!item || typeof item !== 'object') continue;
    if (seenObj.has(item)) continue;
    seenObj.add(item);

    if (Array.isArray(item)) {
      item.forEach((v) => stack.push(v));
      continue;
    }

    const hasShortcode = typeof item.shortcode === 'string' && item.shortcode.length > 0;
    const hasMediaUrl =
      typeof item.display_url === 'string' ||
      typeof item.thumbnail_src === 'string' ||
      typeof item.display_uri === 'string' ||
      typeof item.image_url === 'string' ||
      item.image_versions2?.candidates?.[0]?.url;

    if (hasShortcode && hasMediaUrl) {
      result.push(item);
    }

    Object.values(item).forEach((v) => stack.push(v));
  }

  return result;
}

function normalizePostFromNode(node, account) {
  const shortcode = node.shortcode || '';
  const caption =
    cleanCaption(
      node.edge_media_to_caption?.edges?.[0]?.node?.text ||
        node.caption?.text ||
        node.accessibility_caption ||
        ''
    ) || '';

  const takenTs = node.taken_at_timestamp || node.taken_at || null;
  const tanggal = takenTs
    ? new Date(Number(takenTs) * (String(takenTs).length <= 10 ? 1000 : 1))
        .toISOString()
        .replace('T', ' ')
        .slice(0, 19)
    : '';

  const imageUrl =
    node.display_url ||
    node.thumbnail_src ||
    node.display_uri ||
    node.image_url ||
    node.image_versions2?.candidates?.[0]?.url ||
    '';

  return {
    akun: account,
    shortcode,
    tanggal,
    url_post: shortcode ? `https://www.instagram.com/p/${shortcode}/` : '',
    caption,
    image_url: imageUrl,
    produk: inferProduct(caption, shortcode || '-'),
    kategori: inferCategory(caption),
    varian: inferVariant(caption),
  };
}

async function collectPostsFromGraphql(page, account, maxPosts) {
  const profileUrl = `https://www.instagram.com/${account}/`;
  const postsByCode = new Map();
  let stagnant = 0;
  let lastCount = postsByCode.size;

  const handler = async (res) => {
    const url = res.url();
    if (!url.includes('/graphql/query')) return;
    if (res.status() >= 400) return;

    let payload;
    try {
      payload = await res.json();
    } catch {
      return;
    }

    const nodes = extractMediaNodesDeep(payload);
    nodes.forEach((node) => {
      const post = normalizePostFromNode(node, account);
      if (post.shortcode && post.image_url && !postsByCode.has(post.shortcode)) {
        postsByCode.set(post.shortcode, post);
      }
    });
  };

  page.on('response', handler);
  await page.goto(profileUrl, { waitUntil: 'networkidle2', timeout: 60000 });
  await sleep(3000);

  for (let i = 0; i < 40; i += 1) {
    if (postsByCode.size >= maxPosts) break;

    if (postsByCode.size === lastCount) stagnant += 1;
    else stagnant = 0;

    if (stagnant >= 5) break;
    lastCount = postsByCode.size;

    await page.mouse.wheel({ deltaY: 2200 });
    await sleep(1600 + Math.floor(Math.random() * 1300));
  }

  page.off('response', handler);
  return Array.from(postsByCode.values()).slice(0, maxPosts);
}

async function downloadImage(url, destPath) {
  if (!url) return false;
  try {
    const res = await fetch(url, { headers: { 'user-agent': 'Mozilla/5.0' } });
    if (!res.ok) return false;
    const arr = await res.arrayBuffer();
    await fs.mkdir(path.dirname(destPath), { recursive: true });
    await fs.writeFile(destPath, Buffer.from(arr));
    return true;
  } catch {
    return false;
  }
}

async function writeExcel(rows, outputPath) {
  const wb = new ExcelJS.Workbook();
  const ws = wb.addWorksheet('katalog');

  ws.columns = [
    { header: 'no', key: 'no', width: 6 },
    { header: 'gambar', key: 'gambar', width: 20 },
    { header: 'akun', key: 'akun', width: 20 },
    { header: 'shortcode', key: 'shortcode', width: 16 },
    { header: 'tanggal', key: 'tanggal', width: 22 },
    { header: 'produk', key: 'produk', width: 42 },
    { header: 'kategori', key: 'kategori', width: 16 },
    { header: 'varian', key: 'varian', width: 30 },
    { header: 'url_post', key: 'url_post', width: 48 },
    { header: 'caption', key: 'caption', width: 90 },
  ];

  ws.getRow(1).font = { bold: true };

  for (let i = 0; i < rows.length; i += 1) {
    const r = rows[i];
    const rowIndex = i + 2;
    ws.addRow({
      no: i + 1,
      akun: r.akun,
      shortcode: r.shortcode,
      tanggal: r.tanggal,
      produk: r.produk,
      kategori: r.kategori,
      varian: r.varian,
      url_post: r.url_post,
      caption: r.caption,
    });

    ws.getRow(rowIndex).height = 80;
    [6, 7, 8, 9, 10].forEach((c) => {
      ws.getCell(rowIndex, c).alignment = { vertical: 'top', wrapText: true };
    });

    if (r.image_path) {
      try {
        const ext = r.image_path.toLowerCase().endsWith('.png') ? 'png' : 'jpeg';
        const imgId = wb.addImage({ filename: r.image_path, extension: ext });
        ws.addImage(imgId, {
          tl: { col: 1.15, row: rowIndex - 0.9 },
          ext: { width: 96, height: 96 },
          editAs: 'oneCell',
        });
      } catch {
        // Ignore image failures; keep row data.
      }
    }
  }

  await fs.mkdir(path.dirname(outputPath), { recursive: true });
  await wb.xlsx.writeFile(outputPath);
}

async function main() {
  const args = parseArgs(process.argv.slice(2));
  if (!args.accounts.length) {
    throw new Error('Akun kosong. Isi --accounts "user1,user2"');
  }

  await fs.mkdir(args.imageDir, { recursive: true });

  const browser = await puppeteer.launch({
    headless: args.headless,
    userDataDir: args.userDataDir,
    defaultViewport: { width: 1366, height: 900 },
    args: ['--disable-blink-features=AutomationControlled'],
  });

  const page = await browser.newPage();
  page.setDefaultTimeout(60000);
  await page.setUserAgent(
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36'
  );

  try {
    const cookies = parseCookieHeader(args.cookieHeader);
    if (cookies.length) {
      await page.setCookie(...cookies);
    }

    await ensureLoggedIn(page);

    const rows = [];

    for (const account of args.accounts) {
      console.log(`Ambil data post: ${account}`);
      const posts = await collectPostsFromGraphql(page, account, args.maxPosts);
      console.log(`Dapat ${posts.length} post dari ${account}`);

      for (let i = 0; i < posts.length; i += 1) {
        const detail = posts[i];
        const ext = (detail.image_url || '').includes('.png') ? '.png' : '.jpg';
        const filename = `${detail.shortcode || `post_${i + 1}`}${ext}`;
        const localImage = path.join(args.imageDir, account, filename);
        const ok = await downloadImage(detail.image_url, localImage);

        rows.push({
          ...detail,
          image_path: ok ? localImage : '',
        });

        if ((i + 1) % 10 === 0 || i === posts.length - 1) {
          console.log(`${account}: ${i + 1}/${posts.length}`);
        }

        await sleep(350 + Math.floor(Math.random() * 450));
      }
    }

    rows.sort((a, b) => (b.tanggal || '').localeCompare(a.tanggal || ''));

    await writeExcel(rows, args.output);
    console.log(`\nOK. Total baris: ${rows.length}`);
    console.log(`Excel: ${args.output}`);
  } finally {
    await page.close().catch(() => {});
    await browser.close().catch(() => {});
  }
}

main().catch((err) => {
  console.error(err?.stack || err);
  process.exit(1);
});
