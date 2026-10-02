// =====================================
// SAITEJA grocery  - MAIN JAVASCRIPT
// =====================================


// 1. MOBILE MENU
function toggleMenu() {
    const menu = document.getElementById("mobileMenu");

    if (menu) {
        menu.classList.toggle("show");
    }
}


// 2. PRODUCT SEARCH
function searchProducts() {
    const input = document.getElementById("searchInput");

    if (!input) return;

    const searchText = input.value.toLowerCase().trim();
    const products = document.querySelectorAll(".product-card");

    products.forEach(function (product) {
        const name = product.innerText.toLowerCase();

        if (name.includes(searchText)) {
            product.style.display = "";
        } else {
            product.style.display = "none";
        }
    });
}


// 3. ADD TO CART MESSAGE
function addToCart(productName) {
    alert(productName + " added to cart!");
}


// 4. PAYMENT COUNTDOWN
function startPaymentTimer(seconds) {

    const timer = document.getElementById("paymentTimer");

    if (!timer) return;

    let remaining = seconds;

    const countdown = setInterval(function () {

        const minutes = Math.floor(remaining / 60);
        const secondsLeft = remaining % 60;

        timer.textContent =
            String(minutes).padStart(2, "0") +
            ":" +
            String(secondsLeft).padStart(2, "0");

        remaining--;

        if (remaining < 0) {
            clearInterval(countdown);

            timer.textContent = "Payment Expired";

            alert("Payment time has expired.");
        }

    }, 1000);
}


// 5. IMAGE PREVIEW FOR ADMIN
function previewImage(event) {

    const image = document.getElementById("imagePreview");

    if (!image) return;

    const file = event.target.files[0];

    if (file) {
        image.src = URL.createObjectURL(file);
        image.style.display = "block";
    }
}


// 6. CONFIRM DELETE
function confirmDelete(productName) {

    return confirm(
        "Are you sure you want to delete " +
        productName +
        "?"
    );
}


// 7. AUTO HIDE MESSAGE
function hideMessage() {

    const message = document.getElementById("message");

    if (!message) return;

    setTimeout(function () {
        message.style.display = "none";
    }, 3000);
}