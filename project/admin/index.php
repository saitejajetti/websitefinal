<?php

require_once "../database/database.php";


/* =========================
   DASHBOARD STATISTICS
========================= */

$totalProducts = $db->querySingle(
    "SELECT COUNT(*) FROM products"
);

$totalOrders = $db->querySingle(
    "SELECT COUNT(*) FROM orders"
);

$totalUsers = $db->querySingle(
    "SELECT COUNT(*) FROM users"
);

$totalSales = $db->querySingle("
    SELECT COALESCE(SUM(amount), 0)
    FROM orders
    WHERE status != 'Cancelled'
");

$totalPayments = $db->querySingle("
    SELECT COALESCE(SUM(amount), 0)
    FROM orders
    WHERE status != 'Cancelled'
");


/* =========================
   RECENT ORDERS
========================= */

$recentOrders = $db->query("
    SELECT
        order_number,
        customer,
        amount,
        status,
        date
    FROM orders
    ORDER BY id DESC
    LIMIT 5
");

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Admin Dashboard - Saiteja Groceries</title>


<style>

/* =========================
   RESET
========================= */

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}


/* =========================
   BODY
========================= */

body {
    font-family: Arial, Helvetica, sans-serif;
    background: #f5f5f5;
    color: #111;
}


/* =========================
   SIDEBAR
========================= */

.sidebar {

    position: fixed;

    left: 0;
    top: 0;

    width: 250px;
    height: 100vh;

    background: #111;
    color: white;

    padding: 25px 15px;

    overflow-y: auto;
}


.logo {

    text-align: center;

    font-size: 24px;
    font-weight: bold;

    margin-bottom: 5px;
}


.admin-title {

    text-align: center;

    color: #aaa;

    font-size: 14px;

    margin-bottom: 30px;
}


.menu a {

    display: block;

    text-decoration: none;

    color: #ddd;

    padding: 13px 15px;

    margin-bottom: 5px;

    border-radius: 6px;

    transition: 0.2s;
}


.menu a:hover,
.menu a.active {

    background: white;
    color: #111;
}


.logout {

    margin-top: 25px !important;

    background: #c62828;

    color: white !important;
}


.logout:hover {

    background: #e53935 !important;

    color: white !important;
}


/* =========================
   MAIN
========================= */

.main {

    margin-left: 250px;

    padding: 30px;

    min-height: 100vh;
}


.topbar {

    margin-bottom: 30px;
}


.topbar h1 {

    font-size: 30px;
}


.welcome {

    color: #666;

    font-size: 15px;

    margin-top: 6px;
}


/* =========================
   DASHBOARD CARDS
========================= */

.cards {

    display: grid;

    grid-template-columns: repeat(3, 1fr);

    gap: 20px;

    margin-bottom: 35px;
}


.card {

    background: white;

    padding: 25px;

    border-radius: 10px;

    border: 1px solid #e5e5e5;

    transition: 0.2s;
}


.card:hover {

    transform: translateY(-3px);

    box-shadow: 0 8px 20px rgba(0,0,0,0.08);
}


.card-icon {

    font-size: 30px;

    margin-bottom: 15px;
}


.card h3 {

    font-size: 15px;

    color: #666;

    margin-bottom: 8px;
}


.number {

    font-size: 28px;

    font-weight: bold;
}


.card-link {

    display: inline-block;

    margin-top: 15px;

    color: #111;

    text-decoration: none;

    font-size: 14px;

    font-weight: bold;
}


.card-link:hover {

    text-decoration: underline;
}


/* =========================
   SECTION TITLE
========================= */

.section-title {

    font-size: 22px;

    margin-bottom: 15px;
}


/* =========================
   QUICK ACTIONS
========================= */

.quick-actions {

    display: grid;

    grid-template-columns: repeat(5, 1fr);

    gap: 15px;

    margin-bottom: 35px;
}


.action {

    background: #111;

    color: white;

    text-decoration: none;

    text-align: center;

    padding: 18px 10px;

    border-radius: 7px;

    transition: 0.2s;
}


.action:hover {

    background: #333;
}


/* =========================
   ORDERS TABLE
========================= */

.orders-box {

    background: white;

    border-radius: 10px;

    overflow-x: auto;

    border: 1px solid #e5e5e5;
}


table {

    width: 100%;

    border-collapse: collapse;
}


th {

    background: #111;

    color: white;

    padding: 15px;

    text-align: left;
}


td {

    padding: 15px;

    border-bottom: 1px solid #eee;
}


.no-orders {

    text-align: center;

    padding: 35px;

    color: #777;
}


/* =========================
   STATUS
========================= */

.status {

    display: inline-block;

    padding: 6px 12px;

    border-radius: 20px;

    font-size: 12px;

    font-weight: bold;
}


.status-pending {

    background: #fff0c2;

    color: #856404;
}


.status-confirmed {

    background: #d9f7df;

    color: #176b2c;
}


.status-delivered {

    background: #d9f7df;

    color: #176b2c;
}


.status-cancelled {

    background: #ffe0e0;

    color: #b00000;
}


/* =========================
   MOBILE
========================= */

@media (max-width: 1100px) {

    .cards {

        grid-template-columns: repeat(2, 1fr);
    }

    .quick-actions {

        grid-template-columns: repeat(3, 1fr);
    }

}


@media (max-width: 700px) {

    .sidebar {

        position: relative;

        width: 100%;

        height: auto;
    }


    .main {

        margin-left: 0;

        padding: 20px;
    }


    .cards {

        grid-template-columns: 1fr;
    }


    .quick-actions {

        grid-template-columns: 1fr;
    }

}

</style>

</head>


<body>


<!-- =========================
     SIDEBAR
========================= -->

<aside class="sidebar">


    <div class="logo">
        SAITEJA GROCERIES
    </div>


    <div class="admin-title">
       WELCOME TO  ADMIN PANEL
    </div>


    <nav class="menu">


        <a href="index.php" class="active">
            🏠 Dashboard
        </a>


        <a href="products.php">
            📦 Products
        </a>


        <a href="add_product.php">
            ➕ Add Product
        </a>


        <a href="orders.php">
            🛒 Orders
        </a>


        <a href="payments.php">
            💳 Payments
        </a>


        <!-- PAYMENT SETTINGS ADDED -->

        <a href="payment_settings.php">
            ⚙️ Payment Settings
        </a>


        <a href="sales.php">
            📊 Sales
        </a>


        <a href="profits.php">
            💰 Profits
        </a>


        <a href="discounts.php">
            🏷️ Discounts
        </a>


        <a href="users.php">
            👥 Users
        </a>


        <a href="databaseusers.php">
            👥 Customer Database
        </a>


        <a href="helpdesk.php">
            ❓ Help Desk
        </a>


        <a href="../index.php">
            🌐 View Website
        </a>


        <a href="login.php" class="logout">
            🚪 Logout
        </a>


    </nav>

</aside>



<!-- =========================
     MAIN CONTENT
========================= -->

<main class="main">


    <!-- HEADER -->

    <div class="topbar">

        <h1>
            Admin Dashboard
        </h1>

        <p class="welcome">
            Welcome to Saiteja Groceries administration panel.
        </p>

    </div>



    <!-- =========================
         DASHBOARD CARDS
    ========================= -->

    <div class="cards">


        <!-- PRODUCTS -->

        <div class="card">

            <div class="card-icon">
                📦
            </div>

            <h3>
                Total Products
            </h3>

            <div class="number">
                <?php echo $totalProducts; ?>
            </div>

            <a href="products.php"
               class="card-link">

                Manage Products →

            </a>

        </div>



        <!-- ORDERS -->

        <div class="card">

            <div class="card-icon">
                🛒
            </div>

            <h3>
                Total Orders
            </h3>

            <div class="number">
                <?php echo $totalOrders; ?>
            </div>

            <a href="orders.php"
               class="card-link">

                View Orders →

            </a>

        </div>



        <!-- PAYMENTS -->

        <div class="card">

            <div class="card-icon">
                💳
            </div>

            <h3>
                Total Payments
            </h3>

            <div class="number">

                ₹<?php

                echo number_format(
                    $totalPayments,
                    2
                );

                ?>

            </div>

            <a href="payments.php"
               class="card-link">

                View Payments →

            </a>

        </div>



        <!-- SALES -->

        <div class="card">

            <div class="card-icon">
                📊
            </div>

            <h3>
                Total Sales
            </h3>

            <div class="number">

                ₹<?php

                echo number_format(
                    $totalSales,
                    2
                );

                ?>

            </div>

            <a href="sales.php"
               class="card-link">

                View Sales →

            </a>

        </div>



        <!-- PROFIT -->

        <div class="card">

            <div class="card-icon">
                💰
            </div>

            <h3>
                Total Profit
            </h3>

            <div class="number">
                ₹0.00
            </div>

            <a href="profits.php"
               class="card-link">

                View Profit →

            </a>

        </div>



        <!-- USERS -->

        <div class="card">

            <div class="card-icon">
                👥
            </div>

            <h3>
                Total Users
            </h3>

            <div class="number">
                <?php echo $totalUsers; ?>
            </div>

            <a href="users.php"
               class="card-link">

                Manage Users →

            </a>

        </div>


    </div>



    <!-- =========================
         QUICK ACTIONS
    ========================= -->

    <h2 class="section-title">
        Quick Actions
    </h2>


    <div class="quick-actions">


        <a href="add_product.php"
           class="action">

            ➕ Add Product

        </a>


        <a href="discounts.php"
           class="action">

            🏷️ Create Discount

        </a>


        <a href="helpdesk.php"
           class="action">

            ❓ Manage Help Desk

        </a>


        <a href="ml_questions.php"
           class="action">

            🤖 Manage ML Questions

        </a>


        <!-- PAYMENT SETTINGS ADDED -->

        <a href="payment_settings.php"
           class="action">

            ⚙️ Payment Settings

        </a>


    </div>



    <!-- =========================
         RECENT ORDERS
    ========================= -->

    <h2 class="section-title">
        Recent Orders
    </h2>


    <div class="orders-box">


        <table>


            <thead>

                <tr>

                    <th>
                        Order ID
                    </th>

                    <th>
                        Customer
                    </th>

                    <th>
                        Amount
                    </th>

                    <th>
                        Status
                    </th>

                    <th>
                        Date
                    </th>

                </tr>

            </thead>


            <tbody>


            <?php

            $hasOrders = false;


            while (
                $order =
                $recentOrders->fetchArray(
                    SQLITE3_ASSOC
                )
            ) {

                $hasOrders = true;


                $status =
                    $order['status'];


                $statusClass =
                    "status-pending";


                if (
                    $status === "Confirmed"
                ) {

                    $statusClass =
                        "status-confirmed";

                }


                if (
                    $status === "Delivered"
                ) {

                    $statusClass =
                        "status-delivered";

                }


                if (
                    $status === "Cancelled"
                ) {

                    $statusClass =
                        "status-cancelled";

                }

            ?>


                <tr>


                    <td>

                        #

                        <?php

                        echo htmlspecialchars(
                            $order['order_number']
                        );

                        ?>

                    </td>


                    <td>

                        <?php

                        echo htmlspecialchars(
                            $order['customer']
                        );

                        ?>

                    </td>


                    <td>

                        ₹<?php

                        echo number_format(
                            $order['amount'],
                            2
                        );

                        ?>

                    </td>


                    <td>

                        <span
                            class="status <?php
                                echo $statusClass;
                            ?>"
                        >

                            <?php

                            echo htmlspecialchars(
                                $status
                            );

                            ?>

                        </span>

                    </td>


                    <td>

                        <?php

                        echo htmlspecialchars(
                            $order['date']
                        );

                        ?>

                    </td>


                </tr>


            <?php

            }

            ?>


            <?php if (!$hasOrders): ?>


                <tr>

                    <td
                        colspan="5"
                        class="no-orders"
                    >

                        No orders yet.

                    </td>

                </tr>


            <?php endif; ?>


            </tbody>


        </table>


    </div>


</main>


</body>

</html>