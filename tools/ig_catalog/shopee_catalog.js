import fs from 'node:fs/promises';
import path from 'node:path';
import process from 'node:process';
import ExcelJS from 'exceljs';

function parseArgs(argv) {
  const out = {
    shopUrl: 'https://shopee.co.id/kasyaraa.co',
    maxItems: 200,
    output: path.resolve('tools/ig_catalog/output/katalog_shopee.xlsx'),
    imageDir: path.resolve('tools/ig_catalog/output/shopee_images'),
    cookieHeader: process.env.SHOPEE_COOKIE || '',
  };

  for (let i = 0; i < argv.length; i += 1) {
    const a = argv[i];
    if (a === '--shop-url') {
      out.shopUrl = argv[i + 1] || out.shopUrl;
      i += 1;
    } else if (a === '--max-items') {
      out.maxItems = Number(argv[i + 1] || out.maxItems);
      i += 1;
    } else if (a === '--output') {
      out.output = path.resolve(argv[i + 1] || out.output);
      i += 1;
    } else if (a === '--image-dir') {
      out.imageDir = path.resolve(argv[i + 1] || out.imageDir);
      i += 1;
    } else if (a === '--cookie-header') {
      out.cookieHeader = argv[i + 1] || '';
      i += 1;
    }
  }

  return out;
}

