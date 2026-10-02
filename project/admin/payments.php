<?php

require_once "../database/database.php";

/* Get all payment/order records */
$result = $db->query("
    SELECT
        id,
        order_number,
        customer,
        email,
        phone,
        amount,
        payment,
        status,
        date
    FROM orders
    ORDER BY id DESC
");

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Payments - Saiteja grocery </title>

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

.header {
    background: #111;
    color: white;
    padding: 20px 30px;
}

.header h1 {
    margin: 0;
}

.container {
    width: 95%;
    max-width: 1400px;
    margin: 30px auto;
}

.card {
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

.back {
    display: inline-block;
    margin-bottom: 20px;
    text-decoration: none;
    color: #111;
    font-weight: bold;
}

.stats {
    display: flex;
    gap: 20px;
    margin-bottom: 25px;
    flex-wrap: wrap;
}

.stat {
    background: #111;
    color: white;
    padding: 20px;
    border-radius: 8px;
    min-width: 180px;
}

.stat h3 {
    margin: 0 0 8px;
}

.stat p {
    margin: 0;
    font-size: 22px;
    font-weight: bold;
}

.table-container {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    min-width: 900px;
}

th,
td {
    padding: 14px;
    border-bottom: 1px solid #ddd;
    text-align: left;
}

th {
    background: #111;
    color: white;
}

tr:hover {
    background: #f8f8f8;
}

.payment {
    font-weight: bold;
}

.upi {
    color: #16803c;
}

.cod {
    color: #555;
}

.status {
    padding: 6px 10px;
    border-radius: 5px;
    background: #eee;
    display: inline-block;
}

.empty {
    padding: 30px;
    text-align: center;
    color: #777;
}

</style>

</head>


<body>


<div class="header">

    <h1>💳 Payment Management</h1>

</div>


<div class="container">


<a href="index.php" class="back">
    ← Back to Admin Dashboard
</a>


<div class="stats">

<?php

$totalPayments = $db->querySingle("
    SELECT COUNT(*)
    FROM orders
");

$totalAmount = $db->querySingle("
    SELECT COALESCE(SUM(amount), 0)
    FROM orders
    WHERE status != 'Cancelled'
");

$upiPayments = $db->querySingle("
    SELECT COUNT(*)
    FROM orders
    WHERE payment LIKE 'UPI%'
");

$codPayments = $db->querySingle("
    SELECT COUNT(*)
    FROM orders
    WHERE payment = 'Cash on Delivery'
");

?>

<div class="stat">

    <h3>Total Payments</h3>

    <p>
        <?php echo $totalPayments; ?>
    </p>

</div>


<div class="stat">

    <h3>Total Amount</h3>

    <p>
        ₹<?php echo number_format($totalAmount, 2); ?>
    </p>

</div>


<div class="stat">

    <h3>UPI Payments</h3>

    <p>
        <?php echo $upiPayments; ?>
    </p>

</div>


<div class="stat">

    <h3>COD Orders</h3>

    <p>
        <?php echo $codPayments; ?>
    </p>

</div>

</div>


<div class="card">

<h2>Payment Records</h2>


<div class="table-container">

<table>

<thead>

<tr>

<th>Order</th>

<th>Customer</th>

<th>Phone</th>

<th>Amount</th>

<th>Payment Method</th>

<th>Status</th>

<th>Date</th>

</tr>

</thead>


<tbody>

<?php

$hasPayments = false;

while ($row = $result->fetchArray(SQLITE3_ASSOC)) {

    $hasPayments = true;

?>

<tr>

<td>
    <strong>
        <?php echo htmlspecialchars($row["order_number"]); ?>
    </strong>
</td>


<td>

    <?php echo htmlspecialchars($row["customer"]); ?>

    <?php if (!empty($row["email"])) { ?>

        <br>

        <small>
            <?php echo htmlspecialchars($row["email"]); ?>
        </small>

    <?php } ?>

</td>


<td>
    <?php echo htmlspecialchars($row["phone"]); ?>
</td>


<td>

    <strong>
        ₹<?php echo number_format($row["amount"], 2); ?>
    </strong>

</td>


<td class="payment">

<?php

$payment = $row["payment"] ?? "";

if (strpos($payment, "UPI") === 0) {

?>

    <span class="upi">
        <?php echo htmlspecialchars($payment); ?>
    </span>

<?php

} else {

?>

    <span class="cod">
        <?php echo htmlspecialchars($payment); ?>
    </span>

<?php

}

?>

</td>


<td>

<span class="status">

<?php echo htmlspecialchars($row["status"]); ?>

</span>

</td>


<td>

<?php echo htmlspecialchars($row["date"]); ?>

</td>

</tr>

<?php

}

?>


<?php if (!$hasPayments) { ?>

<tr>

<td colspan="7">

<div class="empty">

<h3>No payment records found</h3>

<p>
Payments will appear here after customers place orders.
</p>

</div>

</td>

</tr>

<?php } ?>


</tbody>

</table>

</div>

</div>


</div>


</body>

</html>