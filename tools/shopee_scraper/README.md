# Shopee Scraper (Python)

Scraper produk Shopee menggunakan Selenium + BeautifulSoup, lalu simpan ke Excel.

## 1) Install dependency

```powershell
cd "C:\Users\Gusindra\Documents\Ardana Yatra\Kasyraa.co"
python -m venv .venv
.\.venv\Scripts\Activate.ps1
pip install -r tools\shopee_scraper\requirements.txt
```

## 2) Jalankan scraper

Dengan URL langsung:

```powershell
python tools\shopee_scraper\scrape_shopee.py --url "https://shopee.co.id/search?keyword=sepatu%20lari"
```

Dengan popup input URL:

```powershell
python tools\shopee_scraper\scrape_shopee.py --popup-url
```

Untuk URL produk tunggal:

```powershell
python tools\shopee_scraper\scrape_shopee.py --url "https://shopee.co.id/TUBE-TOP-JERSEY-SUMMER-TOP-KASYARAA.CO-i.287967404.43507111811"
```

Output default:
- `tools/shopee_scraper/output/shopee_products.xlsx`
- `tools/shopee_scraper/output/01_before_scroll.png`
- `tools/shopee_scraper/output/02_after_scroll.png`

## 3) Login pakai cookie/session

Lebih aman pakai file cookie:

1. Buat file `tools/shopee_scraper/cookie.txt`
2. Isi dengan value header `cookie` (tanpa kata `cookie:` juga boleh)
3. Jalankan:

```powershell
python tools\shopee_scraper\scrape_shopee.py `
  --url "https://shopee.co.id/TUBE-TOP-JERSEY-SUMMER-TOP-KASYARAA.CO-i.287967404.43507111811" `
  --cookie-file "tools/shopee_scraper/cookie.txt"
```

## 4) Kalau diarahkan ke login Shopee

Script juga bisa pakai profil Chrome lokal (`tools/shopee_scraper/chrome_profile`) dan tunggu login manual.

```powershell
python tools\shopee_scraper\scrape_shopee.py `
  --url "https://shopee.co.id/TUBE-TOP-JERSEY-SUMMER-TOP-KASYARAA.CO-i.287967404.43507111811?extraParams=%7B%22display_model_id%22%3A258590293210%2C%22model_selection_logic%22%3A3%7D" `
  --manual-login-wait 180
```

## Catatan

- Struktur HTML Shopee bisa berubah, jadi selector mungkin perlu update berkala.
- Script otomatis deteksi mode `listing` atau `produk tunggal`.
- Jangan pakai `--headless` kalau Shopee sering minta login/verifikasi.
