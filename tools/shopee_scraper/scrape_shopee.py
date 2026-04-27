import argparse
import json
import re
import time
from dataclasses import dataclass, asdict
from pathlib import Path
from typing import List, Optional
from urllib.parse import urljoin, urlparse
import tkinter as tk
from tkinter import simpledialog

import pandas as pd
from bs4 import BeautifulSoup
from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.common.by import By
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.support.ui import WebDriverWait


PRICE_PATTERN = re.compile(r"(?:Rp|IDR|\$)\s?[\d\.,]+")
SOLD_PATTERN = re.compile(r"(\d+[\d\.,]*\s*(?:rb|jt|k)?\s*(?:terjual|sold))", re.IGNORECASE)
PRODUCT_URL_PATTERN = re.compile(r"-i\.\d+\.\d+")


@dataclass
class ProductItem:
    name: str
    image_url: str
    price: str
    product_link: str
    sold_count: str
    location: str


def build_driver(
    headless: bool,
    chrome_binary: Optional[str],
    chromedriver_path: Optional[str],
    user_data_dir: Optional[str],
    profile_directory: Optional[str],
) -> webdriver.Chrome:
    options = Options()
    if headless:
        options.add_argument("--headless=new")

    options.add_argument("--start-maximized")
    options.add_argument("--disable-blink-features=AutomationControlled")
    options.add_argument("--disable-gpu")
    options.add_argument("--no-sandbox")
    options.add_argument("--window-size=1600,1200")
    options.add_argument("--disable-dev-shm-usage")
    options.add_experimental_option("excludeSwitches", ["enable-automation"])
    options.add_experimental_option("useAutomationExtension", False)
    options.add_argument(
        "--user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 "
        "(KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36"
    )

    if chrome_binary:
        options.binary_location = chrome_binary
    if user_data_dir:
        options.add_argument(f"--user-data-dir={user_data_dir}")
    if profile_directory:
        options.add_argument(f"--profile-directory={profile_directory}")

    if chromedriver_path:
        service = Service(executable_path=chromedriver_path)
        driver = webdriver.Chrome(service=service, options=options)
    else:
        driver = webdriver.Chrome(options=options)

    driver.execute_script("Object.defineProperty(navigator, 'webdriver', {get: () => undefined})")
    return driver


def scroll_until_loaded(driver: webdriver.Chrome, max_scroll: int, pause: float) -> None:
    previous_height = driver.execute_script("return document.body.scrollHeight")
    stable_round = 0

    for index in range(max_scroll):
        driver.execute_script("window.scrollTo(0, document.body.scrollHeight);")
        time.sleep(pause)
        current_height = driver.execute_script("return document.body.scrollHeight")

        if current_height == previous_height:
            stable_round += 1
            if stable_round >= 3:
                print(f"[INFO] Scroll berhenti di iterasi {index + 1} (tinggi halaman tidak berubah).")
                break
        else:
            stable_round = 0

        previous_height = current_height


def clean_text(value: Optional[str]) -> str:
    if not value:
        return ""
    return " ".join(value.split())


def first_non_empty(values: List[Optional[str]]) -> str:
    for value in values:
        cleaned = clean_text(value)
        if cleaned:
            return cleaned
    return ""


def is_product_url(url: str) -> bool:
    return bool(PRODUCT_URL_PATTERN.search(url))


def is_login_page(driver: webdriver.Chrome) -> bool:
    current = (driver.current_url or "").lower()
    if "login" in current or "verify" in current:
        return True
    indicators = [
        "input[name='loginKey']",
        "input[type='password']",
        "button[type='submit']",
        "iframe[src*='captcha']",
    ]
    for selector in indicators:
        if driver.find_elements(By.CSS_SELECTOR, selector):
            return True
    return False


def parse_cookie_string(cookie_string: str) -> List[dict]:
    cookies: List[dict] = []
    parts = [part.strip() for part in cookie_string.split(";") if part.strip()]
    for part in parts:
        if "=" not in part:
            continue
        name, value = part.split("=", 1)
        name = name.strip()
        value = value.strip()
        if not name:
            continue
        cookies.append({"name": name, "value": value})
    return cookies


def load_cookie_from_file(cookie_file_path: str) -> str:
    raw = Path(cookie_file_path).read_text(encoding="utf-8").strip()
    if not raw:
        return ""
    if raw.lower().startswith("cookie:"):
        return raw.split(":", 1)[1].strip()
    return raw


