<?php

session_start();

require_once "../database/database.php";


/* =========================
   DELETE SELECTED ORDERS
========================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["delete_selected_orders"])
) {

    $selectedOrders = $_POST["selected_orders"] ?? [];

    if (is_array($selectedOrders)) {

        $stmt = $db->prepare("
            DELETE FROM orders
            WHERE id = :id
        ");

        foreach ($selectedOrders as $id) {

            $id = (int)$id;

            if ($id > 0) {

                $stmt->bindValue(
                    ":id",
                    $id,
                    SQLITE3_INTEGER
                );

                $stmt->execute();
            }
        }
    }

    header("Location: orders.php");
    exit;
}


/* =========================
   CLEAR ALL ORDERS
========================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["clear_all_orders"])
) {

    $db->exec("DELETE FROM orders");

    $db->exec("
        DELETE FROM sqlite_sequence
        WHERE name = 'orders'
    ");

    header("Location: orders.php");
    exit;
}


/* =========================
   UPDATE STATUS
========================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["update_status"])
) {

    $orderId = (int)($_POST["order_id"] ?? 0);

    $status = $_POST["status"] ?? "";

    $allowed = [
        "Pending",
        "Confirmed",
        "Delivered",
        "Cancelled"
    ];

    if (
        $orderId > 0 &&
        in_array($status, $allowed, true)
    ) {

        $stmt = $db->prepare("
            UPDATE orders
            SET status = :status
            WHERE id = :id
        ");

        $stmt->bindValue(
            ":status",
            $status,
            SQLITE3_TEXT
        );

        $stmt->bindValue(
            ":id",
            $orderId,
            SQLITE3_INTEGER
        );

        $stmt->execute();
    }

    header("Location: orders.php");
    exit;
}


/* =========================
   SEARCH
========================= */

$search = trim($_GET["search"] ?? "");

$statusFilter = $_GET["status"] ?? "";


$sql = "
    SELECT *
    FROM orders
    WHERE 1=1
";


if ($search !== "") {

    $sql .= "
        AND (
            order_number LIKE :search
            OR customer LIKE :search
            OR phone LIKE :search
            OR email LIKE :search
        )
    ";
}


if ($statusFilter !== "") {

    $sql .= "
        AND status = :status
    ";
}


$sql .= "
    ORDER BY id DESC
";


$stmt = $db->prepare($sql);


if ($search !== "") {

    $stmt->bindValue(
        ":search",
        "%" . $search . "%",
        SQLITE3_TEXT
    );
}


if ($statusFilter !== "") {

    $stmt->bindValue(
        ":status",
        $statusFilter,
        SQLITE3_TEXT
    );
}


$result = $stmt->execute();


/* =========================
   STATISTICS
========================= */

