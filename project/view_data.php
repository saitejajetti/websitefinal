<?php

require_once "database/database.php";

echo "<h1>Saiteja grocery  - Database Data</h1>";

echo "<h2>Products</h2>";

$result = $db->query("SELECT * FROM products");

echo "<table border='1' cellpadding='10'>";
echo "<tr>
        <th>ID</th>
        <th>Name</th>
        <th>Category</th>
        <th>Purchase Price</th>
        <th>Selling Price</th>
        <th>Discount</th>
        <th>Quantity</th>
      </tr>";

while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
    echo "<td>" . htmlspecialchars($row['category']) . "</td>";
    echo "<td>" . htmlspecialchars($row['purchase_price']) . "</td>";
    echo "<td>" . htmlspecialchars($row['selling_price']) . "</td>";
    echo "<td>" . htmlspecialchars($row['discount']) . "</td>";
    echo "<td>" . htmlspecialchars($row['quantity']) . "</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h2>Users</h2>";

$result = $db->query("SELECT id, name, email, created_at FROM users");

echo "<table border='1' cellpadding='10'>";
echo "<tr>
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Created At</th>
      </tr>";

while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    echo "<tr>";
    echo "<td>{$row['id']}</td>";
    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
    echo "<td>{$row['created_at']}</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h2>Orders</h2>";

$result = $db->query("SELECT * FROM orders");

echo "<table border='1' cellpadding='10'>";
echo "<tr>
        <th>ID</th>
        <th>Order Number</th>
        <th>Customer</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Amount</th>
        <th>Payment</th>
        <th>Status</th>
      </tr>";

while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    echo "<tr>";
    echo "<td>{$row['id']}</td>";
    echo "<td>" . htmlspecialchars($row['order_number']) . "</td>";
    echo "<td>" . htmlspecialchars($row['customer']) . "</td>";
    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
    echo "<td>" . htmlspecialchars($row['phone']) . "</td>";
    echo "<td>{$row['amount']}</td>";
    echo "<td>" . htmlspecialchars($row['payment']) . "</td>";
    echo "<td>" . htmlspecialchars($row['status']) . "</td>";
    echo "</tr>";
}

echo "</table>";

?>