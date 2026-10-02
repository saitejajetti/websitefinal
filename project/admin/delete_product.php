<?php

require_once "../database/database.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: products.php");
    exit;
}

/*
 * First get the product image
 */
$stmt = $db->prepare("
    SELECT image
    FROM products
    WHERE id = :id
");

$stmt->bindValue(
    ':id',
    $id,
    SQLITE3_INTEGER
);

$result = $stmt->execute();

$product = $result->fetchArray(SQLITE3_ASSOC);


/*
 * Delete image file
 */
if ($product && !empty($product['image'])) {

    $imageFile = "../" . ltrim(
        $product['image'],
        "/"
    );

    if (file_exists($imageFile)) {
        unlink($imageFile);
    }
}


/*
 * Delete product from SQLite
 */
$delete = $db->prepare("
    DELETE FROM products
    WHERE id = :id
");

$delete->bindValue(
    ':id',
    $id,
    SQLITE3_INTEGER
);

$delete->execute();


/*
 * Go back to products
 */
header("Location: products.php");

exit;

?>