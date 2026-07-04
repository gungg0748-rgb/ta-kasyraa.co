import puppeteer from 'puppeteer';
import { mkdirSync } from 'fs';
import { join } from 'path';

const BASE = 'http://127.0.0.1:8020';
const SCREENSHOT_DIR = join(import.meta.dirname, 'screenshots');
mkdirSync(SCREENSHOT_DIR, { recursive: true });

const pages = [
  // 4.1 - Login
  { url: '/login', name: '01-login', section: '4.1', title: 'Login' },
  // 4.2 - Dashboard  
  { url: '/dashboard', name: '02-dashboard', section: '4.2', title: 'Dashboard' },
  // 4.3 - Kategori
  { url: '/categories', name: '03-kategori-index', section: '4.3.1', title: 'Daftar Kategori' },
  { url: '/categories/create', name: '04-kategori-create', section: '4.3.2', title: 'Form Tambah Kategori' },
  { url: '/categories/1/edit', name: '05-kategori-edit', section: '4.3.2', title: 'Form Edit Kategori' },
  // 4.4 - Satuan
  { url: '/units', name: '06-satuan-index', section: '4.4.1', title: 'Daftar Satuan' },
  { url: '/units/create', name: '07-satuan-create', section: '4.4.2', title: 'Form Tambah Satuan' },
  { url: '/units/1/edit', name: '08-satuan-edit', section: '4.4.2', title: 'Form Edit Satuan' },
  // 4.5 - Supplier
  { url: '/suppliers', name: '09-supplier-index', section: '4.5.1', title: 'Daftar Supplier' },
  { url: '/suppliers/create', name: '10-supplier-create', section: '4.5.2', title: 'Form Tambah Supplier' },
  { url: '/suppliers/1/edit', name: '11-supplier-edit', section: '4.5.2', title: 'Form Edit Supplier' },
  { url: '/suppliers/1', name: '12-supplier-detail', section: '4.5.3', title: 'Detail Supplier' },
  // 4.6 - Produk
  { url: '/products', name: '13-produk-index', section: '4.6.1', title: 'Daftar Produk' },
  { url: '/products/create', name: '14-produk-create', section: '4.6.2', title: 'Form Tambah Produk' },
  { url: '/products/1/edit', name: '15-produk-edit', section: '4.6.2', title: 'Form Edit Produk' },
  { url: '/products/1', name: '16-produk-detail', section: '4.6.3', title: 'Detail Produk' },
  // 4.7 - Pembelian
  { url: '/purchases', name: '17-pembelian-index', section: '4.7.1', title: 'Daftar Pembelian' },
  { url: '/purchases/create', name: '18-pembelian-create', section: '4.7.2', title: 'Form Tambah Pembelian' },
  { url: '/purchases/1', name: '19-pembelian-detail', section: '4.7.3', title: 'Detail Pembelian' },
  // 4.8 - Penjualan
  { url: '/sales', name: '20-penjualan-index', section: '4.8.1', title: 'Daftar Penjualan' },
  { url: '/sales/create', name: '21-penjualan-create', section: '4.8.2', title: 'Form Tambah Penjualan' },
  { url: '/sales/1', name: '22-penjualan-detail', section: '4.8.3', title: 'Detail Penjualan' },
  // 4.9 - Retur
  { url: '/returns', name: '23-retur-index', section: '4.9.1', title: 'Daftar Retur' },
  { url: '/returns/create', name: '24-retur-create', section: '4.9.2', title: 'Form Tambah Retur' },
  { url: '/returns/1', name: '25-retur-detail', section: '4.9.3', title: 'Detail Retur' },
  // 4.10 - Stok Opname
  { url: '/stock-opnames', name: '26-opname-index', section: '4.10.1', title: 'Daftar Stok Opname' },
  { url: '/stock-opnames/create', name: '27-opname-create', section: '4.10.2', title: 'Form Tambah Stok Opname' },
  { url: '/stock-opnames/1', name: '28-opname-detail', section: '4.10.3', title: 'Detail Stok Opname' },
  // 4.11 - Laporan
  { url: '/reports', name: '29-laporan-index', section: '4.11.1', title: 'Indeks Laporan' },
  { url: '/reports/stock', name: '30-laporan-stock', section: '4.11.2', title: 'Laporan Stok' },
  { url: '/reports/sales', name: '31-laporan-sales', section: '4.11.3', title: 'Laporan Penjualan' },
  { url: '/reports/purchases', name: '32-laporan-purchases', section: '4.11.4', title: 'Laporan Pembelian' },
  { url: '/reports/returns', name: '33-laporan-returns', section: '4.11.5', title: 'Laporan Retur' },
  { url: '/reports/opnames', name: '34-laporan-opnames', section: '4.11.6', title: 'Laporan Stok Opname' },
  // 4.12 - Akun
  { url: '/users', name: '35-akun-index', section: '4.12.1', title: 'Daftar Akun' },
  { url: '/users/create', name: '36-akun-create', section: '4.12.2', title: 'Form Tambah Akun' },
  { url: '/users/1/edit', name: '37-akun-edit', section: '4.12.2', title: 'Form Edit Akun' },
  // 4.13 - Profil
  { url: '/profile', name: '38-profil', section: '4.13', title: 'Profil' },
];