function extractUsername(shopUrl) {
  try {
    const u = new URL(shopUrl);
    const parts = u.pathname.split('/').filter(Boolean);
    return parts[0] || '';
  } catch {
    return '';
  }
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

function inferJenisProduk(text = '') {
  const low = text.toLowerCase();
  if (low.includes('dress') || low.includes('gamis') || low.includes('abaya') || low.includes('tunik')) return 'Dress/Gamis';
  if (low.includes('set') || low.includes('setelan') || low.includes('one set')) return 'Setelan';
  if (low.includes('atasan') || low.includes('blouse') || low.includes('shirt') || low.includes('outer') || low.includes('cardigan')) return 'Atasan/Outer';
  if (low.includes('rok') || low.includes('celana') || low.includes('pants') || low.includes('skirt')) return 'Bawahan';
  if (low.includes('hijab') || low.includes('pashmina') || low.includes('bergo') || low.includes('khimar')) return 'Hijab';
  if (low.includes('tas') || low.includes('bag') || low.includes('sepatu') || low.includes('sandal')) return 'Aksesoris';
  return 'Produk Fashion';
}

function parsePrice(raw) {
  if (!raw || !Number.isFinite(raw)) return '-';
  const asRupiah = Math.round(raw / 100000);
  if (!Number.isFinite(asRupiah) || asRupiah <= 0) return '-';
  return asRupiah.toLocaleString('en-US');
}

async function jsonGet(url, headers) {
  const r = await fetch(url, { headers });
  const txt = await r.text();
  let data = {};
  try { data = JSON.parse(txt); } catch {}
  return { status: r.status, data, raw: txt };
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
  const raw = wb.addWorksheet('katalog_shopee');
  raw.columns = [
    { header: 'no', key: 'no', width: 6 },
    { header: 'gambar', key: 'gambar', width: 20 },
    { header: 'nama_barang', key: 'nama_barang', width: 46 },
    { header: 'harga_rp', key: 'harga', width: 16 },
    { header: 'pilihan_varian', key: 'varian', width: 44 },
    { header: 'jenis_produk', key: 'jenis_produk', width: 26 },
    { header: 'kategori', key: 'kategori', width: 28 },
    { header: 'url_produk', key: 'url_produk', width: 54 },
  ];
  raw.getRow(1).font = { bold: true };

  for (let i = 0; i < rows.length; i += 1) {
    const row = rows[i];
    const rn = i + 2;
    raw.addRow({
      no: i + 1,
      nama_barang: row.nama_barang,
      harga: row.harga,
      varian: row.pilihan_varian,
      jenis_produk: row.jenis_produk,
      kategori: row.kategori,
      url_produk: row.url_produk,
    });
    raw.getRow(rn).height = 80;
    [3, 5, 6, 7, 8].forEach((c) => {
      raw.getCell(rn, c).alignment = { vertical: 'top', wrapText: true };
    });
    if (row.image_path) {
      try {
        const ext = row.image_path.toLowerCase().endsWith('.png') ? 'png' : 'jpeg';
        const id = wb.addImage({ filename: row.image_path, extension: ext });
        raw.addImage(id, { tl: { col: 1.15, row: rn - 0.9 }, ext: { width: 90, height: 90 }, editAs: 'oneCell' });
      } catch {}
    }
  }

  const defs = [
    { key: 'Kategori Dress & Gamis', sheet: 'Tabel 3.1 Dress Gamis', title: 'Tabel 3.1 Kategori Dress & Gamis' },
    { key: 'Kategori Setelan', sheet: 'Tabel 3.2 Setelan', title: 'Tabel 3.2 Kategori Setelan' },
    { key: 'Kategori Atasan & Outer', sheet: 'Tabel 3.3 Atasan Outer', title: 'Tabel 3.3 Kategori Atasan & Outer' },
    { key: 'Kategori Bawahan', sheet: 'Tabel 3.4 Bawahan', title: 'Tabel 3.4 Kategori Bawahan' },
    { key: 'Kategori Hijab', sheet: 'Tabel 3.5 Hijab', title: 'Tabel 3.5 Kategori Hijab' },
    { key: 'Kategori Aksesoris', sheet: 'Tabel 3.6 Aksesoris', title: 'Tabel 3.6 Kategori Aksesoris' },
    { key: 'Kategori Lainnya', sheet: 'Tabel 3.7 Lainnya', title: 'Tabel 3.7 Kategori Lainnya' },
  ];

  for (const def of defs) {
    const sh = wb.addWorksheet(def.sheet);
    sh.mergeCells('A1:F1');
    sh.getCell('A1').value = def.title;
    sh.getCell('A1').font = { bold: true, size: 12 };
    sh.columns = [
      { header: 'No', key: 'no', width: 6 },
      { header: 'Nama Barang', key: 'nama', width: 40 },
      { header: 'Harga (Rp)', key: 'harga', width: 16 },
      { header: 'Pilihan Varian (Warna/Motif)', key: 'varian', width: 44 },
      { header: 'Jenis Produk', key: 'jenis', width: 28 },
      { header: 'Gambar Produk', key: 'gambar', width: 22 },
    ];
    const h = sh.getRow(2);
    h.values = sh.columns.map((c) => c.header);
    h.font = { bold: true };

    const items = rows.filter((r) => r.kategori === def.key);
    items.forEach((r, idx) => {
      const rn = idx + 3;
      sh.addRow({ no: idx + 1, nama: r.nama_barang, harga: r.harga, varian: r.pilihan_varian, jenis: r.jenis_produk, gambar: '' });
      sh.getRow(rn).height = 80;
      [2, 4, 5].forEach((c) => {
        sh.getCell(rn, c).alignment = { vertical: 'top', wrapText: true };
      });
      if (r.image_path) {
        try {
          const ext = r.image_path.toLowerCase().endsWith('.png') ? 'png' : 'jpeg';
          const id = wb.addImage({ filename: r.image_path, extension: ext });
          sh.addImage(id, { tl: { col: 5.15, row: rn - 0.9 }, ext: { width: 90, height: 90 }, editAs: 'oneCell' });
        } catch {}
      }
    });
  }

  await fs.mkdir(path.dirname(outputPath), { recursive: true });
  await wb.xlsx.writeFile(outputPath);
}

async function main() {
  const args = parseArgs(process.argv.slice(2));
  const username = extractUsername(args.shopUrl);
  if (!username) throw new Error('shop-url tidak valid');

  const headers = {
    'user-agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36',
    referer: args.shopUrl,
    'x-api-source': 'pc',
  };
  if (args.cookieHeader) headers.cookie = args.cookieHeader;

  const detail = await jsonGet(`https://shopee.co.id/api/v4/shop/get_shop_detail?username=${encodeURIComponent(username)}`, headers);
  if (detail.status !== 200 || detail.data?.error !== 0) {
    throw new Error(`Gagal ambil detail shop (${detail.status}): ${detail.raw.slice(0, 180)}`);
  }
  const shopid = detail.data.data.shopid;

  const categories = await jsonGet(`https://shopee.co.id/api/v4/shop/get_categories?shopid=${shopid}`, headers);
  const catList = categories.data?.data?.shop_categories || [];

  const allItems = [];
  for (const cat of catList) {
    const url = `https://shopee.co.id/api/v4/shop/search_items?limit=60&offset=0&shopid=${shopid}&sort_by=ctime&order=desc&shop_category_ids=${cat.shop_category_id}`;
    const resp = await jsonGet(url, headers);
    if (resp.status !== 200 || resp.data?.error !== 0) {
      continue;
    }
    const items = (resp.data.items || []).map((x) => x.item_basic).filter(Boolean);
    for (const it of items) {
      allItems.push({ ...it, _shopCategoryName: cat.display_name || '' });
      if (allItems.length >= args.maxItems) break;
    }
    if (allItems.length >= args.maxItems) break;
  }

  if (!allItems.length) {
    throw new Error('Produk belum bisa diambil. Biasanya perlu cookie login Shopee dari browser.');
  }

  const uniq = new Map();
  for (const it of allItems) {
    if (!it.itemid) continue;
    if (!uniq.has(it.itemid)) uniq.set(it.itemid, it);
  }

  const rows = [];
  for (const it of uniq.values()) {
    if (it.video_info_list?.length) continue;
    const name = (it.name || '').trim();
    if (!name) continue;

    const itemid = it.itemid;
    const priceRaw = it.price_min || it.price || it.price_max || 0;
    const priceText = parsePrice(priceRaw);
    const image = it.image;
    const imageUrl = image ? `https://down-id.img.susercontent.com/file/${image}` : '';
    const productUrl = `https://shopee.co.id/product/${shopid}/${itemid}`;
    const hint = `${name} ${it._shopCategoryName || ''}`;
    const kategori = inferSkripsiCategory(hint);
    const jenis = inferJenisProduk(hint);

    const row = {
      nama_barang: name,
      harga: priceText,
      pilihan_varian: it._shopCategoryName || '-',
      jenis_produk: jenis,
      kategori,
      url_produk: productUrl,
      image_url: imageUrl,
      image_path: '',
    };

    const filename = `${itemid}.jpg`;
    const localImage = path.join(args.imageDir, filename);
    const ok = await downloadImage(imageUrl, localImage);
    if (ok) row.image_path = localImage;

    rows.push(row);
  }

  await writeExcel(rows, args.output);
  console.log(`OK. Total produk: ${rows.length}`);
  console.log(`Excel: ${args.output}`);
}

main().catch((e) => {
  console.error(e?.stack || e);
  process.exit(1);
});
