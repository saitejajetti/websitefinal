<?php

session_start();

require_once "../database/database.php";


/* =========================================================
   GET REGISTERED USERS FROM SQLITE DATABASE
   ========================================================= */

$users = [];

$result = $db->query("
    SELECT
        id,
        name,
        email,
        created_at
    FROM users
    ORDER BY id DESC
");

while ($row = $result->fetchArray(SQLITE3_ASSOC)) {

    $userId = (int)$row['id'];

    /* Count orders made by this customer */
    $orderStmt = $db->prepare("
        SELECT COUNT(*)
        FROM orders
        WHERE email = :email
    ");

    $orderStmt->bindValue(
        ":email",
        $row['email'],
        SQLITE3_TEXT
    );

    $orderResult = $orderStmt->execute();
    $orderRow = $orderResult->fetchArray(SQLITE3_NUM);

    $orderCount = (int)$orderRow[0];


    $users[] = [

        'id' => $userId,

        'name' => $row['name'],

        'email' => $row['email'],

        'phone' => 'Not provided',

        'orders' => $orderCount,

        'status' => 'Active'

    ];
}


/* =========================================================
   USER STATISTICS
   ========================================================= */

$totalUsers = count($users);

$activeUsers = 0;
$blockedUsers = 0;

foreach ($users as $user) {

    if ($user['status'] === 'Active') {
        $activeUsers++;
    }

    if ($user['status'] === 'Blocked') {
        $blockedUsers++;
    }
}


/* =========================================================
   SEARCH
   ========================================================= */

$search = '';

if (isset($_GET['search'])) {

    $search = trim($_GET['search']);

}


$filteredUsers = [];

foreach ($users as $user) {

    if ($search === '') {

        $filteredUsers[] = $user;

    } else {

        $searchText = strtolower($search);

        if (
            strpos(
                strtolower($user['name']),
                $searchText
            ) !== false

            ||

            strpos(
                strtolower($user['email']),
                $searchText
            ) !== false
        ) {

            $filteredUsers[] = $user;

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

<title>Users - Saiteja grocery </title>

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    background: #f5f5f5;
    color: #111;
}

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

    font-size: 23px;
    font-weight: bold;
}

.admin-title {
    text-align: center;

    color: #aaa;

    font-size: 13px;

    margin: 7px 0 30px;
}

.menu a {
    display: block;

    color: #ddd;

    text-decoration: none;

    padding: 13px 15px;

    margin-bottom: 5px;

    border-radius: 6px;
}

.menu a:hover,
.menu a.active {
    background: white;
    color: #111;
}

.logout {
    margin-top: 25px;

    background: #c62828;

    color: white !important;
}

.main {
    margin-left: 250px;

    padding: 30px;
}

h1 {
    font-size: 30px;
}

.subtitle {
    color: #777;

    margin: 7px 0 30px;
}


/* =========================================================
   STATS
   ========================================================= */

.stats {
    display: grid;

    grid-template-columns: repeat(3, 1fr);

    gap: 20px;

    margin-bottom: 30px;
}

.card {
    background: white;

    padding: 25px;

    border-radius: 10px;

    border: 1px solid #ddd;
}

.card-title {
    color: #777;

    margin-bottom: 10px;
}

.card-number {
    font-size: 28px;

    font-weight: bold;
}


/* =========================================================
   SEARCH
   ========================================================= */

.search-box {
    background: white;

    padding: 20px;

    border-radius: 10px;

    border: 1px solid #ddd;

    margin-bottom: 25px;
}

.search-form {
    display: flex;

    gap: 10px;
}

.search-input {
    flex: 1;

    padding: 13px;

    border: 1px solid #ccc;

    border-radius: 6px;
}

.search-button {
    padding: 13px 22px;

    background: #111;

    color: white;

    border: none;

    border-radius: 6px;

    cursor: pointer;
}

.clear {
    padding: 13px 20px;

    background: #eee;

    color: #111;

    text-decoration: none;

    border-radius: 6px;
}


/* =========================================================
   TABLE
   ========================================================= */

.table-box {
    background: white;

    border: 1px solid #ddd;

    border-radius: 10px;

    overflow-x: auto;
}

table {
    width: 100%;

    border-collapse: collapse;

    min-width: 750px;
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

.status {
    padding: 6px 10px;

    border-radius: 20px;

    font-size: 12px;

    font-weight: bold;
}

.active-status {
    background: #d9f7df;

    color: #176b2c;
}

.blocked-status {
    background: #ffdede;

    color: #a00000;
}

.view-btn {
    background: #111;

    color: white;

    padding: 7px 12px;

    border-radius: 5px;

    border: none;

    cursor: pointer;
}


/* =========================================================
   MOBILE
   ========================================================= */

@media(max-width:900px) {

    .stats {
        grid-template-columns: 1fr;
    }

}

@media(max-width:700px) {

    .sidebar {
        position: relative;

        width: 100%;

        height: auto;
    }

    .main {
        margin-left: 0;

        padding: 20px;
    }

    .search-form {
        flex-direction: column;
    }

}

</style>

</head>

<body>


<!-- =====================================================
     SIDEBAR
     ===================================================== -->

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

        <a href="orders.php">
            🛒 Orders
        </a>

        <a href="payments.php">
            💳 Payments
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

        <a href="users.php" class="active">
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


<!-- =====================================================
     MAIN
     ===================================================== -->

<main class="main">

    <h1>
        Users
    </h1>

    <p class="subtitle">
        Manage registered Saiteja grocery  customers.
    </p>


    <!-- =================================================
         STATISTICS
         ================================================= -->

    <div class="stats">

        <div class="card">

            <div class="card-title">
                Total Users
            </div>

            <div class="card-number">
                <?php echo $totalUsers; ?>
            </div>

        </div>


        <div class="card">

            <div class="card-title">
                Active Users
            </div>

            <div class="card-number">
                <?php echo $activeUsers; ?>
            </div>

        </div>


        <div class="card">

            <div class="card-title">
                Blocked Users
            </div>

            <div class="card-number">
                <?php echo $blockedUsers; ?>
            </div>

        </div>

    </div>


    <!-- =================================================
         SEARCH
         ================================================= -->

    <div class="search-box">

        <form
            method="GET"
            class="search-form"
        >

            <input
                type="text"
                name="search"
                class="search-input"
                placeholder="Search name, email or phone..."
                value="<?php echo htmlspecialchars($search); ?>"
            >

            <button
                type="submit"
                class="search-button"
            >
                Search
            </button>

            <a
                href="users.php"
                class="clear"
            >
                Clear
            </a>

        </form>

    </div>


    <!-- =================================================
         USERS TABLE
         ================================================= -->

    <div class="table-box">

        <table>

            <thead>

                <tr>

                    <th>
                        ID
                    </th>

                    <th>
                        Name
                    </th>

                    <th>
                        Email
                    </th>

                    <th>
                        Phone
                    </th>

                    <th>
                        Orders
                    </th>

                    <th>
                        Status
                    </th>

                    <th>
                        Action
                    </th>

                </tr>

            </thead>


            <tbody>

            <?php if (count($filteredUsers) > 0): ?>

                <?php foreach ($filteredUsers as $user): ?>

                <tr>

                    <td>
                        <?php echo $user['id']; ?>
                    </td>

                    <td>

                        <strong>

                            <?php

                            echo htmlspecialchars(
                                $user['name']
                            );

                            ?>

                        </strong>

                    </td>

                    <td>

                        <?php

                        echo htmlspecialchars(
                            $user['email']
                        );

                        ?>

                    </td>

                    <td>

                        <?php

                        echo htmlspecialchars(
                            $user['phone']
                        );

                        ?>

                    </td>

                    <td>

                        <?php echo $user['orders']; ?>

                    </td>

                    <td>

                        <?php if ($user['status'] === 'Active'): ?>

                            <span class="status active-status">
                                Active
                            </span>

                        <?php else: ?>

                            <span class="status blocked-status">
                                Blocked
                            </span>

                        <?php endif; ?>

                    </td>

                    <td>

                        <button
                            class="view-btn"
                            onclick="viewUser(
                                '<?php echo htmlspecialchars($user['name'], ENT_QUOTES); ?>',
                                '<?php echo htmlspecialchars($user['email'], ENT_QUOTES); ?>',
                                '<?php echo htmlspecialchars($user['phone'], ENT_QUOTES); ?>',
                                '<?php echo $user['orders']; ?>'
                            )"
                        >
                            View
                        </button>

                    </td>

                </tr>

                <?php endforeach; ?>

            <?php else: ?>

                <tr>

                    <td
                        colspan="7"
                        style="text-align:center;"
                    >
                        No registered users found.
                    </td>

                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</main>


<!-- =====================================================
     VIEW USER JAVASCRIPT
     ===================================================== -->

<script>

function viewUser(
    name,
    email,
    phone,
    orders
) {

    alert(

        "USER DETAILS\n\n" +

        "Name: " + name + "\n" +

        "Email: " + email + "\n" +

        "Phone: " + phone + "\n" +

        "Orders: " + orders

    );

}

</script>


</body>

</html>