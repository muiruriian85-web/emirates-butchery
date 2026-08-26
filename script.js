
// ==========================================
// EMIRATES BUTCHERY CART SYSTEM
// ==========================================

let cart = [];


// ADD PRODUCT
function addToCart(name, price) {

    cart.push({
        name: name,
        price: price
    });

    updateCart();

    alert(name + " has been added to your cart.");
}


// UPDATE CART
function updateCart() {

    const cartItems =
        document.getElementById("cartItems");

    const cartCount =
        document.getElementById("cart-count");

    const cartTotal =
        document.getElementById("cartTotal");


    cartCount.textContent = cart.length;


    if (cart.length === 0) {

        cartItems.innerHTML =
            "<p>Your cart is empty.</p>";

        cartTotal.textContent = "0";

        return;
    }


    let total = 0;

    cartItems.innerHTML = "";


    cart.forEach(function(item, index) {

        total += item.price;

        cartItems.innerHTML += `

            <div class="cart-item">

                <strong>
                    ${item.name}
                </strong>

                <br>

                KSh ${item.price}

                <button
                    onclick="removeItem(${index})"
                    style="
                        float:right;
                        border:none;
                        background:#e63946;
                        color:white;
                        padding:5px 8px;
                        border-radius:4px;
                        cursor:pointer;
                    "
                >
                    Remove
                </button>

            </div>

        `;

    });


    cartTotal.textContent = total;
}


// REMOVE PRODUCT
function removeItem(index) {

    cart.splice(index, 1);

    updateCart();
}


// OPEN CART
function openCart() {

    document
        .getElementById("cartPanel")
        .classList.add("active");
}


// CLOSE CART
function closeCart() {

    document
        .getElementById("cartPanel")
        .classList.remove("active");
}