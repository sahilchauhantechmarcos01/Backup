from playwright.sync_api import TimeoutError
import time
import re

def handle_pamos_modals(page, test_email="test@example.com"):
    print(f"\nChecking for site : https://www.pamos.com/\n")

    AGE_MODAL_SELECTOR = ".modal-content.age-modal, .modal-body.text-center"
    AGE_BUTTON_SELECTOR = ".theme-btn:has-text('21')"

    NEWSLETTER_MODAL_SELECTOR = "div.modal-body.px-lg-5.pt-0.y-abs.w-100"
    EMAIL_SELECTOR = "input[type='email'], input[name='email'], input[data-testid='klaviyo-form-input']"
    
    try:
        page.wait_for_selector(AGE_MODAL_SELECTOR, timeout=10000)
        page.locator(AGE_BUTTON_SELECTOR).first.click()
        page.wait_for_selector(AGE_MODAL_SELECTOR, state="hidden", timeout=10000)
        print("Age confirmed (Pamos)")
    except TimeoutError:
        print("No age modal present (Pamos)")

    page.evaluate("window.scrollTo(0, document.body.scrollHeight)")
    time.sleep(3)

    try:
      
        page.wait_for_selector(NEWSLETTER_MODAL_SELECTOR, timeout=15000)
        print("Newsletter modal detected (Pamos)")

      
        email_found = False
        try:
          
            page.wait_for_selector(EMAIL_SELECTOR, timeout=5000)
            email_input = page.locator(EMAIL_SELECTOR).first
            email_input.fill(test_email)
            email_found = True
            print(f"Filled email: {test_email}")
        except:
         
            try:
              
                iframe = page.frame_locator("iframe[title='Klaviyo iFrame']")
                email_input = iframe.locator("input[type='email']")
                email_input.fill(test_email)
                email_found = True
                print(f"Filled email in iframe: {test_email}")
            except:
                print("Could not find email input field")

   
        if email_found:
           
            submit_selectors = [
                "button:has-text('Subscribe')",
                "button[type='submit']",
                ".klaviyo-form button",
                "button.primary",
                "button:has-text('Sign Up')",
                "button:has-text('Submit')"
            ]
            
            submitted = False
            for selector in submit_selectors:
                try:
                    if page.locator(selector).count() > 0:
                        page.locator(selector).first.click()
                        print(f"Clicked submit button: {selector}")
                        submitted = True
                        break
                except:
                    continue
            
            if not submitted:
              
                page.keyboard.press("Enter")
                print("Pressed Enter to submit form")

      
        confirmed = False
        text = ""
        for _ in range(30): 
            try:
              
                text = page.locator(NEWSLETTER_MODAL_SELECTOR).inner_text(timeout=1000).lower()
            except:
                text = ""
            
         
            if any(k in text for k in ["check your email", "thank", "20% off", "first order", "cheers", "subscribed"]):
                confirmed = True
                break
            time.sleep(0.5)

        if confirmed:
            print("Submission confirmed (Pamos)")
          
        else:
            print("Newsletter submission confirmation not found (Pamos)")
         
            if text:
                print(f"Found text: {text[:100]}...")

        return True

    except TimeoutError:
        print("Newsletter modal not present (Pamos)")
        return True
    except Exception as e:
        print(f"Error handling newsletter modal (Pamos): {e}")
        import traceback
        traceback.print_exc()
        return False
    