def ask_url_popup() -> str:
    root = tk.Tk()
    root.withdraw()
    root.attributes("-topmost", True)
    url = simpledialog.askstring(
        "Shopee Scraper",
        "Masukkan link Shopee target:",
        parent=root,
    )
    root.destroy()
    return clean_text(url)


def inject_cookies(driver: webdriver.Chrome, target_url: str, cookie_string: str) -> int:
    parsed = urlparse(target_url)
    scheme = parsed.scheme or "https"
    host = parsed.netloc or "shopee.co.id"
    base_url = f"{scheme}://{host}"

    driver.get(base_url)
    time.sleep(2)

    added = 0
    for cookie in parse_cookie_string(cookie_string):
        try:
            driver.add_cookie(cookie)
            added += 1
        except Exception:
            continue
    return added


def extract_name(card) -> str:
    selectors = [
        "div[data-sqe='name']",
        "div[aria-hidden='false']",
        "div.line-clamp-2",
        "div.line-clamp-3",
        "span",
    ]

    for selector in selectors:
        node = card.select_one(selector)
        if node:
            text = clean_text(node.get_text(" ", strip=True))
            if text and len(text) > 2:
                return text

    texts = [clean_text(t.get_text(" ", strip=True)) for t in card.find_all(["div", "span", "p"]) if t.get_text(strip=True)]
    texts = [t for t in texts if len(t) > 4]
    return max(texts, key=len, default="")


def extract_image(card) -> str:
    img = card.select_one("img")
    if not img:
        return ""

    for attr in ["src", "data-src", "data-lazy-src", "srcset"]:
        val = img.get(attr)
        if val:
            return val.split(" ")[0]

    return ""


def extract_price(card) -> str:
    for selector in ["span[data-sqe='price']", "div[data-sqe='price']", "span", "div"]:
        for node in card.select(selector):
            text = clean_text(node.get_text(" ", strip=True))
            match = PRICE_PATTERN.search(text)
            if match:
                return match.group(0)

    all_text = clean_text(card.get_text(" ", strip=True))
    match = PRICE_PATTERN.search(all_text)
    return match.group(0) if match else ""


def extract_sold(card) -> str:
    for selector in ["div[data-sqe='rating']", "div", "span"]:
        for node in card.select(selector):
            text = clean_text(node.get_text(" ", strip=True))
            match = SOLD_PATTERN.search(text)
            if match:
                return match.group(1)

    return "Belum terjual"


def extract_location(card) -> str:
    candidates = []
    for node in card.find_all(["div", "span"]):
        text = clean_text(node.get_text(" ", strip=True))
        if not text:
            continue
        if "terjual" in text.lower() or "sold" in text.lower():
            continue
        if PRICE_PATTERN.search(text):
            continue
        if len(text) <= 25 and re.search(r"[A-Za-z]", text):
            candidates.append(text)

    return candidates[-1] if candidates else ""


def parse_cards(html: str, base_url: str) -> List[ProductItem]:
    soup = BeautifulSoup(html, "lxml")
    cards = []

    anchor_candidates = soup.select("a[href*='/product/'], a[href*='i.'], a[href*='shopee.co.id']")
    if not anchor_candidates:
        anchor_candidates = soup.select("a")

    seen = set()

    for anchor in anchor_candidates:
        href = anchor.get("href")
        if not href:
            continue

        full_link = urljoin(base_url, href)
        if full_link in seen:
            continue

        card = anchor
        name = extract_name(card)
        price = extract_price(card)

        if not name and not price:
            continue

        item = ProductItem(
            name=name,
            image_url=extract_image(card),
            price=price,
            product_link=full_link,
            sold_count=extract_sold(card),
            location=extract_location(card),
        )

        seen.add(full_link)
        cards.append(item)

    return cards


