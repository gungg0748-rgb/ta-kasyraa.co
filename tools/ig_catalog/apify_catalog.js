import fs from 'node:fs/promises';
import path from 'node:path';
import process from 'node:process';
import ExcelJS from 'exceljs';
import { ApifyClient } from 'apify-client';

function parseArgs(argv) {
  const out = {
    accounts: ['kasyaraa.co', 'kasyaraa.catalog'],
    maxPosts: 200,
    output: path.resolve('tools/ig_catalog/output/katalog_instagram.xlsx'),
    imageDir: path.resolve('tools/ig_catalog/output/images'),
    actor: 'apify/instagram-scraper',
  };

  for (let i = 0; i < argv.length; i += 1) {
    const a = argv[i];
    if (a === '--accounts') {
      out.accounts = (argv[i + 1] || '')
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
    } else if (a === '--actor') {
      out.actor = argv[i + 1] || out.actor;
      i += 1;
    }
  }

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

function cleanCaption(text = '') {
  return String(text).replace(/\r/g, '\n').replace(/\n{3,}/g, '\n\n').trim();
}

const SCRIPSI_CATEGORY_ORDER = [
  'Kategori Dress & Gamis',
  'Kategori Setelan',
  'Kategori Atasan & Outer',
  'Kategori Bawahan',
  'Kategori Hijab',
  'Kategori Aksesoris',
  'Kategori Lainnya',
];

const PRODUCT_HINTS = [
  'dress',
  'gamis',
  'tunik',
  'set',
  'setelan',
  'atasan',
  'outer',
  'rok',
  'celana',
  'hijab',
  'pashmina',
  'bergo',
  'abaya',
  'motif',
  'warna',
  'size',
  'new collection',
];

const NON_PRODUCT_HINTS = [
  'live',
  'siaran langsung',
  'behind the scene',
  'bts',
  'giveaway',
  'testimoni',
  'testimonial',
  'opening',
  'coming soon',
];

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

  for (const [category, keywords] of Object.entries(CATEGORY_RULES)) {
    const score = keywords.reduce((acc, kw) => (low.includes(kw) ? acc + 1 : acc), 0);
    if (score > bestScore) {
      bestScore = score;
      best = category;
    }
  }

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
  for (const re of patterns) {
    let m;
    while ((m = re.exec(caption)) !== null) {
      hits.push((m[1] || m[0] || '').trim());
    }
  }

  const unique = [];
  const seen = new Set();
  for (const h of hits) {
    const norm = h.replace(/\s+/g, ' ').trim().replace(/^[, .-]+|[, .-]+$/g, '');
    if (!norm) continue;
    const key = norm.toLowerCase();
    if (!seen.has(key)) {
      seen.add(key);
      unique.push(norm);
    }
  }

  return unique.length ? unique.slice(0, 5).join(' | ') : '-';
}

function extractPrice(caption = '') {
  const text = caption.replace(/\n/g, ' ');
  const rpMatch =
    text.match(/(?:rp|idr)\s*([0-9][0-9., ]{3,})/i) ||
    text.match(/\b([0-9]{3,}(?:[.,][0-9]{3})+)\b/);
  if (!rpMatch) return '-';

  const digits = (rpMatch[1] || '').replace(/\D/g, '');
  if (!digits || digits.length < 4) return '-';
  const value = Number(digits);
  if (!Number.isFinite(value)) return '-';
  return value.toLocaleString('en-US');
}

function inferJenisTenun(text = '') {
  const low = text.toLowerCase();
  if (low.includes('dress') || low.includes('gamis') || low.includes('abaya') || low.includes('tunik')) return 'Dress/Gamis';
  if (low.includes('set') || low.includes('setelan') || low.includes('one set')) return 'Setelan';
  if (low.includes('atasan') || low.includes('blouse') || low.includes('shirt') || low.includes('outer') || low.includes('cardigan')) return 'Atasan/Outer';
  if (low.includes('rok') || low.includes('celana') || low.includes('pants') || low.includes('skirt')) return 'Bawahan';
  if (low.includes('hijab') || low.includes('pashmina') || low.includes('bergo') || low.includes('khimar')) return 'Hijab';
  if (low.includes('tas') || low.includes('bag') || low.includes('sepatu') || low.includes('sandal') || low.includes('aksesori')) return 'Aksesoris';
  return 'Produk Fashion';
}

