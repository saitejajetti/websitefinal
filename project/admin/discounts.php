<?php

session_start();

require_once "../database/database.php";

/* =========================
   CREATE COUPONS TABLE
========================= */

$db->exec("
    CREATE TABLE IF NOT EXISTS coupons (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        code TEXT UNIQUE NOT NULL,
        discount_type TEXT NOT NULL,
        discount_value REAL NOT NULL,
        minimum_order REAL DEFAULT 0,
        maximum_discount REAL DEFAULT 0,
        expiry_date TEXT,
        status TEXT DEFAULT 'Active',
        created_at TEXT DEFAULT CURRENT_TIMESTAMP
    )
");


/* =========================
   ADD DISCOUNT
========================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["add_discount"])
) {

    $code = strtoupper(
        trim($_POST["code"] ?? "")
    );

    $type = trim(
        $_POST["type"] ?? "Percentage"
    );

    $value = (float)(
        $_POST["value"] ?? 0
    );

    $minimum = (float)(
        $_POST["minimum"] ?? 0
    );

    $maximum = (float)(
        $_POST["maximum"] ?? 0
    );

    $expiry = trim(
        $_POST["expiry"] ?? ""
    );


    if ($code === "") {

        $_SESSION["discount_error"] =
            "Please enter a discount code.";

    } elseif ($value <= 0) {

        $_SESSION["discount_error"] =
            "Discount value must be greater than 0.";

    } else {

        /* Check duplicate */

        $check = $db->prepare("
            SELECT id
            FROM coupons
            WHERE UPPER(code) = :code
            LIMIT 1
        ");

        $check->bindValue(
            ":code",
            $code,
            SQLITE3_TEXT
        );

        $result = $check->execute();

        $existing = $result->fetchArray(
            SQLITE3_ASSOC
        );


        if ($existing) {

            $_SESSION["discount_error"] =
                "This coupon code already exists.";

        } else {

            $stmt = $db->prepare("
                INSERT INTO coupons
                (
                    code,
                    discount_type,
                    discount_value,
                    minimum_order,
                    maximum_discount,
                    expiry_date,
                    status
                )
                VALUES
                (
                    :code,
                    :type,
                    :value,
                    :minimum,
                    :maximum,
                    :expiry,
                    'Active'
                )
            ");

            $stmt->bindValue(
                ":code",
                $code,
                SQLITE3_TEXT
            );

            $stmt->bindValue(
                ":type",
                $type,
                SQLITE3_TEXT
            );

            $stmt->bindValue(
                ":value",
                $value,
                SQLITE3_FLOAT
            );

            $stmt->bindValue(
                ":minimum",
                $minimum,
                SQLITE3_FLOAT
            );

            $stmt->bindValue(
                ":maximum",
                $maximum,
                SQLITE3_FLOAT
            );

            if ($expiry === "") {

                $stmt->bindValue(
                    ":expiry",
                    null,
                    SQLITE3_NULL
                );

            } else {

                $stmt->bindValue(
                    ":expiry",
                    $expiry,
                    SQLITE3_TEXT
                );

            }

            $stmt->execute();


            $_SESSION["discount_success"] =
                "Discount created successfully.";
        }
    }


    header("Location: discounts.php");

    exit;
}


/* =========================
   DELETE DISCOUNT
========================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["delete_discount"])
) {

    $id = (int)(
        $_POST["id"] ?? 0
    );

    if ($id > 0) {

        $stmt = $db->prepare("
            DELETE FROM coupons
            WHERE id = :id
        ");

        $stmt->bindValue(
            ":id",
            $id,
            SQLITE3_INTEGER
        );

        $stmt->execute();
    }

    header("Location: discounts.php");

    exit;
}


/* =========================
   ACTIVATE / DEACTIVATE
========================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["toggle_status"])
) {

    $id = (int)(
        $_POST["id"] ?? 0
    );

    $currentStatus = $_POST["current_status"] ?? "Active";

    $newStatus =
        ($currentStatus === "Active")
        ? "Inactive"
        : "Active";


    if ($id > 0) {

        $stmt = $db->prepare("
            UPDATE coupons
            SET status = :status
            WHERE id = :id
        ");

        $stmt->bindValue(
            ":status",
            $newStatus,
            SQLITE3_TEXT
        );

        $stmt->bindValue(
            ":id",
            $id,
            SQLITE3_INTEGER
        );

        $stmt->execute();
    }

    header("Location: discounts.php");

    exit;
}


/* =========================
   GET DISCOUNTS
========================= */

$discounts = $db->query("
    SELECT *
    FROM coupons
    ORDER BY id DESC
");


$success =
    $_SESSION["discount_success"]
    ?? "";

$error =
    $_SESSION["discount_error"]
    ?? "";


unset($_SESSION["discount_success"]);
unset($_SESSION["discount_error"]);

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
    Discounts - Saiteja grocery 
</title>

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
    margin: 7px 0 30px;
    font-size: 13px;
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

.form-box,
.table-box {
    background: white;
    padding: 25px;
    border-radius: 10px;
    border: 1px solid #ddd;
    margin-bottom: 30px;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 15px;
}

label {
    display: block;
    font-weight: bold;
    margin-bottom: 7px;
}

input,
select {
    width: 100%;
    padding: 12px;
    border: 1px solid #ccc;
    border-radius: 6px;
}

button {
    padding: 10px 14px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}

.add-button {
    width: 100%;
    margin-top: 25px;
    background: #111;
    color: white;
}

.add-button:hover {
    background: #333;
}

table {
    width: 100%;
    border-collapse: collapse;
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
}

.active-status {
    color: #176b2c;
    font-weight: bold;
}

.inactive-status {
    color: #c62828;
    font-weight: bold;
}

.action-form {
    display: inline;
}

.toggle {
    background: #eee;
    color: #111;
}

.delete {
    background: #c62828;
    color: white;
    margin-left: 5px;
}

.message-success {
    background: #e7f6eb;
    color: #176b2c;
    padding: 14px;
    border-radius: 7px;
    margin-bottom: 20px;
}

.message-error {
    background: #ffe5e5;
    color: #b00000;
    padding: 14px;
    border-radius: 7px;
    margin-bottom: 20px;
}

@media(max-width:1000px) {

    .form-grid {
        grid-template-columns: repeat(2, 1fr);
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
    }

    .form-grid {
        grid-template-columns: 1fr;
    }

    table {
        font-size: 13px;
    }

}

</style>

</head>

<body>

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

        <a href="discounts.php" class="active">
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


<main class="main">

    <h1>
        Discounts
    </h1>

    <p class="subtitle">
        Create and manage discount codes.
    </p>


    <?php if ($success !== ""): ?>

        <div class="message-success">
            ✅
            <?php echo htmlspecialchars($success); ?>
        </div>

    <?php endif; ?>


    <?php if ($error !== ""): ?>

        <div class="message-error">
            ❌
            <?php echo htmlspecialchars($error); ?>
        </div>

    <?php endif; ?>


    <!-- ADD DISCOUNT -->

    <div class="form-box">

        <h2>
            Add New Discount
        </h2>

        <br>

        <form method="POST">

            <div class="form-grid">

                <div>

                    <label>
                        Discount Code
                    </label>

                    <input
                        type="text"
                        name="code"
                        placeholder="Example: SAVE20"
                        required
                    >

                </div>


                <div>

                    <label>
                        Discount Type
                    </label>

                    <select name="type">

                        <option value="Percentage">
                            Percentage
                        </option>

                        <option value="Fixed">
                            Fixed Amount
                        </option>

                    </select>

                </div>


                <div>

                    <label>
                        Discount Value
                    </label>

                    <input
                        type="number"
                        name="value"
                        min="0"
                        step="0.01"
                        required
                    >

                </div>


                <div>

                    <label>
                        Minimum Order
                    </label>

                    <input
                        type="number"
                        name="minimum"
                        min="0"
                        step="0.01"
                        value="0"
                        required
                    >

                </div>


                <div>

                    <label>
                        Maximum Discount
                    </label>

                    <input
                        type="number"
                        name="maximum"
                        min="0"
                        step="0.01"
                        value="0"
                    >

                </div>


                <div>

                    <label>
                        Expiry Date
                    </label>

                    <input
                        type="date"
                        name="expiry"
                    >

                </div>

            </div>


            <button
                type="submit"
                name="add_discount"
                class="add-button"
            >
                ➕ Add Discount
            </button>

        </form>

    </div>


    <!-- DISCOUNT LIST -->

    <div class="table-box">

        <h2>
            Available Discounts
        </h2>

        <br>

        <table>

            <thead>

                <tr>

                    <th>
                        Code
                    </th>

                    <th>
                        Type
                    </th>

                    <th>
                        Value
                    </th>

                    <th>
                        Minimum
                    </th>

                    <th>
                        Maximum
                    </th>

                    <th>
                        Expiry
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

            <?php while (
                $discount =
                $discounts->fetchArray(
                    SQLITE3_ASSOC
                )
            ): ?>

                <tr>

                    <td>

                        <strong>

                            <?php

                            echo htmlspecialchars(
                                $discount["code"]
                            );

                            ?>

                        </strong>

                    </td>


                    <td>

                        <?php

                        echo htmlspecialchars(
                            $discount["discount_type"]
                        );

                        ?>

                    </td>


                    <td>

                        <?php if (
                            strtolower(
                                $discount["discount_type"]
                            )
                            ===
                            "percentage"
                        ): ?>

                            <?php
                            echo number_format(
                                $discount["discount_value"],
                                2
                            );
                            ?>%

                        <?php else: ?>

                            ₹<?php

                            echo number_format(
                                $discount["discount_value"],
                                2
                            );

                            ?>

                        <?php endif; ?>

                    </td>


                    <td>

                        ₹<?php

                        echo number_format(
                            $discount["minimum_order"],
                            2
                        );

                        ?>

                    </td>


                    <td>

                        <?php

                        if (
                            (float)$discount[
                                "maximum_discount"
                            ] > 0
                        ) {

                            echo "₹"
                                .
                                number_format(
                                    $discount[
                                        "maximum_discount"
                                    ],
                                    2
                                );

                        } else {

                            echo "No limit";

                        }

                        ?>

                    </td>


                    <td>

                        <?php

                        echo !empty(
                            $discount["expiry_date"]
                        )
                            ? htmlspecialchars(
                                $discount["expiry_date"]
                            )
                            : "No expiry";

                        ?>

                    </td>


                    <td>

                        <?php if (
                            $discount["status"]
                            ===
                            "Active"
                        ): ?>

                            <span class="active-status">
                                Active
                            </span>

                        <?php else: ?>

                            <span class="inactive-status">
                                Inactive
                            </span>

                        <?php endif; ?>

                    </td>


                    <td>

                        <!-- Toggle -->

                        <form
                            method="POST"
                            class="action-form"
                        >

                            <input
                                type="hidden"
                                name="id"
                                value="<?php
                                    echo $discount["id"];
                                ?>"
                            >

                            <input
                                type="hidden"
                                name="current_status"
                                value="<?php
                                    echo htmlspecialchars(
                                        $discount["status"]
                                    );
                                ?>"
                            >

                            <button
                                type="submit"
                                name="toggle_status"
                                class="toggle"
                            >

                                <?php

                                echo
                                $discount["status"]
                                === "Active"
                                    ? "Deactivate"
                                    : "Activate";

                                ?>

                            </button>

                        </form>


                        <!-- Delete -->

                        <form
                            method="POST"
                            class="action-form"
                            onsubmit="
                                return confirm(
                                    'Delete this discount?'
                                );
                            "
                        >

                            <input
                                type="hidden"
                                name="id"
                                value="<?php
                                    echo $discount["id"];
                                ?>"
                            >

                            <button
                                type="submit"
                                name="delete_discount"
                                class="delete"
                            >
                                Delete
                            </button>

                        </form>

                    </td>

                </tr>

            <?php endwhile; ?>

            </tbody>

        </table>

    </div>

</main>

</body>

</html>