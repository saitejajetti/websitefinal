<?php

require_once "database/database.php";

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($name === "" || $email === "" || $password === "") {

        $message = "Please fill all fields.";
        $message_type = "error";

    } else {

        // Check whether email already exists
        $check = $db->prepare("SELECT id FROM users WHERE email = :email");
        $check->bindValue(":email", $email, SQLITE3_TEXT);

        $result = $check->execute();
        $existing_user = $result->fetchArray(SQLITE3_ASSOC);

        if ($existing_user) {

            $message = "This email is already registered.";
            $message_type = "error";

        } else {

            // Securely hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user
            $stmt = $db->prepare("
                INSERT INTO users (name, email, password)
                VALUES (:name, :email, :password)
            ");

            $stmt->bindValue(":name", $name, SQLITE3_TEXT);
            $stmt->bindValue(":email", $email, SQLITE3_TEXT);
            $stmt->bindValue(":password", $hashed_password, SQLITE3_TEXT);

            if ($stmt->execute()) {

                $message = "Registration successful! You can now login.";
                $message_type = "success";

            } else {

                $message = "Registration failed. Please try again.";
                $message_type = "error";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Register - Saiteja grocery </title>

    <link rel="stylesheet" href="CSS/style.css">

</head>

<body>

<header class="header">

    <div class="logo">
        🛒 Saiteja grocery 
    </div>

    <nav>
        <a href="index.php">Home</a>
        <a href="products.php">Products</a>
        <a href="login.php">Login</a>
    </nav>

</header>


<main class="container">

    <div class="form-box">

        <h1>Create Account</h1>

        <?php if ($message): ?>

            <p class="<?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </p>

        <?php endif; ?>


        <form method="POST">

            <label>Name</label>

            <input
                type="text"
                name="name"
                placeholder="Enter your name"
                required
            >


            <label>Email</label>

            <input
                type="email"
                name="email"
                placeholder="Enter your email"
                required
            >


            <label>Password</label>

            <input
                type="password"
                name="password"
                placeholder="Create a password"
                required
            >


            <button type="submit" class="button">
                Register
            </button>

        </form>


        <p>
            Already have an account?
            <a href="login.php">Login</a>
        </p>

    </div>

</main>

</body>

</html>