function inferSkripsiCategory(text = '') {
  const low = text.toLowerCase();
  if (low.includes('dress') || low.includes('gamis') || low.includes('abaya') || low.includes('tunik')) return 'Kategori Dress & Gamis';
  if (low.includes('set') || low.includes('setelan') || low.includes('one set') || low.includes('2in1')) return 'Kategori Setelan';
  if (low.includes('atasan') || low.includes('blouse') || low.includes('shirt') || low.includes('outer') || low.includes('cardigan')) return 'Kategori Atasan & Outer';
  if (low.includes('rok') || low.includes('celana') || low.includes('pants') || low.includes('skirt') || low.includes('kulot')) return 'Kategori Bawahan';
  if (low.includes('hijab') || low.includes('pashmina') || low.includes('bergo') || low.includes('khimar') || low.includes('scarf')) return 'Kategori Hijab';
  if (low.includes('tas') || low.includes('bag') || low.includes('sepatu') || low.includes('sandal') || low.includes('aksesori')) return 'Kategori Aksesoris';
  return 'Kategori Lainnya';
}

function inferNamaBarang(caption = '', fallback = '-') {
  const lines = caption
    .split('\n')
    .map((l) => l.trim())
    .filter(Boolean);
  for (const line of lines) {
    if (line.startsWith('#') || line.startsWith('@')) continue;
    const cleaned = line
      .replace(/https?:\/\/\S+/g, '')
      .replace(/(?:rp|idr)\s*[0-9][0-9., ]*/gi, '')
      .replace(/\s{2,}/g, ' ')
      .trim();
    if (cleaned.length >= 4) return cleaned.length > 90 ? `${cleaned.slice(0, 87)}...` : cleaned;
  }
  return fallback;
}

function inferPilihanVarian(caption = '') {
  const base = inferVariant(caption);
  const low = caption.toLowerCase();
  const motifMatch = low.match(/motif\s*[:\-]?\s*([a-z0-9, /-]+)/i);
  if (base === '-' && motifMatch?.[1]) return motifMatch[1].trim();
  if (base !== '-' && motifMatch?.[1]) return `${base} (Motif ${motifMatch[1].trim()})`;
  return base;
}

function isVideoItem(item) {
  const t = String(item.type || '').toLowerCase();
  const p = String(item.productType || '').toLowerCase();
  return t === 'video' || !!item.videoUrl || p.includes('clips') || p.includes('reel');
}

function isCatalogPost(item) {
  const caption = cleanCaption(item.caption || '');
  const low = caption.toLowerCase();
  const hasImage = !!(item.displayUrl || item.imageUrl || item.thumbnailSrc || item.images?.[0]);
  if (!hasImage) return false;
  if (NON_PRODUCT_HINTS.some((k) => low.includes(k))) return false;
  const hasProductHint = PRODUCT_HINTS.some((k) => low.includes(k));
  const hasPriceHint = /(?:rp|idr)\s*[0-9]/i.test(low) || /\b[0-9]{3,}(?:[.,][0-9]{3})+\b/.test(low);
  return hasProductHint || hasPriceHint;
}

function inferProduct(caption = '', fallback = '-') {
  const lines = caption
    .split('\n')
    .map((l) => l.trim())
    .filter(Boolean);
  for (const line of lines) {
    if (line.startsWith('#') || line.startsWith('@')) continue;
    const cleaned = line.replace(/https?:\/\/\S+/g, '').trim();
    if (cleaned.length >= 3) return cleaned.length > 80 ? `${cleaned.slice(0, 77)}...` : cleaned;
  }
  return fallback;
}