async function main() {
  const browser = await puppeteer.launch({
    headless: 'new',
    executablePath: 'C:\\Users\\imade\\.cache\\puppeteer\\chrome\\win64-150.0.7871.24\\chrome-win64\\chrome.exe',
    defaultViewport: { width: 1440, height: 900 }
  });

  const context = browser.defaultBrowserContext();
  const page = await browser.newPage();

  // Login dulu
  console.log('Logging in...');
  await page.goto(`${BASE}/login`, { waitUntil: 'networkidle2' });
  await page.type('input[type="email"]', 'admin@kasyraa.co');
  await page.type('input[type="password"]', 'password');
  await Promise.all([
    page.click('button[type="submit"]'),
    page.waitForNavigation({ waitUntil: 'networkidle2' })
  ]);
  console.log('Logged in successfully!');

  for (const p of pages) {
    console.log(`Screenshot: ${p.name} -> ${p.title}`);
    try {
      await page.goto(`${BASE}${p.url}`, { waitUntil: 'networkidle2', timeout: 15000 });
      await new Promise(r => setTimeout(r, 500)); // wait for alpine/tailwind rendering
      await page.screenshot({
        path: join(SCREENSHOT_DIR, `${p.name}.png`),
        fullPage: true
      });
      console.log(`  OK: ${p.name}.png`);
    } catch (err) {
      console.log(`  FAILED: ${p.name} - ${err.message}`);
    }
  }

  // Scanner Mobile - generate token dan screenshot
  try {
    console.log('Generating scanner token...');
    await page.goto(`${BASE}/dashboard`, { waitUntil: 'networkidle2' });
    const tokenResp = await page.evaluate(async () => {
      const resp = await fetch('/scanner/token', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
          'Accept': 'application/json'
        }
      });
      return resp.json();
    });
    console.log('Token response:', JSON.stringify(tokenResp));
    
    const scannerUrl = `${BASE}/scanner/${tokenResp.token}`;
    console.log(`Scanner URL: ${scannerUrl}`);
    await page.goto(scannerUrl, { waitUntil: 'networkidle2', timeout: 15000 });
    await new Promise(r => setTimeout(r, 1000));
    await page.screenshot({
      path: join(SCREENSHOT_DIR, '39-scanner-mobile.png'),
      fullPage: true
    });
    console.log('  OK: 39-scanner-mobile.png');
  } catch (err) {
    console.log(`  FAILED: scanner - ${err.message}`);
  }

  await browser.close();
  console.log('\nAll screenshots captured!');
}

main().catch(err => {
  console.error('Fatal error:', err);
  process.exit(1);
});
