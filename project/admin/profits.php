<?php

session_start();

require_once "../database/database.php";


/*
|--------------------------------------------------------------------------
| CREATE PROFIT SETTINGS TABLE
|--------------------------------------------------------------------------
*/

$db->exec("
    CREATE TABLE IF NOT EXISTS profit_settings (
        id INTEGER PRIMARY KEY,
        cleared_at TEXT
    )
");


/*
|--------------------------------------------------------------------------
| CLEAR ALL PROFITS
|--------------------------------------------------------------------------
*/

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["clear_all_profits"])
) {

    $clearedAt = date("Y-m-d H:i:s");

    $stmt = $db->prepare("
        INSERT OR REPLACE INTO profit_settings
        (id, cleared_at)
        VALUES
        (1, :cleared_at)
    ");

    $stmt->bindValue(
        ":cleared_at",
        $clearedAt,
        SQLITE3_TEXT
    );

    $stmt->execute();

    header("Location: profits.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| GET LAST PROFIT CLEAR TIME
|--------------------------------------------------------------------------
*/

$clearedAt = $db->querySingle("
    SELECT cleared_at
    FROM profit_settings
    WHERE id = 1
");


/*
|--------------------------------------------------------------------------
| GET ORDERS
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        id,
        order_number,
        customer,
        items,
        amount,
        status,
        date
    FROM orders
    WHERE status != 'Cancelled'
";


if (!empty($clearedAt)) {

    $sql .= "
        AND date > :cleared_at
    ";

}


$sql .= "
    ORDER BY id DESC
";


$stmt = $db->prepare($sql);


if (!empty($clearedAt)) {

    $stmt->bindValue(
        ":cleared_at",
        $clearedAt,
        SQLITE3_TEXT
    );

}


$result = $stmt->execute();


/*
|--------------------------------------------------------------------------
| PROFIT CALCULATIONS
|--------------------------------------------------------------------------
*/

$totalSales = 0;

$totalCost = 0;

$totalProfit = 0;

$profitRows = [];


/*
|--------------------------------------------------------------------------
| READ ORDERS
|--------------------------------------------------------------------------
*/

while (
    $order = $result->fetchArray(SQLITE3_ASSOC)
) {

    $orderSales = (float)$order["amount"];

    $orderCost = 0;


    /*
    |--------------------------------------------------------------------------
    | DECODE ORDER ITEMS
    |--------------------------------------------------------------------------
    */

    $items = json_decode(
        $order["items"],
        true
    );


    if (is_array($items)) {

        foreach ($items as $item) {

            $productId =
                (int)($item["product_id"] ?? 0);

            $quantity =
                (int)($item["quantity"] ?? 1);


            /*
            |--------------------------------------------------------------------------
            | FIND PRODUCT PURCHASE PRICE
            |--------------------------------------------------------------------------
            */

            if (
                $productId > 0
                && $quantity > 0
            ) {

                $productStmt = $db->prepare("
                    SELECT purchase_price
                    FROM products
                    WHERE id = :id
                ");

                $productStmt->bindValue(
                    ":id",
                    $productId,
                    SQLITE3_INTEGER
                );

                $productResult =
                    $productStmt->execute();

                $product =
                    $productResult->fetchArray(
                        SQLITE3_ASSOC
                    );


                if ($product) {

                    $purchasePrice =
                        (float)$product["purchase_price"];


                    $orderCost +=
                        $purchasePrice * $quantity;

                }

            }

        }

    }


    /*
    |--------------------------------------------------------------------------
    | CALCULATE ORDER PROFIT
    |--------------------------------------------------------------------------
    */

    $orderProfit =
        $orderSales - $orderCost;


    /*
    |--------------------------------------------------------------------------
    | ADD TO TOTALS
    |--------------------------------------------------------------------------
    */

    $totalSales +=
        $orderSales;


    $totalCost +=
        $orderCost;


    $totalProfit +=
        $orderProfit;


    /*
    |--------------------------------------------------------------------------
    | STORE ROW
    |--------------------------------------------------------------------------
    */

    $profitRows[] = [

        "order_number" =>
            $order["order_number"],

        "customer" =>
            $order["customer"],

        "sales" =>
            $orderSales,

        "cost" =>
            $orderCost,

        "profit" =>
            $orderProfit,

        "status" =>
            $order["status"],

        "date" =>
            $order["date"]

    ];

}


/*
|--------------------------------------------------------------------------
| PROFIT MARGIN
|--------------------------------------------------------------------------
*/

if ($totalSales > 0) {

    $profitMargin =
        ($totalProfit / $totalSales) * 100;

} else {

    $profitMargin = 0;

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
    Profits - Saiteja grocery 
</title>

<style>

/* RESET */

* {

    margin: 0;

    padding: 0;

    box-sizing: border-box;

}


/* BODY */

body {

    font-family: Arial, sans-serif;

    background: #f5f5f5;

    color: #111;

}


/* SIDEBAR */

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

    font-size: 23px;

    font-weight: bold;

    margin-bottom: 5px;

}


.admin-title {

    text-align: center;

    color: #aaa;

    font-size: 13px;

    margin-bottom: 30px;

}


.menu a {

    display: block;

    color: #ddd;

    text-decoration: none;

    padding: 13px 15px;

    margin-bottom: 5px;

    border-radius: 6px;

}


.menu a:hover {

    background: white;

    color: #111;

}


.menu a.active {

    background: white;

    color: #111;

}


/* LOGOUT */

.logout {

    margin-top: 25px;

    background: #c62828;

    color: white !important;

}


/* MAIN */

.main {

    margin-left: 250px;

    padding: 30px;

}


h1 {

    font-size: 30px;

}


.subtitle {

    color: #777;

    margin-top: 7px;

    margin-bottom: 25px;

}


/* TOP ACTION */

.top-actions {

    display: flex;

    justify-content: flex-end;

    margin-bottom: 25px;

}


.clear-button {

    background: #c62828;

    color: white;

    border: none;

    padding: 12px 20px;

    border-radius: 6px;

    cursor: pointer;

    font-weight: bold;

    font-size: 14px;

}


.clear-button:hover {

    background: #a51f1f;

}


/* STATISTICS */

.stats {

    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 15px;

    margin-bottom: 30px;

}


.card {

    background: white;

    padding: 22px;

    border-radius: 10px;

    border: 1px solid #e5e5e5;

}


.card-title {

    color: #777;

    font-size: 13px;

    margin-bottom: 10px;

}


.card-number {

    font-size: 25px;

    font-weight: bold;

}


.profit {

    color: #176b2c;

}


.cost {

    color: #c62828;

}


.margin {

    color: #1565c0;

}


/* TABLE */

.table-box {

    background: white;

    border-radius: 10px;

    overflow-x: auto;

    border: 1px solid #ddd;

}


table {

    width: 100%;

    border-collapse: collapse;

    min-width: 900px;

}


th {

    background: #111;

    color: white;

    padding: 14px;

    text-align: left;

    font-size: 14px;

}


td {

    padding: 14px;

    border-bottom: 1px solid #eee;

    font-size: 14px;

}


.profit-positive {

    color: #176b2c;

    font-weight: bold;

}


.profit-negative {

    color: #c62828;

    font-weight: bold;

}


.no-data {

    text-align: center;

    padding: 50px;

    color: #777;

    font-size: 16px;

}


/* RESPONSIVE */

@media(max-width:1100px) {

    .stats {

        grid-template-columns:
            repeat(2, 1fr);

    }

}


@media(max-width:900px) {

    .sidebar {

        position: relative;

        width: 100%;

        height: auto;

    }


    .main {

        margin-left: 0;

        padding: 20px;

    }

}


@media(max-width:600px) {

    .stats {

        grid-template-columns: 1fr;

    }

}

</style>

</head>

<body>

<!-- SIDEBAR -->

<aside class="sidebar">

```
<div class="logo">

    SAITEJA grocery 

</div>


<div class="admin-title">

    ADMIN PANEL

</div>


<nav class="menu">


    <a href="index.php">

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


    <a href="payment_settings.php">

        ⚙️ Payment Settings

    </a>


    <a href="sales.php">

        📊 Sales

    </a>


    <a
        href="profits.php"
        class="active"
    >

        💰 Profits

    </a>


    <a href="discounts.php">

        🏷️ Discounts

    </a>


    <a href="users.php">

        👥 Users

    </a>


    <a href="helpdesk.php">

        ❓ Help Desk

    </a>


    <a href="ml_questions.php">

        🤖 ML Questions

    </a>


    <a href="../index.php">

        🌐 View Website

    </a>


    <a
        href="login.php"
        class="logout"
    >

        🚪 Logout

    </a>


</nav>
```

</aside>

<!-- MAIN -->

<main class="main">

```
<h1>

    Profit Management

</h1>


<p class="subtitle">

    Track sales, product costs and actual profit.

</p>


<!-- CLEAR PROFITS -->

<div class="top-actions">


    <form
        method="POST"
        onsubmit="
            return confirm(
                'Are you sure you want to clear all profit records? Orders will NOT be deleted.'
            );
        "
    >

        <button
            type="submit"
            name="clear_all_profits"
            class="clear-button"
        >

            🗑️ Clear All Profits

        </button>

    </form>


</div>


<!-- STATISTICS -->

<div class="stats">


    <div class="card">

        <div class="card-title">

            Total Sales

        </div>


        <div class="card-number">

            ₹<?php

            echo number_format(
                $totalSales,
                2
            );

            ?>

        </div>

    </div>


    <div class="card">

        <div class="card-title">

            Total Product Cost

        </div>


        <div class="card-number cost">

            ₹<?php

            echo number_format(
                $totalCost,
                2
            );

            ?>

        </div>

    </div>


    <div class="card">

        <div class="card-title">

            Total Profit

        </div>


        <div class="card-number profit">

            ₹<?php

            echo number_format(
                $totalProfit,
                2
            );

            ?>

        </div>

    </div>


    <div class="card">

        <div class="card-title">

            Profit Margin

        </div>


        <div class="card-number margin">

            <?php

            echo number_format(
                $profitMargin,
                2
            );

            ?>%

        </div>

    </div>


</div>


<!-- PROFIT TABLE -->

<div class="table-box">


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
                    Sales
                </th>

                <th>
                    Product Cost
                </th>

                <th>
                    Profit
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


        <?php if (count($profitRows) > 0): ?>


            <?php foreach ($profitRows as $row): ?>


                <tr>


                    <td>

                        <strong>

                            #<?php

                            echo htmlspecialchars(
                                $row["order_number"]
                            );

                            ?>

                        </strong>

                    </td>


                    <td>

                        <?php

                        echo htmlspecialchars(
                            $row["customer"]
                        );

                        ?>

                    </td>


                    <td>

                        ₹<?php

                        echo number_format(
                            $row["sales"],
                            2
                        );

                        ?>

                    </td>


                    <td>

                        ₹<?php

                        echo number_format(
                            $row["cost"],
                            2
                        );

                        ?>

                    </td>


                    <td class="<?php

                        echo $row["profit"] >= 0
                            ? "profit-positive"
                            : "profit-negative";

                    ?>">

                        ₹<?php

                        echo number_format(
                            $row["profit"],
                            2
                        );

                        ?>

                    </td>


                    <td>

                        <?php

                        echo htmlspecialchars(
                            $row["status"]
                        );

                        ?>

                    </td>


                    <td>

                        <?php

                        echo htmlspecialchars(
                            $row["date"]
                        );

                        ?>

                    </td>


                </tr>


            <?php endforeach; ?>


        <?php else: ?>


            <tr>

                <td
                    colspan="7"
                    class="no-data"
                >

                    No profit records found.

                </td>

            </tr>


        <?php endif; ?>


        </tbody>


    </table>


</div>
```

</main>

</body>

</html>
