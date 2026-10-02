<?php

session_start();

require_once "database/database.php";


// Get logged-in customer's email
$user_email = $_SESSION['user_email'] ?? "";


// If customer is not logged in
if ($user_email === "") {

    header("Location: login.php");
    exit;
}


// Get customer's orders
$stmt = $db->prepare("
    SELECT *
    FROM orders
    WHERE email = :email
    ORDER BY id DESC
");

$stmt->bindValue(":email", $user_email, SQLITE3_TEXT);

$result = $stmt->execute();

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Orders - Saiteja grocery </title>

    <link rel="stylesheet" href="css/style.css">

    <style>

        .order-card {
            background: white;
            padding: 25px;
            margin: 20px 0;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            flex-wrap: wrap;
            border-bottom: 1px solid #ddd;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }

        .status {
            padding: 6px 12px;
            border-radius: 5px;
            background: #fff3cd;
            color: #856404;
            font-weight: bold;
        }

        .items {
            background: #f7f7f7;
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
        }

        .empty {
            background: white;
            padding: 40px;
            text-align: center;
            border-radius: 10px;
        }

    </style>

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

    <h1>My Orders</h1>


    <?php

    $has_orders = false;

    while ($order = $result->fetchArray(SQLITE3_ASSOC)) {

        $has_orders = true;

    ?>

        <div class="order-card">

            <div class="order-header">

                <div>

                    <h2>
                        Order #<?php echo htmlspecialchars($order['order_number']); ?>
                    </h2>

                    <p>
                        Date:
                        <?php echo htmlspecialchars($order['date']); ?>
                    </p>

                </div>


                <div>

                    <span class="status">

                        <?php echo htmlspecialchars($order['status']); ?>

                    </span>

                </div>

            </div>


            <div>

                <p>
                    <strong>Customer:</strong>
                    <?php echo htmlspecialchars($order['customer']); ?>
                </p>

                <p>
                    <strong>Phone:</strong>
                    <?php echo htmlspecialchars($order['phone']); ?>
                </p>

                <p>
                    <strong>Address:</strong>
                    <?php echo htmlspecialchars($order['address']); ?>
                </p>

                <p>
                    <strong>Payment:</strong>
                    <?php echo htmlspecialchars($order['payment']); ?>
                </p>

            </div>


            <div class="items">

                <h3>Products</h3>

                <?php

                $items = json_decode($order['items'], true);

                if (is_array($items)) {

                    foreach ($items as $item) {

                ?>

                    <p>

                        <?php echo htmlspecialchars($item['name']); ?>

                        ×

                        <?php echo htmlspecialchars($item['quantity']); ?>

                        —

                        ₹<?php echo number_format($item['subtotal'], 2); ?>

                    </p>

                <?php

                    }

                }

                ?>

            </div>


            <h2>

                Total:

                ₹<?php echo number_format($order['amount'], 2); ?>

            </h2>

        </div>


    <?php } ?>


    <?php if (!$has_orders) { ?>

        <div class="empty">

            <h2>You have no orders yet.</h2>

            <p>Start shopping and place your first order.</p>

            <br>

            <a
                href="products.php"
                class="button"
            >
                Start Shopping
            </a>

        </div>

    <?php } ?>


</main>


<footer>

    <p>© 2026 Saiteja grocery </p>

</footer>


</body>

</html>