def parse_product_detail(html: str, base_url: str) -> List[ProductItem]:
    soup = BeautifulSoup(html, "lxml")
    text_blob = clean_text(soup.get_text(" ", strip=True))

    canonical = soup.select_one("link[rel='canonical']")
    canonical_url = canonical.get("href", "") if canonical else ""
    product_link = canonical_url or base_url

    name = first_non_empty(
        [
            clean_text(soup.select_one("h1").get_text(" ", strip=True)) if soup.select_one("h1") else "",
            clean_text(soup.select_one("div[data-sqe='name']").get_text(" ", strip=True))
            if soup.select_one("div[data-sqe='name']")
            else "",
            soup.select_one("meta[property='og:title']").get("content", "")
            if soup.select_one("meta[property='og:title']")
            else "",
        ]
    )

    image_url = first_non_empty(
        [
            soup.select_one("meta[property='og:image']").get("content", "")
            if soup.select_one("meta[property='og:image']")
            else "",
            extract_image(soup),
        ]
    )

    price = ""
    for selector in ["div[data-sqe='price']", "span[data-sqe='price']", "div", "span"]:
        for node in soup.select(selector):
            text = clean_text(node.get_text(" ", strip=True))
            match = PRICE_PATTERN.search(text)
            if match:
                price = match.group(0)
                break
        if price:
            break

    if not price:
        match = PRICE_PATTERN.search(text_blob)
        price = match.group(0) if match else ""

    sold_match = SOLD_PATTERN.search(text_blob)
    sold_count = sold_match.group(1) if sold_match else "Belum terjual"

    location = extract_location(soup)

    jsonld_nodes = soup.select("script[type='application/ld+json']")
    for node in jsonld_nodes:
        raw = node.string or node.get_text(strip=True)
        if not raw:
            continue
        try:
            parsed = json.loads(raw)
        except json.JSONDecodeError:
            continue

        candidates = parsed if isinstance(parsed, list) else [parsed]
        for candidate in candidates:
            if not isinstance(candidate, dict):
                continue
            ctype = str(candidate.get("@type", "")).lower()
            if ctype != "product":
                continue

            name = name or clean_text(candidate.get("name"))

            image = candidate.get("image")
            if isinstance(image, list):
                image_url = image_url or clean_text(image[0] if image else "")
            elif isinstance(image, str):
                image_url = image_url or clean_text(image)

            offers = candidate.get("offers")
            if isinstance(offers, dict):
                offer_price = clean_text(str(offers.get("price", "")))
                currency = clean_text(str(offers.get("priceCurrency", "")))
                if offer_price and not price:
                    if currency.upper() == "IDR":
                        price = f"Rp {offer_price}"
                    else:
                        price = offer_price

                offer_url = clean_text(offers.get("url"))
                if offer_url:
                    product_link = offer_url

    if not name and not price:
        return []

    return [
        ProductItem(
            name=name,
            image_url=image_url,
            price=price,
            product_link=product_link,
            sold_count=sold_count,
            location=location,
        )
    ]


def save_excel(items: List[ProductItem], output_path: Path) -> None:
    output_path.parent.mkdir(parents=True, exist_ok=True)
    df = pd.DataFrame([asdict(item) for item in items])
    if df.empty:
        df = pd.DataFrame(columns=["name", "image_url", "price", "product_link", "sold_count", "location"])
    df.to_excel(output_path, index=False)


