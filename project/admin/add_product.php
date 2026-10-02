<?php

require_once "../database/database.php";

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name']);
    $category = trim($_POST['category']);
    $purchase_price = floatval($_POST['purchase_price']);
    $selling_price = floatval($_POST['selling_price']);
    $discount = floatval($_POST['discount']);
    $quantity = intval($_POST['quantity']);
    $description = trim($_POST['description']);

    $imagePath = "";

    /*
     * Create product image folder
     */
    $imageFolder = "../Images/products/";

    if (!is_dir($imageFolder)) {
        mkdir($imageFolder, 0777, true);
    }

    /*
     * Upload image
     */
    if (
        isset($_FILES['image']) &&
        $_FILES['image']['error'] === UPLOAD_ERR_OK
    ) {

        $allowedTypes = [
            'image/jpeg',
            'image/png',
            'image/webp'
        ];

        if (in_array($_FILES['image']['type'], $allowedTypes)) {

            $extension = strtolower(
                pathinfo(
                    $_FILES['image']['name'],
                    PATHINFO_EXTENSION
                )
            );

            $fileName = uniqid('product_') . '.' . $extension;

            $target = $imageFolder . $fileName;

            if (
                move_uploaded_file(
                    $_FILES['image']['tmp_name'],
                    $target
                )
            ) {

                $imagePath = 'Images/products/' . $fileName;
            }
        }
    }

    /*
     * Insert product into SQLite
     */

    $stmt = $db->prepare("
        INSERT INTO products
        (
            name,
            category,
            purchase_price,
            selling_price,
            discount,
            quantity,
            description,
            image
        )
        VALUES
        (
            :name,
            :category,
            :purchase_price,
            :selling_price,
            :discount,
            :quantity,
            :description,
            :image
        )
    ");

    $stmt->bindValue(':name', $name, SQLITE3_TEXT);
    $stmt->bindValue(':category', $category, SQLITE3_TEXT);
    $stmt->bindValue(':purchase_price', $purchase_price, SQLITE3_FLOAT);
    $stmt->bindValue(':selling_price', $selling_price, SQLITE3_FLOAT);
    $stmt->bindValue(':discount', $discount, SQLITE3_FLOAT);
    $stmt->bindValue(':quantity', $quantity, SQLITE3_INTEGER);
    $stmt->bindValue(':description', $description, SQLITE3_TEXT);
    $stmt->bindValue(':image', $imagePath, SQLITE3_TEXT);

    $stmt->execute();

    header("Location: products.php");
    exit;
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

<title>Add Product - Saiteja grocery </title>

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

.container {
    max-width: 650px;
    margin: 40px auto;
    background: white;
    padding: 35px;
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
}

h1 {
    margin-bottom: 25px;
}

label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
}

input,
select,
textarea {

    width: 100%;

    padding: 13px;

    margin-bottom: 20px;

    border: 1px solid #ccc;

    border-radius: 6px;

    font-size: 15px;
}

textarea {
    resize: vertical;
    min-height: 100px;
}

button {

    width: 100%;

    padding: 14px;

    background: #111;

    color: white;

    border: none;

    border-radius: 6px;

    font-size: 16px;

    cursor: pointer;
}

button:hover {
    background: #333;
}

.back {

    display: block;

    margin-top: 20px;

    text-align: center;

    color: #111;

    text-decoration: none;
}

</style>

</head>

<body>

<div class="container">

<h1>Add Product</h1>

<form method="POST" enctype="multipart/form-data">

    <label>Product Name</label>

    <input
        type="text"
        name="name"
        placeholder="Enter product name"
        required
    >


    <label>Category</label>

    <select name="category" required>

        <option value="">Select Category</option>

        <option value="Vegetables">Vegetables</option>

        <option value="Fruits">Fruits</option>

        <option value="Grains">Grains</option>

        <option value="Dairy">Dairy</option>

        <option value="Beverages">Beverages</option>

        <option value="Snacks">Snacks</option>

    </select>


    <label>Purchase Price</label>

    <input
        type="number"
        name="purchase_price"
        step="0.01"
        min="0"
        placeholder="Your buying price"
        required
    >


    <label>Selling Price</label>

    <input
        type="number"
        name="selling_price"
        step="0.01"
        min="0"
        placeholder="Customer selling price"
        required
    >


    <label>Discount (%)</label>

    <input
        type="number"
        name="discount"
        step="0.01"
        min="0"
        max="100"
        value="0"
        placeholder="Enter discount"
    >


    <label>Quantity</label>

    <input
        type="number"
        name="quantity"
        min="0"
        placeholder="Enter quantity"
        required
    >


    <label>Description</label>

    <textarea
        name="description"
        placeholder="Enter product description"
    ></textarea>


    <label>Product Image</label>

    <input
        type="file"
        name="image"
        accept="image/jpeg,image/png,image/webp"
    >


    <button type="submit">
        Add Product
    </button>

</form>

<a href="products.php" class="back">
    ← Back to Products
</a>

</div>

</body>

</html>