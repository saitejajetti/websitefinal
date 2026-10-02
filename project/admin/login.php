<?php
session_start();

$admin_id = "admin";
$admin_password = "admin123";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $id = $_POST["admin_id"] ?? "";
    $password = $_POST["password"] ?? "";

    if ($id === $admin_id && $password === $admin_password) {

        $_SESSION["admin_logged_in"] = true;

        header("Location: index.php");
        exit;

    } else {

        $error = "Invalid Admin ID or Password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Login - Saiteja grocery </title>

    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<div class="container">

    <h1>Admin Login</h1>

    <?php if ($error != "") { ?>

        <p style="color:red;">
            <?php echo $error; ?>
        </p>

    <?php } ?>

    <form method="POST">

        <label>Admin ID</label>

        <input
            type="text"
            name="admin_id"
            required
        >

        <br><br>

        <label>Password</label>

        <input
            type="password"
            name="password"
            required
        >

        <br><br>

        <button type="submit" class="button">
            Login
        </button>

    </form>

</div>

</body>
</html>