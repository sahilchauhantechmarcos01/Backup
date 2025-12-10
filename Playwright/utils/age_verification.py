from playwright.sync_api import sync_playwright, TimeoutError


MODAL_SELECTOR = ".modal-body.text-center"
AGE_TEXT_SELECTOR = ".modal-body .age-txt"
BUTTON_SELECTOR = ".modal-body .theme-btn"

MODAL_SELECTOR_PAMOS = ".modal-content.age-modal, .modal-body.text-center"
BUTTON_SELECTOR_PAMOS = ".theme-btn"

MODAL_SELECTOR_BUDDY = ".modal-content, .modal-body.text-center"
AGE_TEXT_SELECTOR_BUDDY = ".age-txt"
CONFIRM_BUTTON_SELECTOR_BUDDY = (
        ".age-confirm, "
        ".theme-btn:has-text('21'), "
        "button:has-text('21')"
    )
AGE_VERIFICATION = {
    "https://www.lollisoda.com/" :[MODAL_SELECTOR,AGE_TEXT_SELECTOR,BUTTON_SELECTOR],
    "https://www.pamos.com/" : [MODAL_SELECTOR_PAMOS,BUTTON_SELECTOR_PAMOS],
    "https://hey-buddi.com/" : [MODAL_SELECTOR_BUDDY,AGE_TEXT_SELECTOR_BUDDY,CONFIRM_BUTTON_SELECTOR_BUDDY]

}


def check_age_modal(page):
    for key , value in AGE_VERIFICATION.items(): 
        page.goto(key, wait_until="domcontentloaded", timeout=60000)
        match key:
            case "https://www.lollisoda.com/":
                print(f"\nChecking for {key}\n")
                try:
                    page.wait_for_selector(AGE_VERIFICATION[key][0], timeout=10000)
                    print("Age verification modal is visible")
                    age_text = page.text_content(AGE_VERIFICATION[key][1])
                    assert "21+" in age_text
                    print(f"Modal text verified: '{age_text.strip()}'")
                    page.click(AGE_VERIFICATION[key][2])
                    print("Clicked 'I AM 21+' button")
                    page.wait_for_selector(AGE_VERIFICATION[key][0], state="hidden", timeout=10000)
                    print("Modal closed successfully")

                except TimeoutError:
                    print("Age verification modal did NOT appear or close")

                except AssertionError:
                    print("Age verification text is incorrect")
                    
            case "https://www.pamos.com/":
                print(f"\nChecking for {key}\n")
                try:
                    page.wait_for_selector(AGE_VERIFICATION[key][0], timeout=10000)
                    print("Age verification modal detected")

                    modal = page.locator(AGE_VERIFICATION[key][0]).first
                    modal_text = modal.inner_text()
                    if "21+" not in modal_text:
                        print("Age text missing in modal")
                        return False
                    print("Age verification text is correct")
                    confirm_button = modal.locator(
                        f"{AGE_VERIFICATION[key][1]}:has-text('21+')"
                    ).first
                    confirm_button.wait_for(state="visible", timeout=5000)
                    confirm_button.click()
                    print("Clicked 'I am 21+' button")
                    page.wait_for_selector(AGE_VERIFICATION[key][0], state="hidden", timeout=10000)
                    print("Age modal closed successfully")


                except TimeoutError:
                    print("Age modal not found (may already be accepted)")
                    

            case "https://hey-buddi.com/":
                print(f"\nChecking for {key}\n")
                try:

                    page.wait_for_selector(AGE_VERIFICATION[key][0], timeout=8000)
                    print("Age verification modal detected")

                    modal = page.locator(AGE_VERIFICATION[key][0]).first

                    if modal.locator(AGE_VERIFICATION[key][1]).count() > 0:
                        age_text = modal.locator(AGE_VERIFICATION[key][1]).inner_text()
                        assert "21" in age_text
                        print(f"Age text verified: {age_text.strip()}")

                    confirm_btn = modal.locator(AGE_VERIFICATION[key][2]).first
                    confirm_btn.wait_for(state="visible", timeout=5000)
                    confirm_btn.click()
                    print("Clicked age confirmation button")

                    page.wait_for_selector(AGE_VERIFICATION[key][0], state="hidden", timeout=10000)
                    print("Age modal closed")


                except TimeoutError:
                    print("No age verification modal present")
                 

                except AssertionError:
                    print("Age text invalid")
                