function toDateTime(value) {
  if (!value) return '';
  const d = new Date(value);
  if (!Number.isNaN(d.getTime())) return d.toISOString().replace('T', ' ').slice(0, 19);
  if (typeof value === 'number') {
    const dt = new Date(value < 2_000_000_000 ? value * 1000 : value);
    if (!Number.isNaN(dt.getTime())) return dt.toISOString().replace('T', ' ').slice(0, 19);
  }
  return '';
}

function mapItem(item) {
  const shortcode = item.shortCode || item.shortcode || item.code || '';
  const account =
    item.ownerUsername ||
    item.owner?.username ||
    item.inputUrl?.split('/').filter(Boolean).pop() ||
    '';
  const caption = cleanCaption(item.caption || item.latestComments?.[0]?.text || '');
  const imageUrl =
    item.displayUrl ||
    item.imageUrl ||
    item.thumbnailSrc ||
    item.images?.[0] ||
    item.childPosts?.[0]?.displayUrl ||
    '';
  const url = item.url || (shortcode ? `https://www.instagram.com/p/${shortcode}/` : '');

  const textForInference = `${caption}\n${item.alt || ''}`;
  const namaBarang = inferNamaBarang(caption, shortcode || '-');
  const kategoriSkripsi = inferSkripsiCategory(textForInference || namaBarang);
  const pilihanVarian = inferPilihanVarian(caption);

  return {
    akun: account,
    shortcode,
    tanggal: toDateTime(item.timestamp || item.takenAt || item.createdAt),
    url_post: url,
    caption,
    image_url: imageUrl,
    produk: namaBarang,
    kategori: kategoriSkripsi,
    varian: pilihanVarian,
    nama_barang: namaBarang,
    harga: extractPrice(caption),
    pilihan_varian: pilihanVarian,
    jenis_tenun: inferJenisTenun(textForInference || namaBarang),
    kategori_skripsi: kategoriSkripsi,
  };
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
      }
    }
  }

  const makeSkripsiSheet = (sheetName, tableTitle, records) => {
    const sh = wb.addWorksheet(sheetName);
    sh.mergeCells('A1:F1');
    sh.getCell('A1').value = tableTitle;
    sh.getCell('A1').font = { bold: true, size: 12 };
    sh.getCell('A1').alignment = { vertical: 'middle', horizontal: 'left' };

    sh.columns = [
      { header: 'No', key: 'no', width: 6 },
      { header: 'Nama Barang', key: 'nama_barang', width: 38 },
      { header: 'Harga (Rp)', key: 'harga', width: 16 },
      { header: 'Pilihan Varian (Warna/Motif)', key: 'pilihan_varian', width: 44 },
      { header: 'Jenis Produk', key: 'jenis_tenun', width: 30 },
      { header: 'Gambar Produk', key: 'gambar_produk', width: 22 },
    ];

    const headerRow = sh.getRow(2);
    headerRow.values = sh.columns.map((c) => c.header);
    headerRow.font = { bold: true };

    records.forEach((r, idx) => {
      const rowNum = idx + 3;
      sh.addRow({
        no: idx + 1,
        nama_barang: r.nama_barang || '-',
        harga: r.harga || '-',
        pilihan_varian: r.pilihan_varian || '-',
        jenis_tenun: r.jenis_tenun || '-',
        gambar_produk: '',
      });
      sh.getRow(rowNum).height = 80;
      [2, 4, 5].forEach((c) => {
        sh.getCell(rowNum, c).alignment = { vertical: 'top', wrapText: true };
      });

      if (r.image_path) {
        try {
          const ext = r.image_path.toLowerCase().endsWith('.png') ? 'png' : 'jpeg';
          const imgId = wb.addImage({ filename: r.image_path, extension: ext });
          sh.addImage(imgId, {
            tl: { col: 5.15, row: rowNum - 0.9 },
            ext: { width: 90, height: 90 },
            editAs: 'oneCell',
          });
        } catch {
        }
      }
    });
  };

  const bySkripsiCategory = new Map();
  for (const row of rows) {
    const key = row.kategori_skripsi || 'Kategori Kain Endek Katun';
    if (!bySkripsiCategory.has(key)) bySkripsiCategory.set(key, []);
    bySkripsiCategory.get(key).push(row);
  }

  const tableDefs = [
    { key: 'Kategori Dress & Gamis', sheet: 'Tabel 3.1 Dress Gamis', title: 'Tabel 3.1 Kategori Dress & Gamis' },
    { key: 'Kategori Setelan', sheet: 'Tabel 3.2 Setelan', title: 'Tabel 3.2 Kategori Setelan' },
    { key: 'Kategori Atasan & Outer', sheet: 'Tabel 3.3 Atasan Outer', title: 'Tabel 3.3 Kategori Atasan & Outer' },
    { key: 'Kategori Bawahan', sheet: 'Tabel 3.4 Bawahan', title: 'Tabel 3.4 Kategori Bawahan' },
    { key: 'Kategori Hijab', sheet: 'Tabel 3.5 Hijab', title: 'Tabel 3.5 Kategori Hijab' },
    { key: 'Kategori Aksesoris', sheet: 'Tabel 3.6 Aksesoris', title: 'Tabel 3.6 Kategori Aksesoris' },
    { key: 'Kategori Lainnya', sheet: 'Tabel 3.7 Lainnya', title: 'Tabel 3.7 Kategori Lainnya' },
  ];

  for (const def of tableDefs) {
    const items = bySkripsiCategory.get(def.key) || [];
    const unique = new Map();
    for (const it of items) {
      const k = (it.nama_barang || '').toLowerCase().trim();
      if (!k) continue;
      if (!unique.has(k)) unique.set(k, it);
    }
    makeSkripsiSheet(def.sheet, def.title, Array.from(unique.values()));
  }

  const summarySheet = wb.addWorksheet('kategori_summary');
  summarySheet.columns = [
    { header: 'no', key: 'no', width: 6 },
    { header: 'kategori', key: 'kategori', width: 20 },
    { header: 'jumlah_post', key: 'jumlah_post', width: 14 },
    { header: 'produk_unik', key: 'produk_unik', width: 14 },
    { header: 'contoh_produk', key: 'contoh_produk', width: 60 },
  ];
  summarySheet.getRow(1).font = { bold: true };

  const byCategory = new Map();
  for (const row of rows) {
    const category = row.kategori || 'lainnya';
    if (!byCategory.has(category)) {
      byCategory.set(category, []);
    }
    byCategory.get(category).push(row);
  }

  const summaryRows = Array.from(byCategory.entries())
    .map(([kategori, items]) => {
      const uniqueProducts = new Set(
        items.map((it) => (it.produk || '').trim().toLowerCase()).filter(Boolean)
      );
      const sample = items
        .map((it) => it.produk)
        .filter(Boolean)
        .slice(0, 5)
        .join(' | ');
      return {
        kategori,
        jumlah_post: items.length,
        produk_unik: uniqueProducts.size,
        contoh_produk: sample,
      };
    })
    .sort((a, b) => b.jumlah_post - a.jumlah_post);

  summaryRows.forEach((item, idx) => {
    summarySheet.addRow({
      no: idx + 1,
      kategori: item.kategori,
      jumlah_post: item.jumlah_post,
      produk_unik: item.produk_unik,
      contoh_produk: item.contoh_produk,
    });
  });

  for (let r = 2; r <= summarySheet.rowCount; r += 1) {
    summarySheet.getCell(r, 5).alignment = { vertical: 'top', wrapText: true };
  }

  const productCategorySheet = wb.addWorksheet('produk_per_kategori');
  productCategorySheet.columns = [
    { header: 'no', key: 'no', width: 6 },
    { header: 'kategori', key: 'kategori', width: 20 },
    { header: 'produk', key: 'produk', width: 46 },
    { header: 'jumlah_post', key: 'jumlah_post', width: 14 },
    { header: 'varian_terdeteksi', key: 'varian_terdeteksi', width: 40 },
    { header: 'contoh_url_post', key: 'contoh_url_post', width: 50 },
  ];
  productCategorySheet.getRow(1).font = { bold: true };

  const productMap = new Map();
  for (const row of rows) {
    const kategori = row.kategori_skripsi || row.kategori || 'lainnya';
    const produk = (row.nama_barang || row.produk || '-').trim() || '-';
    const key = `${kategori}||${produk.toLowerCase()}`;
    if (!productMap.has(key)) {
      productMap.set(key, {
        kategori,
        produk,
        count: 0,
        variants: new Set(),
        firstUrl: row.url_post || '',
      });
    }
    const agg = productMap.get(key);
    agg.count += 1;
    if (row.varian && row.varian !== '-') {
      row.varian
        .split('|')
        .map((v) => v.trim())
        .filter(Boolean)
        .forEach((v) => agg.variants.add(v));
    }
    if (!agg.firstUrl && row.url_post) {
      agg.firstUrl = row.url_post;
    }
  }

  const productRows = Array.from(productMap.values()).sort((a, b) => {
    if (a.kategori === b.kategori) return b.count - a.count;
    return a.kategori.localeCompare(b.kategori);
  });

  productRows.forEach((item, idx) => {
    productCategorySheet.addRow({
      no: idx + 1,
      kategori: item.kategori,
      produk: item.produk,
      jumlah_post: item.count,
      varian_terdeteksi: item.variants.size ? Array.from(item.variants).join(' | ') : '-',
      contoh_url_post: item.firstUrl,
    });
  });

  for (let r = 2; r <= productCategorySheet.rowCount; r += 1) {
    [3, 5, 6].forEach((c) => {
      productCategorySheet.getCell(r, c).alignment = { vertical: 'top', wrapText: true };
    });
  }

  await fs.mkdir(path.dirname(outputPath), { recursive: true });
  await wb.xlsx.writeFile(outputPath);
}

