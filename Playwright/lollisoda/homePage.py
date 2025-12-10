from playwright.sync_api import Page, TimeoutError
import time

def automate_cart_flow(page: Page, base_url: str):
    print(f"Testing Cart Functionality on : {base_url} ")

    page.goto(base_url, wait_until="load", timeout=60000)

   
    try:
        print("Checking for age verification modal")
        age_modal = page.locator(".modal-body.text-center:has-text('21')")
        age_modal.wait_for(state="visible", timeout=10000)

        page.locator(
            "a:has-text('I AM 21'), button:has-text('I AM 21')"
        ).first.click(force=True)

        age_modal.wait_for(state="hidden", timeout=10000)
        print("Age modal bypassed")

    except TimeoutError:
        print("No age modal detected")

    print("Newsletter modal detected but will be ignored")

    buttons = page.locator(".add_to_cart_button")
    total_buttons = buttons.count()
    print(f"Total Add to Cart buttons: {total_buttons}")


    cart_count_elem = page.locator(".cart-count")
    initial_cart = int(cart_count_elem.inner_text().strip()) if cart_count_elem.count() > 0 else 0
    print(f"Initial cart count: {initial_cart}")

    final_cart = initial_cart

    for i in range(total_buttons):
        print(f"\nClicking Add to Cart button #{i + 1}")

        btn = buttons.nth(i)
        btn.scroll_into_view_if_needed()
        print("Force clicking button (bypassing modal overlay)")
        btn.click(force=True)


        waited = 0
        while waited < 15:  
            time.sleep(1)
            waited += 1
            cart_text = cart_count_elem.inner_text().strip() if cart_count_elem.count() > 0 else "0"
            if cart_text.isdigit():
                current_count = int(cart_text)
                if current_count > final_cart:
                    final_cart = current_count
                    break

        print(f"Cart count after click {i + 1}: {final_cart}")

    if final_cart > initial_cart:
        print("\nAdd to cart working correctly\n")
    else:
        print("\nAdd to cart failed\n")


def check_order_online_redirect(page: Page): 
    try: 
        print("Checking the order online button\n")
        button = page.locator("div.d-lg-block.d-none a.theme-btn[title='Order Online']")
        button.wait_for(state="visible", timeout=10000)
        button.click()
        page.wait_for_load_state("load", timeout=10000)
        current_url = page.url
        print(f"Current URL after click: {current_url}")

        if current_url.startswith("https://www.lollisoda.com/shop/"):
            print("Redirected to shop page successfully")
            return True
        else:
            print("Redirect did not happen correctly")
            return False

    except TimeoutError:
        print("'Order Online' button not found or not visible\n")
        return False
    except Exception as e:
        print(f"Exception during redirect check: {e}\n")
        return False



def check_hamburger_menu(page: Page, base_url: str):
    print(f"\nChecking hamburger menu on: {base_url}")
    
    page.goto(base_url, wait_until="load", timeout=60000)
    
    try:
        age_modal = page.locator(".modal-body.text-center:has-text('YOU MUST BE 21+')")
        age_modal.wait_for(state="visible", timeout=10000)
        page.locator("a.theme-btn[title='I AM 21+']").first.click(force=True)
        age_modal.wait_for(state="hidden", timeout=10000)
    except TimeoutError:
        print("[STEP] No age modal detected")

    try:
        menu_button = page.locator("div.menu-ham button.ham-btn[title='Menu']")
        menu_button.wait_for(state="visible", timeout=10000)
        menu_button.scroll_into_view_if_needed()
        time.sleep(1)
        print("Clicking hamburger menu button")
        menu_button.click(force=True)
        time.sleep(1)

        header_menu = page.locator("div.header-menu")
        header_menu.wait_for(state="visible", timeout=5000)
        style = header_menu.get_attribute("style")

        if style and "display: block" in style:
            print("Hamburger menu opened successfully")
            return True
        else:
            print("Hamburger menu did not open")
            return False

    except TimeoutError:
        print("Hamburger menu button not found or menu did not appear")
        return False
    except Exception as e:
        print(f"Exception during hamburger menu check: {e}")
        return False

def homepage_test_lollisoda(page: Page): 
   check_hamburger_menu(page, "https://www.lollisoda.com/")
#    automate_cart_flow(page, "https://www.lollisoda.com/")
#    check_order_online_redirect(page)