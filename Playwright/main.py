from playwright.sync_api import sync_playwright, TimeoutError
from urllib.parse import urljoin
import requests
from utils import age_verification
from utils import newspaper_modal
from lollisoda import homePage
URLS = [
    "https://www.lollisoda.com/",
    "https://www.pamos.com/",
    "https://hey-buddi.com/"
]

SELECTORS = [
    "header",
    "footer",
    ".head-nav",
    ".site-footer",
]


HEADERS = {
    "User-Agent": (
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) "
        "AppleWebKit/537.36 (KHTML, like Gecko) "
        "Chrome/120 Safari/537.36"
    )
}


def check_header_footer_links(page, base_url, selectors):
    broken_links = []

    for selector in selectors:
        elements = page.query_selector_all(selector)
        if not elements:
            print(f"Selector '{selector}' not found on {base_url}")
            continue

        print(f"\nChecking selector '{selector}'")

        for element in elements:
            for link in element.query_selector_all("a"):
                href = link.get_attribute("href")
                if not href:
                    continue

                if href.startswith(("#", "javascript:", "mailto:")):
                    continue

                full_url = urljoin(base_url, href)
                try:
                    resp = requests.get(
                        full_url,
                        headers=HEADERS,
                        allow_redirects=True,
                        timeout=10
                    )

                    if resp.status_code >= 400:
                        print(f"Broken: {full_url} ({resp.status_code})")
                        broken_links.append(full_url)
                    else:
                        print(f"OK: {full_url}")

                except requests.RequestException as e:
                    print(f"Error: {full_url} ({e})")
                    broken_links.append(full_url)

    return broken_links


if __name__ == "__main__":
    with sync_playwright() as p:
        browser = p.chromium.launch(
            headless=True,
            args=["--disable-blink-features=AutomationControlled"]
        )
        context = browser.new_context(user_agent=HEADERS["User-Agent"])
        page = context.new_page()
        # for url in URLS:
        #     print(f"\n===== Checking website: {url} =====")

        #     try:
        #         page.goto(url, wait_until="domcontentloaded", timeout=60000)
        #         broken = check_header_footer_links(page, url, SELECTORS)

        #         print(f"\nSummary for {url}")
        #         print(f"Broken links found: {len(broken)}")

        #     except Exception as e:
        #         print(f"Failed to check {url}: {e}")

        # print("\n======Checking Age Modals======\n")
        # age_verification.check_age_modal(page)        
        # print("\n=====Checking the Newspaper Modals======\n")
        # newspaper_modal.run_newsletter(page)

        print("Checking homepage : Lollisoda")
        homePage.homepage_test_lollisoda(page)
        browser.close()
