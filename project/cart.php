<?php

session_start();

require_once "database/database.php";

/* Create cart if it does not exist */
if (!isset($_SESSION["cart"])) {
    $_SESSION["cart"] = [];
}


/* =========================
   ADD TO CART
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $product_id = intval($_POST["product_id"] ?? 0);
    $quantity = intval($_POST["quantity"] ?? 1);

    if ($product_id <= 0) {
        die("Invalid product.");
    }

    if ($quantity <= 0) {
        $quantity = 1;
    }


    /* Check product exists */

    $stmt = $db->prepare("
        SELECT *
        FROM products
        WHERE id = :id
        LIMIT 1
    ");

    $stmt->bindValue(":id", $product_id, SQLITE3_INTEGER);

    $result = $stmt->execute();

    $product = $result->fetchArray(SQLITE3_ASSOC);


    if (!$product) {
        die("Product not found.");
    }


    /* Check stock */

    if ($product["quantity"] <= 0) {
        die("Product is out of stock.");
    }


    /* Do not allow quantity greater than stock */

    if ($quantity > $product["quantity"]) {
        $quantity = $product["quantity"];
    }


    /* Add to cart */

    if (isset($_SESSION["cart"][$product_id])) {

        $_SESSION["cart"][$product_id] += $quantity;

    } else {

        $_SESSION["cart"][$product_id] = $quantity;

    }


    /* Redirect */

    header("Location: cart.php");
    exit;
}


/* =========================
   REMOVE PRODUCT
========================= */

if (isset($_GET["remove"])) {

    $remove_id = intval($_GET["remove"]);

    if (isset($_SESSION["cart"][$remove_id])) {
        unset($_SESSION["cart"][$remove_id]);
    }

    header("Location: cart.php");
    exit;
}


/* =========================
   CART ITEMS
========================= */

$cart_items = [];

$total = 0;


foreach ($_SESSION["cart"] as $product_id => $quantity) {

    $stmt = $db->prepare("
        SELECT *
        FROM products
        WHERE id = :id
        LIMIT 1
    ");

    $stmt->bindValue(":id", $product_id, SQLITE3_INTEGER);

    $result = $stmt->execute();

    $product = $result->fetchArray(SQLITE3_ASSOC);


    if (!$product) {
        continue;
    }


    $subtotal = $product["selling_price"] * $quantity;

    $total += $subtotal;


    $cart_items[] = [
        "id" => $product["id"],
        "name" => $product["name"],
        "price" => $product["selling_price"],
        "quantity" => $quantity,
        "subtotal" => $subtotal,
        "image" => $product["image"]
    ];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Cart - Saiteja grocery </title>

<link rel="stylesheet" href="css/style.css">

</head>


<body>


<header class="header">

    <div class="logo">
        🛒 Saiteja grocery 
    </div>

    <nav>

        <a href="index.php">Home</a>

        <a href="products.php">Products</a>

        <a href="cart.php">Cart</a>

        <a href="orders.php">Orders</a>

        <a href="login.php">Login</a>

    </nav>

</header>


<main class="container">

<h1>Your Cart</h1>


<?php if (empty($cart_items)) { ?>

    <div class="cart-empty">

        <h2>Your Cart is Empty</h2>

        <p>Add some grocery products to your cart.</p>

        <a href="products.php" class="button">
            Continue Shopping
        </a>

    </div>


<?php } else { ?>


    <?php foreach ($cart_items as $item) { ?>

        <div class="cart-item">

            <div class="cart-image">

                <?php if (!empty($item["image"])) { ?>

                    <img
                        src="<?php echo htmlspecialchars($item["image"]); ?>"
                        alt="<?php echo htmlspecialchars($item["name"]); ?>"
                        style="width:100px;"
                    >

                <?php } else { ?>

                    🛒

                <?php } ?>

            </div>


            <div class="cart-info">

                <h3>
                    <?php echo htmlspecialchars($item["name"]); ?>
                </h3>

                <p>
                    Price:
                    ₹<?php echo number_format($item["price"], 2); ?>
                </p>

                <p>
                    Quantity:
                    <?php echo $item["quantity"]; ?>
                </p>

                <p>

                    Subtotal:

                    ₹<?php echo number_format($item["subtotal"], 2); ?>

                </p>


                <a
                    href="cart.php?remove=<?php echo $item["id"]; ?>"
                    class="button"
                >
                    Remove
                </a>

            </div>

        </div>

    <?php } ?>


    <hr>


    <h2>
        Total:
        ₹<?php echo number_format($total, 2); ?>
    </h2>


    <br>


    <a href="products.php" class="button">
        Continue Shopping
    </a>


    <a href="checkout.php" class="button">
        Proceed to Checkout
    </a>


<?php } ?>


</main>


<footer>

    <p>© 2026 Saiteja grocery </p>

</footer>


</body>

</html>