<?php

session_start();

require_once "database/database.php";


/* =========================
   CHECK CART
========================= */

if (
    !isset($_SESSION["cart"]) ||
    empty($_SESSION["cart"])
) {
    header("Location: cart.php");
    exit;
}


/* =========================
   CREATE COUPONS TABLE
========================= */

$db->exec("
    CREATE TABLE IF NOT EXISTS coupons (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        code TEXT UNIQUE NOT NULL,
        discount_type TEXT NOT NULL,
        discount_value REAL NOT NULL,
        minimum_order REAL DEFAULT 0,
        maximum_discount REAL DEFAULT 0,
        expiry_date TEXT,
        status TEXT DEFAULT 'Active',
        created_at TEXT DEFAULT CURRENT_TIMESTAMP
    )
");


/* =========================
   GET PAYMENT SETTINGS
========================= */

$paymentSettings = $db->querySingle("
    SELECT *
    FROM payment_settings
    ORDER BY id DESC
    LIMIT 1
", true);


/* =========================
   GET CART PRODUCTS
========================= */

$cartItems = [];

$total = 0;


foreach ($_SESSION["cart"] as $productId => $quantity) {

    $productId = (int)$productId;
    $quantity = (int)$quantity;


    if ($quantity <= 0) {
        continue;
    }


    $stmt = $db->prepare("
        SELECT *
        FROM products
        WHERE id = :id
    ");


    $stmt->bindValue(
        ":id",
        $productId,
        SQLITE3_INTEGER
    );


    $result = $stmt->execute();

    $product = $result->fetchArray(
        SQLITE3_ASSOC
    );


    if ($product) {

        $subtotal =
            $product["selling_price"] *
            $quantity;


        $total += $subtotal;


        $cartItems[] = [

            "id" =>
                $product["id"],

            "name" =>
                $product["name"],

            "price" =>
                $product["selling_price"],

            "quantity" =>
                $quantity,

            "subtotal" =>
                $subtotal

        ];

    }

}


/* =========================
   IF CART EMPTY
========================= */

if (empty($cartItems)) {

    header("Location: cart.php");

    exit;
}


/* =========================
   COUPON VARIABLES
========================= */

$couponCode =
    $_SESSION["coupon_code"] ?? "";

$discount =
    $_SESSION["coupon_discount"] ?? 0;

$finalTotal =
    $total - $discount;


/* Make sure total never becomes negative */

if ($finalTotal < 0) {
    $finalTotal = 0;
}


$couponMessage = "";

$couponError = "";


/* =========================
   APPLY COUPON
========================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["claim_discount"])
) {

    $enteredCoupon =
        strtoupper(
            trim(
                $_POST["coupon_code"] ?? ""
            )
        );


    /* Reset previous coupon */

    unset($_SESSION["coupon_code"]);

    unset($_SESSION["coupon_discount"]);


    $couponCode = "";

    $discount = 0;

    $finalTotal = $total;


    if ($enteredCoupon === "") {

        $couponError =
            "Please enter a coupon code.";

    } else {

        $stmt = $db->prepare("
            SELECT *
            FROM coupons
            WHERE UPPER(code) = :code
            LIMIT 1
        ");


        $stmt->bindValue(
            ":code",
            $enteredCoupon,
            SQLITE3_TEXT
        );


        $result =
            $stmt->execute();


        $coupon =
            $result->fetchArray(
                SQLITE3_ASSOC
            );


        if (!$coupon) {

            $couponError =
                "Invalid coupon code.";

        } else {


            /* =========================
               CHECK STATUS
            ========================= */

            if (
                strtolower(
                    $coupon["status"]
                ) !== "active"
            ) {

                $couponError =
                    "This coupon is not active.";

            }


            /* =========================
               CHECK EXPIRY
            ========================= */

            elseif (
                !empty(
                    $coupon["expiry_date"]
                )
                &&
                strtotime(
                    $coupon["expiry_date"]
                ) < time()
            ) {

                $couponError =
                    "This coupon has expired.";

            }


            /* =========================
               CHECK MINIMUM ORDER
            ========================= */

            elseif (
                $total <
                (float)$coupon["minimum_order"]
            ) {

                $couponError =
                    "Minimum order value is ₹"
                    .
                    number_format(
                        (float)$coupon["minimum_order"],
                        2
                    )
                    .
                    ".";

            }


            /* =========================
               CALCULATE DISCOUNT
            ========================= */

            else {

                $discountType =
                    strtolower(
                        trim(
                            $coupon["discount_type"]
                        )
                    );


                $discountValue =
                    (float)$coupon["discount_value"];


                if (
                    $discountType === "percentage"
                    ||
                    $discountType === "percent"
                ) {

                    $discount =
                        ($total * $discountValue)
                        / 100;


                    /* Maximum discount */

                    if (
                        (float)$coupon["maximum_discount"] > 0
                        &&
                        $discount >
                        (float)$coupon["maximum_discount"]
                    ) {

                        $discount =
                            (float)$coupon["maximum_discount"];

                    }

                } else {

                    /* Fixed discount */

                    $discount =
                        $discountValue;

                }


                /* Discount cannot exceed total */

                if ($discount > $total) {

                    $discount = $total;

                }


                $finalTotal =
                    $total - $discount;


                /* Save coupon in session */

                $_SESSION["coupon_code"] =
                    $enteredCoupon;


                $_SESSION["coupon_discount"] =
                    $discount;


                $couponCode =
                    $enteredCoupon;


                $couponMessage =
                    "Coupon applied successfully! You saved ₹"
                    .
                    number_format(
                        $discount,
                        2
                    )
                    .
                    ".";

            }

        }

    }

}


/* =========================
   REMOVE COUPON
========================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["remove_coupon"])
) {

    unset($_SESSION["coupon_code"]);

    unset($_SESSION["coupon_discount"]);


    $couponCode = "";

    $discount = 0;

    $finalTotal = $total;

    $couponMessage =
        "Coupon removed.";

}


/* =========================
   FORM VALUES
========================= */

$name = "";

$phone = "";

$address = "";

$payment = "COD";

$transactionId = "";

$error = "";

$success = "";


/* =========================
   PLACE ORDER
========================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["place_order"])
) {


    $name =
        trim(
            $_POST["name"] ?? ""
        );


    $phone =
        trim(
            $_POST["phone"] ?? ""
        );


    $address =
        trim(
            $_POST["address"] ?? ""
        );


    $payment =
        trim(
            $_POST["payment"] ?? "COD"
        );


    $transactionId =
        trim(
            $_POST["transaction_id"] ?? ""
        );


    /* =========================
       VALIDATION
    ========================= */

    if ($name === "") {

        $error =
            "Please enter your name.";

    }

    elseif ($phone === "") {

        $error =
            "Please enter your phone number.";

    }

    elseif ($address === "") {

        $error =
            "Please enter your delivery address.";

    }

    elseif (
        $payment !== "COD"
        &&
        $payment !== "UPI"
    ) {

        $error =
            "Invalid payment method.";

    }


    /* =========================
       UPI VALIDATION
    ========================= */

    if (
        $error === ""
        &&
        $payment === "UPI"
        &&
        $transactionId === ""
    ) {

        $error =
            "Please enter your UPI transaction/reference ID.";

    }


    /* =========================
       CREATE ORDER
    ========================= */

    if ($error === "") {


        $orderNumber =
            "SG"
            .
            date("YmdHis")
            .
            rand(100, 999);


        /* =========================
           PAYMENT INFORMATION
        ========================= */

        if ($payment === "UPI") {

            $paymentInfo =
                "UPI - Transaction ID: "
                .
                $transactionId;

        } else {

            $paymentInfo =
                "Cash on Delivery";

        }


        /* =========================
           ADD COUPON INFORMATION
        ========================= */

        if ($couponCode !== "") {

            $paymentInfo .=
                " | Coupon: "
                .
                $couponCode
                .
                " | Discount: ₹"
                .
                number_format(
                    $discount,
                    2
                );

        }


        /* =========================
           ITEMS JSON
        ========================= */

        $itemsJson =
            json_encode(
                $cartItems,
                JSON_UNESCAPED_UNICODE
            );


        /* =========================
           CUSTOMER EMAIL
        ========================= */

        $email =
            $_SESSION["user_email"]
            ??
            "";


        /* =========================
           INSERT ORDER
        ========================= */

        $stmt = $db->prepare("

            INSERT INTO orders

            (
                order_number,
                customer,
                email,
                phone,
                address,
                items,
                amount,
                payment,
                status,
                date
            )

            VALUES

            (
                :order_number,
                :customer,
                :email,
                :phone,
                :address,
                :items,
                :amount,
                :payment,
                'Pending',
                :date
            )

        ");


        $stmt->bindValue(
            ":order_number",
            $orderNumber,
            SQLITE3_TEXT
        );


        $stmt->bindValue(
            ":customer",
            $name,
            SQLITE3_TEXT
        );


        $stmt->bindValue(
            ":email",
            $email,
            SQLITE3_TEXT
        );


        $stmt->bindValue(
            ":phone",
            $phone,
            SQLITE3_TEXT
        );


        $stmt->bindValue(
            ":address",
            $address,
            SQLITE3_TEXT
        );


        $stmt->bindValue(
            ":items",
            $itemsJson,
            SQLITE3_TEXT
        );


        /* IMPORTANT:
           Save discounted final amount */

        $stmt->bindValue(
            ":amount",
            $finalTotal,
            SQLITE3_FLOAT
        );


        $stmt->bindValue(
            ":payment",
            $paymentInfo,
            SQLITE3_TEXT
        );


        $stmt->bindValue(
            ":date",
            date("Y-m-d H:i:s"),
            SQLITE3_TEXT
        );


        $result =
            $stmt->execute();


        if ($result) {


            /* =========================
               CLEAR CART
            ========================= */

            $_SESSION["cart"] = [];


            /* =========================
               CLEAR COUPON
            ========================= */

            unset(
                $_SESSION["coupon_code"]
            );


            unset(
                $_SESSION["coupon_discount"]
            );


            $success =
                $orderNumber;


        } else {

            $error =
                "Unable to place order. Please try again.";

        }

    }

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0"

>

<title>
    Checkout - Saiteja grocery 
</title>

<style>

* {

    box-sizing: border-box;

}


body {

    margin: 0;

    font-family: Arial, sans-serif;

    background: #f5f5f5;

    color: #111;

}


/* =========================
   HEADER
========================= */

.header {

    background: #111;

    color: white;

    padding: 18px 40px;

    display: flex;

    justify-content: space-between;

    align-items: center;

}


.logo {

    font-size: 22px;

    font-weight: bold;

}


.header a {

    color: white;

    text-decoration: none;

    margin-left: 20px;

}


/* =========================
   CONTAINER
========================= */

.container {

    max-width: 1100px;

    margin: 40px auto;

    padding: 20px;

}


h1 {

    margin-bottom: 25px;

}


/* =========================
   GRID
========================= */

.checkout-grid {

    display: grid;

    grid-template-columns:
        1.5fr
        1fr;

    gap: 25px;

}


.box {

    background: white;

    padding: 25px;

    border-radius: 10px;

    border: 1px solid #ddd;

}


.box h2 {

    margin-top: 0;

    margin-bottom: 20px;

}


/* =========================
   FORM
========================= */

label {

    display: block;

    font-weight: bold;

    margin-top: 15px;

    margin-bottom: 7px;

}


input,
textarea {

    width: 100%;

    padding: 12px;

    border: 1px solid #ccc;

    border-radius: 6px;

    font-size: 15px;

}


textarea {

    min-height: 100px;

    resize: vertical;

}


/* =========================
   DISCOUNT
========================= */

.discount-box {

    margin-top: 25px;

    padding: 18px;

    background: #f8f8f8;

    border: 1px solid #ddd;

    border-radius: 8px;

}


.discount-title {

    font-size: 17px;

    font-weight: bold;

    margin-bottom: 12px;

}


.discount-form {

    display: flex;

    gap: 8px;

}


.discount-form input {

    flex: 1;

}


.discount-button {

    background: #111;

    color: white;

    border: none;

    padding: 12px 18px;

    border-radius: 6px;

    cursor: pointer;

    font-weight: bold;

}


.discount-button:hover {

    background: #333;

}


.remove-discount {

    margin-top: 10px;

    background: #eee;

    color: #111;

    border: none;

    padding: 9px 14px;

    border-radius: 6px;

    cursor: pointer;

}


.coupon-success {

    margin-top: 12px;

    padding: 10px;

    background: #e7f6eb;

    color: #176b2c;

    border-radius: 6px;

}


.coupon-error {

    margin-top: 12px;

    padding: 10px;

    background: #ffe5e5;

    color: #b00000;

    border-radius: 6px;

}


/* =========================
   PAYMENT
========================= */

.payment-option {

    margin-top: 12px;

    padding: 15px;

    border: 1px solid #ddd;

    border-radius: 7px;

    cursor: pointer;

}


.payment-option input {

    width: auto;

    margin-right: 8px;

}


.upi-box {

    display: none;

    margin-top: 20px;

    padding: 20px;

    background: #f8f8f8;

    border-radius: 8px;

    text-align: center;

}


.upi-box img {

    max-width: 250px;

    max-height: 250px;

    margin: 15px auto;

    display: block;

    border: 1px solid #ddd;

    padding: 10px;

    background: white;

}


.upi-id {

    font-size: 18px;

    font-weight: bold;

    margin: 10px 0;

}


.instructions {

    color: #555;

    line-height: 1.5;

}


/* =========================
   ORDER SUMMARY
========================= */

.item {

    display: flex;

    justify-content: space-between;

    padding: 12px 0;

    border-bottom: 1px solid #eee;

}


.item-name {

    font-weight: bold;

}


.item-quantity {

    color: #777;

    font-size: 14px;

}


.summary-row {

    display: flex;

    justify-content: space-between;

    padding: 8px 0;

}


.discount-row {

    color: #176b2c;

    font-weight: bold;

}


.total {

    display: flex;

    justify-content: space-between;

    font-size: 21px;

    font-weight: bold;

    margin-top: 15px;

    padding-top: 15px;

    border-top: 2px solid #111;

}


/* =========================
   BUTTON
========================= */

.place-order {

    width: 100%;

    background: #111;

    color: white;

    border: none;

    padding: 15px;

    border-radius: 7px;

    font-size: 16px;

    cursor: pointer;

    margin-top: 20px;

}


.place-order:hover {

    background: #333;

}


/* =========================
   ERROR
========================= */

.error {

    background: #ffe0e0;

    color: #b00000;

    padding: 14px;

    border-radius: 7px;

    margin-bottom: 20px;

}


/* =========================
   SUCCESS
========================= */

.success {

    background: white;

    max-width: 600px;

    margin: 70px auto;

    padding: 40px;

    text-align: center;

    border-radius: 12px;

    box-shadow:
        0 5px 20px rgba(0,0,0,0.08);

}


.success h1 {

    color: #176b2c;

}


.success .order-number {

    font-size: 22px;

    font-weight: bold;

    margin: 20px 0;

}


.success a {

    display: inline-block;

    margin: 8px;

    padding: 12px 20px;

    background: #111;

    color: white;

    text-decoration: none;

    border-radius: 6px;

}


/* =========================
   MOBILE
========================= */

@media (max-width: 800px) {

    .checkout-grid {

        grid-template-columns: 1fr;

    }


    .header {

        padding: 15px 20px;

        flex-direction: column;

        gap: 10px;

    }


    .discount-form {

        flex-direction: column;

    }

}

</style>

</head>

<body>

<!-- HEADER -->

<header class="header">

```
<div class="logo">

    🛒 Saiteja grocery 

</div>


<nav>

    <a href="index.php">
        Home
    </a>

    <a href="products.php">
        Products
    </a>

    <a href="cart.php">
        Cart
    </a>

</nav>
```

</header>

<?php if ($success !== ""): ?>

<!-- SUCCESS -->

<div class="success">

```
<h1>
    ✅ Order Placed Successfully
</h1>


<p>
    Thank you for shopping with Saiteja grocery .
</p>


<div class="order-number">

    Order:

    <?php

    echo htmlspecialchars(
        $success
    );

    ?>

</div>


<p>

    Your order is currently

    <strong>
        Pending
    </strong>.

</p>


<a href="orders.php">
    View My Orders
</a>


<a href="products.php">
    Continue Shopping
</a>
```

</div>

<?php else: ?>

<!-- CHECKOUT -->

<div class="container">

```
<h1>
    Checkout
</h1>


<?php if ($error !== ""): ?>

    <div class="error">

        <?php

        echo htmlspecialchars(
            $error
        );

        ?>

    </div>

<?php endif; ?>


<form
    method="POST"
    id="checkoutForm"
>


    <div class="checkout-grid">


        <!-- CUSTOMER DETAILS -->

        <div class="box">


            <h2>
                Delivery Details
            </h2>


            <label>
                Full Name
            </label>


            <input
                type="text"
                name="name"
                value="<?php

                    echo htmlspecialchars(
                        $name
                    );

                ?>"
                required
            >


            <label>
                Phone Number
            </label>


            <input
                type="tel"
                name="phone"
                value="<?php

                    echo htmlspecialchars(
                        $phone
                    );

                ?>"
                required
            >


            <label>
                Delivery Address
            </label>


            <textarea
                name="address"
                required
            ><?php

                echo htmlspecialchars(
                    $address
                );

            ?></textarea>


            <!-- DISCOUNT -->

            <div class="discount-box">


                <div class="discount-title">

                    🎟️ Claim Discount

                </div>


                <?php if ($couponCode === ""): ?>


                    <div class="discount-form">


                        <input
                            type="text"
                            name="coupon_code"
                            placeholder="Enter coupon code"
                            autocomplete="off"
                        >


                        <button
                            type="submit"
                            name="claim_discount"
                            class="discount-button"
                        >

                            Claim Discount

                        </button>


                    </div>


                <?php else: ?>


                    <div class="coupon-success">

                        🎉 Coupon

                        <strong>
                            <?php

                            echo htmlspecialchars(
                                $couponCode
                            );

                            ?>
                        </strong>

                        applied.

                        You saved

                        <strong>

                            ₹<?php

                            echo number_format(
                                $discount,
                                2
                            );

                            ?>

                        </strong>

                    </div>


                    <button
                        type="submit"
                        name="remove_coupon"
                        class="remove-discount"
                    >

                        Remove Coupon

                    </button>


                <?php endif; ?>


                <?php if (
                    $couponError !== ""
                ): ?>


                    <div class="coupon-error">

                        ❌

                        <?php

                        echo htmlspecialchars(
                            $couponError
                        );

                        ?>

                    </div>


                <?php endif; ?>


                <?php if (
                    $couponMessage !== ""
                    &&
                    $couponError === ""
                ): ?>


                    <div class="coupon-success">

                        <?php

                        echo htmlspecialchars(
                            $couponMessage
                        );

                        ?>

                    </div>


                <?php endif; ?>


            </div>


            <!-- PAYMENT -->

            <h2 style="margin-top:30px;">

                Payment Method

            </h2>


            <!-- COD -->

            <label class="payment-option">


                <input
                    type="radio"
                    name="payment"
                    value="COD"
                    <?php

                    echo
                    ($payment === "COD")
                        ? "checked"
                        : "";

                    ?>
                >


                💵 Cash on Delivery


            </label>


            <!-- UPI -->

            <label class="payment-option">


                <input
                    type="radio"
                    name="payment"
                    value="UPI"
                    <?php

                    echo
                    ($payment === "UPI")
                        ? "checked"
                        : "";

                    ?>
                >


                📱 UPI / QR Payment


            </label>


            <!-- UPI DETAILS -->

            <div
                class="upi-box"
                id="upiBox"
            >


                <?php

                if (
                    $paymentSettings
                    &&
                    !empty(
                        $paymentSettings["qr_image"]
                    )
                ):

                ?>


                    <h3>

                        Scan & Pay

                    </h3>


                    <img
                        src="<?php

                            echo htmlspecialchars(
                                $paymentSettings["qr_image"]
                            );

                        ?>"
                        alt="UPI QR Code"
                    >


                    <?php if (
                        !empty(
                            $paymentSettings["upi_id"]
                        )
                    ): ?>


                        <div class="upi-id">

                            UPI:

                            <?php

                            echo htmlspecialchars(
                                $paymentSettings["upi_id"]
                            );

                            ?>

                        </div>


                    <?php endif; ?>


                    <?php if (
                        !empty(
                            $paymentSettings["instructions"]
                        )
                    ): ?>


                        <p class="instructions">

                            <?php

                            echo nl2br(
                                htmlspecialchars(
                                    $paymentSettings[
                                        "instructions"
                                    ]
                                )
                            );

                            ?>

                        </p>


                    <?php endif; ?>


                    <label>

                        UPI Transaction / Reference ID

                    </label>


                    <input
                        type="text"
                        name="transaction_id"
                        id="transactionId"
                        placeholder="Enter transaction ID"
                        value="<?php

                            echo htmlspecialchars(
                                $transactionId
                            );

                        ?>"
                    >


                <?php else: ?>


                    <p>

                        UPI payment is currently unavailable.

                    </p>


                <?php endif; ?>


            </div>


        </div>


        <!-- ORDER SUMMARY -->

        <div class="box">


            <h2>

                Order Summary

            </h2>


            <?php foreach (
                $cartItems as $item
            ): ?>


                <div class="item">


                    <div>


                        <div class="item-name">

                            <?php

                            echo htmlspecialchars(
                                $item["name"]
                            );

                            ?>

                        </div>


                        <div class="item-quantity">

                            Quantity:

                            <?php

                            echo $item["quantity"];

                            ?>

                        </div>


                    </div>


                    <div>

                        ₹<?php

                        echo number_format(
                            $item["subtotal"],
                            2
                        );

                        ?>

                    </div>


                </div>


            <?php endforeach; ?>


            <!-- SUBTOTAL -->

            <div class="summary-row">

                <span>
                    Subtotal
                </span>

                <span>

                    ₹<?php

                    echo number_format(
                        $total,
                        2
                    );

                    ?>

                </span>

            </div>


            <!-- DISCOUNT -->

            <?php if ($discount > 0): ?>


                <div
                    class="summary-row discount-row"
                >

                    <span>

                        🎟️ Discount

                    </span>


                    <span>

                        -₹<?php

                        echo number_format(
                            $discount,
                            2
                        );

                        ?>

                    </span>

                </div>


            <?php endif; ?>


            <!-- FINAL TOTAL -->

            <div class="total">


                <span>

                    Total

                </span>


                <span>

                    ₹<?php

                    echo number_format(
                        $finalTotal,
                        2
                    );

                    ?>

                </span>


            </div>


            <!-- PLACE ORDER -->

            <button
                type="submit"
                name="place_order"
                class="place-order"
            >

                🛒 Place Order

            </button>


        </div>


    </div>


</form>
```

</div>

<?php endif; ?>

<script>

/* =========================
   UPI DISPLAY
========================= */

const paymentOptions =
    document.querySelectorAll(
        'input[name="payment"]'
    );


const upiBox =
    document.getElementById(
        "upiBox"
    );


const transactionInput =
    document.getElementById(
        "transactionId"
    );


function updatePaymentDisplay() {

    const selected =
        document.querySelector(
            'input[name="payment"]:checked'
        );


    if (!selected) {

        return;

    }


    if (
        selected.value === "UPI"
    ) {

        if (upiBox) {

            upiBox.style.display =
                "block";

        }


        if (transactionInput) {

            transactionInput.required =
                true;

        }

    } else {

        if (upiBox) {

            upiBox.style.display =
                "none";

        }


        if (transactionInput) {

            transactionInput.required =
                false;

        }

    }

}


paymentOptions.forEach(
    function(option) {

        option.addEventListener(
            "change",
            updatePaymentDisplay
        );

    }
);


updatePaymentDisplay();

</script>

</body>

</html>