def run(args: argparse.Namespace) -> int:
    target_url = clean_text(args.url) if args.url else ""
    if not target_url and args.popup_url:
        target_url = ask_url_popup()
    if not target_url:
        raise ValueError("URL kosong. Isi --url atau gunakan --popup-url.")

    driver = build_driver(
        headless=args.headless,
        chrome_binary=args.chrome_binary,
        chromedriver_path=args.chromedriver,
        user_data_dir=args.user_data_dir,
        profile_directory=args.profile_directory,
    )

    try:
        cookie_string = args.cookie
        if args.cookie_file:
            cookie_string = load_cookie_from_file(args.cookie_file)

        if cookie_string:
            cookie_count = inject_cookies(driver, target_url, cookie_string)
            print(f"[INFO] Cookie berhasil diinject: {cookie_count}")

        print(f"[INFO] Buka URL: {target_url}")
        driver.get(target_url)

        WebDriverWait(driver, args.timeout).until(
            EC.presence_of_element_located((By.TAG_NAME, "body"))
        )
        time.sleep(args.initial_wait)

        if is_login_page(driver):
            if args.manual_login_wait > 0:
                print(
                    "[WARN] Terdeteksi halaman login/verifikasi. "
                    f"Silakan login manual di browser, menunggu {args.manual_login_wait} detik..."
                )
                time.sleep(args.manual_login_wait)
                driver.get(target_url)
                time.sleep(args.initial_wait)
            else:
                print(
                    "[WARN] Shopee mengarahkan ke login/verifikasi. "
                    "Coba pakai --manual-login-wait 120 dan --user-data-dir untuk simpan sesi."
                )

        screenshot_before = Path(args.screenshot_before)
        screenshot_before.parent.mkdir(parents=True, exist_ok=True)
        driver.save_screenshot(str(screenshot_before))
        print(f"[INFO] Screenshot awal tersimpan: {screenshot_before}")

        scroll_until_loaded(driver, max_scroll=args.max_scroll, pause=args.scroll_pause)

        screenshot_after = Path(args.screenshot_after)
        screenshot_after.parent.mkdir(parents=True, exist_ok=True)
        driver.save_screenshot(str(screenshot_after))
        print(f"[INFO] Screenshot setelah scroll tersimpan: {screenshot_after}")

        html = driver.page_source
        if is_product_url(target_url):
            print("[INFO] Mode produk tunggal terdeteksi.")
            items = parse_product_detail(html, base_url=target_url)
        else:
            print("[INFO] Mode listing produk terdeteksi.")
            items = parse_cards(html, base_url=target_url)

        print(f"[INFO] Total produk terdeteksi: {len(items)}")

        output_file = Path(args.output)
        save_excel(items, output_file)
        print(f"[INFO] File Excel tersimpan: {output_file}")

        if items:
            preview = pd.DataFrame([asdict(item) for item in items[:5]])
            print("[INFO] Preview 5 data pertama:")
            print(preview.to_string(index=False))
        else:
            print("[WARN] Belum dapat data produk. Coba naikkan --max-scroll atau ganti URL target.")

        return 0

    finally:
        driver.quit()


def build_parser() -> argparse.ArgumentParser:
    parser = argparse.ArgumentParser(
        description="Scraper Shopee dengan Selenium + BeautifulSoup, output Excel"
    )
    parser.add_argument("--url", help="URL target Shopee (search/store/category/product)")
    parser.add_argument(
        "--popup-url",
        action="store_true",
        help="Tampilkan popup untuk input URL jika --url tidak diisi",
    )
    parser.add_argument("--output", default="tools/shopee_scraper/output/shopee_products.xlsx", help="Path output Excel")
    parser.add_argument("--max-scroll", type=int, default=15, help="Maksimal iterasi scroll")
    parser.add_argument("--scroll-pause", type=float, default=2.0, help="Delay tiap scroll (detik)")
    parser.add_argument("--initial-wait", type=float, default=4.0, help="Delay awal setelah halaman terbuka")
    parser.add_argument("--timeout", type=int, default=20, help="Timeout tunggu elemen body")
    parser.add_argument("--headless", action="store_true", help="Jalankan browser mode headless")
    parser.add_argument("--chromedriver", help="Path chromedriver (opsional)")
    parser.add_argument("--chrome-binary", help="Path binary chrome/chromium (opsional)")
    parser.add_argument(
        "--user-data-dir",
        default="tools/shopee_scraper/chrome_profile",
        help="Folder profil chrome untuk menyimpan sesi login (opsional, default aktif)",
    )
    parser.add_argument(
        "--profile-directory",
        default="Default",
        help="Nama profil di user-data-dir (contoh: Default, Profile 1)",
    )
    parser.add_argument(
        "--manual-login-wait",
        type=int,
        default=120,
        help="Waktu tunggu (detik) untuk login manual jika terdeteksi halaman login",
    )
    parser.add_argument(
        "--cookie",
        help="Cookie string browser. Contoh: 'SPC_EC=...; csrftoken=...; ...'",
    )
    parser.add_argument(
        "--cookie-file",
        help="Path file txt berisi cookie string (boleh diawali 'cookie:').",
    )
    parser.add_argument(
        "--screenshot-before",
        default="tools/shopee_scraper/output/01_before_scroll.png",
        help="Path screenshot sebelum scroll",
    )
    parser.add_argument(
        "--screenshot-after",
        default="tools/shopee_scraper/output/02_after_scroll.png",
        help="Path screenshot setelah scroll",
    )
    return parser


if __name__ == "__main__":
    parser = build_parser()
    arguments = parser.parse_args()
    raise SystemExit(run(arguments))
