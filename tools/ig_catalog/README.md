# IG Catalog Tools

## 1) Puppeteer local

```powershell
npm run ig:catalog -- --accounts "akun1,akun2" --max-posts 150 --headless false
```

Output:
- `tools/ig_catalog/output/katalog_instagram.xlsx`
- `tools/ig_catalog/output/images/`

## 2) Apify (Recommended)

Set token Apify dulu (Windows PowerShell):

```powershell
$env:APIFY_TOKEN="APIFY_API_TOKEN_KAMU"
```

Lalu run:

```powershell
npm run ig:catalog:apify -- --accounts "kasyaraa.co,kasyaraa.catalog" --max-posts 200
```

Output:
- `tools/ig_catalog/output/katalog_instagram.xlsx`
- `tools/ig_catalog/output/images/`

Opsional:
- `--actor apify/instagram-scraper` (default)
- `--output "path.xlsx"`
- `--image-dir "folder_gambar"`
