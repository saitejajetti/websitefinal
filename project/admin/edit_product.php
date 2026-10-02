<?php

require_once "../database/database.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    die("Invalid product ID.");
}

/*
 * Get product from SQLite
 */
$stmt = $db->prepare("
    SELECT * FROM products
    WHERE id = :id
");

$stmt->bindValue(':id', $id, SQLITE3_INTEGER);

$result = $stmt->execute();

$product = $result->fetchArray(SQLITE3_ASSOC);

if (!$product) {
    die("Product not found.");
}


/*
 * Update product
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name']);
    $category = trim($_POST['category']);

    $purchase_price = floatval($_POST['purchase_price']);
    $selling_price = floatval($_POST['selling_price']);

    $discount = floatval($_POST['discount']);
    $quantity = intval($_POST['quantity']);

    $description = trim($_POST['description']);

    /*
     * Keep old image
     */
    $imagePath = $product['image'];

    $imageFolder = "../Images/products/";

    if (!is_dir($imageFolder)) {
        mkdir($imageFolder, 0777, true);
    }


    /*
     * Upload new image if selected
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

                /*
                 * Delete old image
                 */
                if (!empty($product['image'])) {

                    $oldImage = "../" . ltrim(
                        $product['image'],
                        "/"
                    );

                    if (file_exists($oldImage)) {
                        unlink($oldImage);
                    }
                }

                $imagePath =
                    'Images/products/' . $fileName;
            }
        }
    }


    /*
     * Update SQLite database
     */
    $update = $db->prepare("
        UPDATE products
        SET
            name = :name,
            category = :category,
            purchase_price = :purchase_price,
            selling_price = :selling_price,
            discount = :discount,
            quantity = :quantity,
            description = :description,
            image = :image
        WHERE id = :id
    ");

    $update->bindValue(
        ':name',
        $name,
        SQLITE3_TEXT
    );

    $update->bindValue(
        ':category',
        $category,
        SQLITE3_TEXT
    );

    $update->bindValue(
        ':purchase_price',
        $purchase_price,
        SQLITE3_FLOAT
    );

    $update->bindValue(
        ':selling_price',
        $selling_price,
        SQLITE3_FLOAT
    );

    $update->bindValue(
        ':discount',
        $discount,
        SQLITE3_FLOAT
    );

    $update->bindValue(
        ':quantity',
        $quantity,
        SQLITE3_INTEGER
    );

    $update->bindValue(
        ':description',
        $description,
        SQLITE3_TEXT
    );

    $update->bindValue(
        ':image',
        $imagePath,
        SQLITE3_TEXT
    );

    $update->bindValue(
        ':id',
        $id,
        SQLITE3_INTEGER
    );

    $update->execute();


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

<title>Edit Product - Saiteja grocery </title>

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

    box-shadow:
        0 8px 25px rgba(0,0,0,0.08);
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

.current-image {

    width: 120px;

    height: 120px;

    object-fit: cover;

    display: block;

    margin-bottom: 20px;

    border-radius: 8px;

    border: 1px solid #ddd;
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

    text-align: center;

    margin-top: 20px;

    color: #111;

    text-decoration: none;
}

</style>

</head>

<body>

<div class="container">

<h1>Edit Product</h1>

<form
    method="POST"
    enctype="multipart/form-data"
>

    <label>Product Name</label>

    <input
        type="text"
        name="name"
        value="<?php echo htmlspecialchars($product['name']); ?>"
        required
    >


    <label>Category</label>

    <select name="category" required>

        <?php

        $categories = [
            'Vegetables',
            'Fruits',
            'Grains',
            'Dairy',
            'Beverages',
            'Snacks'
        ];

        foreach ($categories as $category) {

        ?>

            <option
                value="<?php echo htmlspecialchars($category); ?>"

                <?php

                if ($product['category'] === $category) {
                    echo 'selected';
                }

                ?>
            >

                <?php echo htmlspecialchars($category); ?>

            </option>

        <?php } ?>

    </select>


    <label>Purchase Price</label>

    <input
        type="number"
        name="purchase_price"
        step="0.01"
        min="0"
        value="<?php echo $product['purchase_price']; ?>"
        required
    >


    <label>Selling Price</label>

    <input
        type="number"
        name="selling_price"
        step="0.01"
        min="0"
        value="<?php echo $product['selling_price']; ?>"
        required
    >


    <label>Discount (%)</label>

    <input
        type="number"
        name="discount"
        step="0.01"
        min="0"
        max="100"
        value="<?php echo $product['discount']; ?>"
    >


    <label>Quantity</label>

    <input
        type="number"
        name="quantity"
        min="0"
        value="<?php echo $product['quantity']; ?>"
        required
    >


    <label>Description</label>

    <textarea
        name="description"
    ><?php echo htmlspecialchars($product['description']); ?></textarea>


    <?php if (!empty($product['image'])) { ?>

        <label>Current Image</label>

        <img
            src="../<?php echo htmlspecialchars(
                ltrim($product['image'], '/')
            ); ?>"
            class="current-image"
            alt="Current Product Image"
        >

    <?php } ?>


    <label>Change Image</label>

    <input
        type="file"
        name="image"
        accept="image/jpeg,image/png,image/webp"
    >


    <button type="submit">
        Save Changes
    </button>

</form>


<a
    href="products.php"
    class="back"
>
    ← Back to Products
</a>

</div>

</body>

</html>