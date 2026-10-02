<?php

require_once "../database/database.php";

$result = $db->query("SELECT id, name, email, created_at FROM users");

?>

<!DOCTYPE html>
<html>
<head>
    <title>Database Users - Saiteja grocery </title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 30px;
        }

        h1 {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            background: white;
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
            border-bottom: 1px solid #ddd;
        }

        a {
            display: inline-block;
            margin-bottom: 20px;
            color: #111;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>

<body>

<a href="index.php">← Back to Admin Panel</a>

<h1>Customer Database</h1>

<table>

<tr>
    <th>ID</th>
    <th>Name</th>
    <th>Email</th>
    <th>Created At</th>
</tr>

<?php while ($user = $result->fetchArray(SQLITE3_ASSOC)): ?>

<tr>
    <td><?php echo htmlspecialchars($user['id']); ?></td>

    <td><?php echo htmlspecialchars($user['name']); ?></td>

    <td><?php echo htmlspecialchars($user['email']); ?></td>

    <td><?php echo htmlspecialchars($user['created_at']); ?></td>
</tr>

<?php endwhile; ?>

</table>

</body>
</html>