$totalOrders = $db->querySingle("
    SELECT COUNT(*) FROM orders
");


$pendingOrders = $db->querySingle("
    SELECT COUNT(*)
    FROM orders
    WHERE status='Pending'
");


$confirmedOrders = $db->querySingle("
    SELECT COUNT(*)
    FROM orders
    WHERE status='Confirmed'
");


$deliveredOrders = $db->querySingle("
    SELECT COUNT(*)
    FROM orders
    WHERE status='Delivered'
");


$cancelledOrders = $db->querySingle("
    SELECT COUNT(*)
    FROM orders
    WHERE status='Cancelled'
");

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Orders - Saiteja grocery </title>


<style>

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}


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
}


.logo {

    text-align: center;

    font-size: 22px;

    font-weight: bold;

    margin-bottom: 5px;
}


.admin-title {

    text-align: center;

    color: #aaa;

    margin-bottom: 25px;

    font-size: 13px;
}


.menu a {

    display: block;

    color: #ddd;

    text-decoration: none;

    padding: 13px;

    margin-bottom: 5px;

    border-radius: 6px;
}


.menu a:hover,
.menu a.active {

    background: white;

    color: black;
}


.logout {

    background: #c62828;

    margin-top: 20px;
}


/* MAIN */

.main {

    margin-left: 250px;

    padding: 30px;
}


h1 {

    font-size: 30px;

    margin-bottom: 7px;
}


.subtitle {

    color: #777;

    margin-bottom: 25px;
}


/* ACTION BAR */

.action-bar {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 20px;
}


.select-all {

    display: flex;

    align-items: center;

    gap: 8px;

    font-weight: bold;
}


.select-all input,
.order-checkbox {

    width: 18px;

    height: 18px;

    cursor: pointer;
}


.clear-button {

    background: #c62828;

    color: white;

    border: none;

    padding: 12px 20px;

    border-radius: 6px;

    cursor: pointer;

    font-weight: bold;
}


.clear-button:hover {

    background: #a51f1f;
}


.delete-selected {

    background: #d32f2f;

    color: white;

    border: none;

    padding: 12px 20px;

    border-radius: 6px;

    cursor: pointer;

    font-weight: bold;

    margin-top: 15px;
}


.delete-selected:hover {

    background: #a51f1f;
}


/* STATS */

.stats {

    display: grid;

    grid-template-columns:
        repeat(5, 1fr);

    gap: 15px;

    margin-bottom: 25px;
}


.card {

    background: white;

    padding: 20px;

    border-radius: 10px;

    border: 1px solid #ddd;
}


.card-title {

    color: #777;

    font-size: 14px;

    margin-bottom: 8px;
}


.card-number {

    font-size: 26px;

    font-weight: bold;
}


/* FILTER */

.filters {

    background: white;

    padding: 20px;

    border-radius: 10px;

    margin-bottom: 25px;

    border: 1px solid #ddd;
}


.filters form {

    display: flex;

    gap: 10px;
}


.filters input,
.filters select {

    padding: 11px;

    border: 1px solid #ccc;

    border-radius: 6px;
}


.search-button {

    background: #111;

    color: white;

    border: none;

    padding: 10px 20px;

    border-radius: 6px;

    cursor: pointer;
}


.reset-button {

    background: #eee;

    color: #111;

    padding: 10px 20px;

    border-radius: 6px;

    text-decoration: none;
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

    min-width: 1200px;
}


th {

    background: #111;

    color: white;

    padding: 14px;

    text-align: left;
}


td {

    padding: 14px;

    border-bottom: 1px solid #eee;

    vertical-align: middle;
}


.status-form {

    display: flex;

    gap: 5px;
}


.status-form select {

    padding: 6px;

    border: 1px solid #ccc;

    border-radius: 5px;
}


.status-form button {

    background: #111;

    color: white;

    border: none;

    padding: 6px 10px;

    border-radius: 5px;

    cursor: pointer;
}


.no-orders {

    text-align: center;

    padding: 50px;

    color: #777;
}


@media(max-width:900px) {

    .sidebar {

        position: relative;

        width: 100%;

        height: auto;
    }

    .main {

        margin-left: 0;
    }

    .stats {

        grid-template-columns: repeat(2, 1fr);
    }

}

</style>

</head>


<body>


<!-- SIDEBAR -->

<aside class="sidebar">

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

        <a href="orders.php" class="active">
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

        <a href="profits.php">
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

        <a href="login.php" class="logout">
            🚪 Logout
        </a>

    </nav>

</aside>



<!-- MAIN -->

<main class="main">


<h1>
    Order Management
</h1>


<p class="subtitle">
    Manage customer orders and order status.
</p>



<!-- ACTION BAR -->

<div class="action-bar">


    <label class="select-all">

        <input
            type="checkbox"
            id="selectAll"
        >

        Select All

    </label>


    <form
        method="POST"
        onsubmit="
            return confirm(
                'Delete ALL orders permanently?'
            );
        "
    >

        <button
            type="submit"
            name="clear_all_orders"
            class="clear-button"
        >

            🗑️ Clear All Orders

        </button>

    </form>


</div>



<!-- STATS -->

<div class="stats">


    <div class="card">

        <div class="card-title">
            Total Orders
        </div>

        <div class="card-number">
            <?php echo $totalOrders; ?>
        </div>

    </div>


    <div class="card">

        <div class="card-title">
            Pending
        </div>

        <div class="card-number">
            <?php echo $pendingOrders; ?>
        </div>

    </div>


    <div class="card">

        <div class="card-title">
            Confirmed
        </div>

        <div class="card-number">
            <?php echo $confirmedOrders; ?>
        </div>

    </div>


    <div class="card">

        <div class="card-title">
            Delivered
        </div>

        <div class="card-number">
            <?php echo $deliveredOrders; ?>
        </div>

    </div>


    <div class="card">

        <div class="card-title">
            Cancelled
        </div>

        <div class="card-number">
            <?php echo $cancelledOrders; ?>
        </div>

    </div>


</div>



<!-- SEARCH -->

<div class="filters">

    <form method="GET">


        <input
            type="text"
            name="search"
            placeholder="Search order/customer/phone"
            value="<?php
                echo htmlspecialchars($search);
            ?>"
        >


        <select name="status">

            <option value="">
                All Status
            </option>

            <option
                value="Pending"
                <?php
                if ($statusFilter === "Pending")
                    echo "selected";
                ?>
            >
                Pending
            </option>

            <option
                value="Confirmed"
                <?php
                if ($statusFilter === "Confirmed")
                    echo "selected";
                ?>
            >
                Confirmed
            </option>

            <option
                value="Delivered"
                <?php
                if ($statusFilter === "Delivered")
                    echo "selected";
                ?>
            >
                Delivered
            </option>

            <option
                value="Cancelled"
                <?php
                if ($statusFilter === "Cancelled")
                    echo "selected";
                ?>
            >
                Cancelled
            </option>

        </select>


        <button
            type="submit"
            class="search-button"
        >
            🔍 Search
        </button>


        <a
            href="orders.php"
            class="reset-button"
        >
            Reset
        </a>


    </form>

</div>



<!--
IMPORTANT:
The delete form is ONLY around the table.
Status update forms are separate.
No nested forms.
-->

<form
    method="POST"
    id="deleteOrdersForm"
    onsubmit="
        return confirm(
            'Delete the selected orders permanently?'
        );
    "
>


<div class="table-box">


<table>


<thead>

<tr>

    <th>
        Select
    </th>

    <th>
        Order ID
    </th>

    <th>
        Customer
    </th>

    <th>
        Phone
    </th>

    <th>
        Items
    </th>

    <th>
        Amount
    </th>

    <th>
        Payment
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
    $result->fetchArray(
        SQLITE3_ASSOC
    )
):

$hasOrders = true;

?>


<tr>


<!-- SELECT -->

<td>

    <input
        type="checkbox"
        name="selected_orders[]"
        value="<?php
            echo (int)$order["id"];
        ?>"
        class="order-checkbox"
    >

</td>


<!-- ORDER -->

<td>

<strong>

<?php

echo htmlspecialchars(
    $order["order_number"]
);

?>

</strong>

</td>


<!-- CUSTOMER -->

<td>

<strong>

<?php

echo htmlspecialchars(
    $order["customer"]
);

?>

</strong>

<br>

<small>

<?php

echo htmlspecialchars(
    $order["email"] ?? ""
);

?>

</small>

</td>


<!-- PHONE -->

<td>

<?php

echo htmlspecialchars(
    $order["phone"] ?? ""
);

?>

</td>


<!-- ITEMS -->

<td>

<?php

$items =
    json_decode(
        $order["items"] ?? "",
        true
    );


if (is_array($items)) {

    foreach ($items as $item) {

        echo htmlspecialchars(
            $item["name"] ?? "Product"
        );

        echo " × ";

        echo (int)(
            $item["quantity"] ?? 1
        );

        echo "<br>";
    }

} else {

    echo htmlspecialchars(
        $order["items"] ?? ""
    );

}

?>

</td>


<!-- AMOUNT -->

<td>

<strong>

₹<?php

echo number_format(
    (float)$order["amount"],
    2
);

?>

</strong>

</td>


<!-- PAYMENT -->

<td>

<?php

echo htmlspecialchars(
    $order["payment"] ?? ""
);

?>

</td>


<!-- STATUS -->

<td>

<form
    method="POST"
    class="status-form"
>


<input
    type="hidden"
    name="order_id"
    value="<?php
        echo (int)$order["id"];
    ?>"
>


<select name="status">

<option
    value="Pending"
    <?php
    if ($order["status"] === "Pending")
        echo "selected";
    ?>
>
Pending
</option>


<option
    value="Confirmed"
    <?php
    if ($order["status"] === "Confirmed")
        echo "selected";
    ?>
>
Confirmed
</option>


<option
    value="Delivered"
    <?php
    if ($order["status"] === "Delivered")
        echo "selected";
    ?>
>
Delivered
</option>


<option
    value="Cancelled"
    <?php
    if ($order["status"] === "Cancelled")
        echo "selected";
    ?>
>
Cancelled
</option>

</select>


<button
    type="submit"
    name="update_status"
>
Save
</button>


</form>

</td>


<!-- DATE -->

<td>

<?php

echo htmlspecialchars(
    $order["date"] ?? ""
);

?>

</td>


</tr>


<?php

endwhile;

?>


<?php if (!$hasOrders): ?>

<tr>

<td
    colspan="9"
    class="no-orders"
>

No orders found.

</td>

</tr>

<?php endif; ?>


</tbody>

</table>


</div>


<?php if ($hasOrders): ?>

<button
    type="submit"
    name="delete_selected_orders"
    class="delete-selected"
>

🗑️ Delete Selected

</button>

<?php endif; ?>


</form>


</main>



<script>


/* SELECT ALL */

const selectAll =
    document.getElementById(
        "selectAll"
    );


const checkboxes =
    document.querySelectorAll(
        ".order-checkbox"
    );


selectAll.addEventListener(
    "change",
    function() {

        checkboxes.forEach(
            function(checkbox) {

                checkbox.checked =
                    selectAll.checked;

            }
        );

    }
);


/* INDIVIDUAL CHECKBOX */

checkboxes.forEach(
    function(checkbox) {

        checkbox.addEventListener(
            "change",
            function() {

                const allChecked =
                    Array.from(
                        checkboxes
                    ).every(
                        function(item) {

                            return item.checked;

                        }
                    );


                selectAll.checked =
                    allChecked;

            }
        );

    }
);

</script>


</body>

</html>