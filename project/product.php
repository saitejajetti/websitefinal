<?php

require_once "database/database.php";

$product_id = $_GET['id'] ?? 0;

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

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?php echo htmlspecialchars($product['name']); ?> - Saiteja grocery 
    </title>

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

    <h1>Product Details</h1>

    <div class="product-details">

        <div class="product-image large">

            <?php if (!empty($product['image'])) { ?>

                <img
                    src="<?php echo htmlspecialchars($product['image']); ?>"
                    alt="<?php echo htmlspecialchars($product['name']); ?>"
                    style="max-width:300px;"
                >

            <?php } else { ?>

                🛒

            <?php } ?>

        </div>


        <div>

            <h2>
                <?php echo htmlspecialchars($product['name']); ?>
            </h2>

            <p>
                <?php echo htmlspecialchars($product['description']); ?>
            </p>

            <h2>
                ₹<?php echo number_format($product['selling_price'], 2); ?>
            </h2>

            <?php if ($product['discount'] > 0) { ?>

                <p>
                    Discount:
                    <?php echo htmlspecialchars($product['discount']); ?>%
                </p>

            <?php } ?>


            <?php if ($product['quantity'] > 0) { ?>

                <p>
                    Available: In Stock
                </p>

                <form action="cart.php" method="POST">

                    <input
                        type="hidden"
                        name="product_id"
                        value="<?php echo $product['id']; ?>"
                    >

                    <label>Quantity</label>

                    <input
                        type="number"
                        name="quantity"
                        value="1"
                        min="1"
                        max="<?php echo $product['quantity']; ?>"
                    >

                    <br><br>

                    <button
                        type="submit"
                        class="button"
                    >
                        Add to Cart
                    </button>

                </form>

            <?php } else { ?>

                <p>
                    <strong>Out of Stock</strong>
                </p>

            <?php } ?>

        </div>

    </div>

</main>


<footer>

    <p>© 2026 Saiteja grocery </p>

</footer>

</body>
</html>