async function runApify(client, actorId, usernames, maxPosts) {
  const directUrls = usernames.map((u) => `https://www.instagram.com/${u}/`);

  const input = {
    directUrls,
    resultsType: 'posts',
    resultsLimit: maxPosts,
    searchType: 'user',
    addParentData: false,
  };

  const run = await client.actor(actorId).call(input);
  if (!run?.defaultDatasetId) {
    throw new Error('Apify run gagal: dataset tidak ditemukan.');
  }

  const items = [];
  const dataset = client.dataset(run.defaultDatasetId);
  let offset = 0;
  const limit = 100;

  while (true) {
    const page = await dataset.listItems({ offset, limit, clean: true });
    if (!page.items.length) break;
    items.push(...page.items);
    offset += page.items.length;
    if (items.length >= maxPosts * usernames.length) break;
  }

  return items;
}

async function main() {
  const args = parseArgs(process.argv.slice(2));
  const token = process.env.APIFY_TOKEN;

  if (!token) {
    throw new Error('APIFY_TOKEN belum di-set.');
  }

  const client = new ApifyClient({ token });
  const rawItems = await runApify(client, args.actor, args.accounts, args.maxPosts);
  if (!rawItems.length) {
    console.log('Tidak ada data post dari Apify.');
  }

  const rows = rawItems
    .filter((item) => !isVideoItem(item))
    .filter((item) => isCatalogPost(item))
    .map(mapItem)
    .filter((r) => r.url_post && r.image_url && r.nama_barang && r.nama_barang !== '-');
  for (let i = 0; i < rows.length; i += 1) {
    const row = rows[i];
    const account = row.akun || 'unknown';
    const ext = (row.image_url || '').includes('.png') ? '.png' : '.jpg';
    const filename = `${row.shortcode || `post_${i + 1}`}${ext}`;
    const localImage = path.join(args.imageDir, account, filename);
    const ok = await downloadImage(row.image_url, localImage);
    row.image_path = ok ? localImage : '';
  }

  rows.sort((a, b) => (b.tanggal || '').localeCompare(a.tanggal || ''));
  await writeExcel(rows, args.output);

  console.log(`OK. Total baris: ${rows.length}`);
  console.log(`Excel: ${args.output}`);
}

main().catch((err) => {
  console.error(err?.stack || err);
  process.exit(1);
});