def handle_buddy_modals(page, test_email="test@example.com"):
    print(f"\nChecking for site : https://hey-buddi.com/\n")

    AGE_MODAL_SELECTOR = ".modal-content, .modal-body.text-center"
    CONFIRM_BUTTON_SELECTOR = ".age-confirm, .theme-btn:has-text('21'), button:has-text('21')"

    NEWSLETTER_MODAL_SELECTOR = "div.modal-body .newsletter-form"
    EMAIL_SELECTOR = f"{NEWSLETTER_MODAL_SELECTOR} input[type='email']"
    BUTTON_SELECTOR = f"{NEWSLETTER_MODAL_SELECTOR} button:has-text('Subscribe')"

    try:
        page.wait_for_selector(AGE_MODAL_SELECTOR, timeout=10000)
        page.locator(CONFIRM_BUTTON_SELECTOR).first.click()
        page.wait_for_selector(AGE_MODAL_SELECTOR, state="hidden", timeout=10000)
        print("Age confirmed (Hey Buddi)")
    except TimeoutError:
        print("No age modal present (Hey Buddi)")

    page.evaluate("window.scrollTo(0, document.body.scrollHeight)")
    time.sleep(3)

    try:
        page.wait_for_selector(NEWSLETTER_MODAL_SELECTOR, timeout=15000)
        print("Newsletter modal detected (Hey Buddi)")

        page.fill(EMAIL_SELECTOR, test_email)
        page.locator(BUTTON_SELECTOR).first.click()
        print(f"Filled email: {test_email} and clicked Subscribe")

        confirmed = False
        text = ""
        for _ in range(12):
            try:
                text = page.locator(NEWSLETTER_MODAL_SELECTOR).inner_text().lower()
            except:
                text = ""
            if any(k in text for k in ["thank", "code", "%", "off", "save", "subscrib"]):
                confirmed = True
                break
            time.sleep(0.5)

        if confirmed:
            print("Submission confirmed (Hey Buddi)")
       
        else:
            print("Newsletter submission confirmation not found (Hey Buddi)")

        return True

    except TimeoutError:
        print("Newsletter modal not present (Hey Buddi)")
        return True
    except Exception as e:
        print(f"Error handling newsletter modal (Hey Buddi): {e}")
        return False

def handle_lollisoda_modals(page, test_email="test@example.com"):
    print(f"\nChecking for site : https://www.lollisoda.com/\n")

    AGE_MODAL_SELECTOR = ".modal-body.text-center"
    AGE_BUTTON_SELECTOR = ".theme-btn:has-text('I AM 21')"

    NEWSLETTER_MODAL_SELECTOR = "#newsModal"
    EMAIL_SELECTOR = f"{NEWSLETTER_MODAL_SELECTOR} input[type='email']"
    SUBMIT_SELECTOR = f"{NEWSLETTER_MODAL_SELECTOR} button[type='button']"

    try:
        page.wait_for_selector(AGE_MODAL_SELECTOR, timeout=10000)
        page.locator(AGE_BUTTON_SELECTOR).first.click()
        page.wait_for_selector(AGE_MODAL_SELECTOR, state="hidden", timeout=10000)
        print("Age confirmed (LolliSoda)")
    except TimeoutError:
        print("No age modal present (LolliSoda)")

    time.sleep(6)

    try:
        page.wait_for_selector(NEWSLETTER_MODAL_SELECTOR, timeout=25000)
        print("Newsletter modal detected (LolliSoda)")

        page.wait_for_selector(EMAIL_SELECTOR, state="visible", timeout=10000)
        page.fill(EMAIL_SELECTOR, test_email)
        page.locator(SUBMIT_SELECTOR).first.click()
        print(f"Filled email: {test_email} and clicked Submit")

        confirmed = False
        for _ in range(12):
            text = page.locator(NEWSLETTER_MODAL_SELECTOR).inner_text().lower()
            if any(k in text for k in ["thank", "code", "%", "off", "save"]):
                confirmed = True
                break
            time.sleep(0.5)

        if confirmed:
            print("Submission confirmed (LolliSoda)")
         
        else:
            print("Newsletter submission confirmation not found (LolliSoda)")

        return True

    except TimeoutError:
        print("Newsletter modal not present (LolliSoda)")
        return True
    except Exception as e:
        print(f"Error handling newsletter modal (LolliSoda): {e}")
        return False

def run_newsletter(page):
    page.goto("https://www.pamos.com/", wait_until="domcontentloaded", timeout=60000)
    handle_pamos_modals(page)
    page.goto("https://www.lollisoda.com/", wait_until="domcontentloaded", timeout=60000)
    handle_lollisoda_modals(page)

    page.goto("https://hey-buddi.com/", wait_until="domcontentloaded", timeout=60000)
    handle_buddy_modals(page)
