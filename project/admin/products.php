<?php

require_once "../database/database.php";

$result = $db->query("SELECT * FROM products ORDER BY id DESC");

$products = [];

while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $products[] = $row;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Products - Saiteja grocery </title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f5f5f5;
            color: #111;
        }

        .header {
            background: #000;
            color: white;
            padding: 18px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 22px;
            font-weight: bold;
        }

        .header a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
        }

        .container {
            padding: 30px;
        }

        .top-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        h1 {
            font-size: 30px;
        }

        .add-button {
            background: #000;
            color: white;
            padding: 12px 18px;
            text-decoration: none;
            border-radius: 6px;
        }

        .table-container {
            background: white;
            border-radius: 10px;
            overflow-x: auto;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #000;
            color: white;
        }

        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #ddd;
        }

        .no-image {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #eee;
            border-radius: 6px;
            font-size: 25px;
        }

        .edit {
            background: #000;
            color: white;
            padding: 7px 12px;
            text-decoration: none;
            border-radius: 5px;
            margin-right: 5px;
        }

        .delete {
            background: #ddd;
            color: #000;
            padding: 7px 12px;
            text-decoration: none;
            border-radius: 5px;
        }

        .empty {
            padding: 40px;
            text-align: center;
            font-size: 18px;
        }

    </style>

</head>

<body>

<header class="header">

    <div class="logo">
        🛒 Saiteja grocery  - Admin
    </div>

    <div>
        <a href="../index.php">View Website</a>
        <a href="index.php">Dashboard</a>
    </div>

</header>


<main class="container">

    <div class="top-section">

        <h1>Products</h1>

        <a href="add_product.php" class="add-button">
            + Add Product
        </a>

    </div>


    <div class="table-container">

        <?php if (count($products) > 0) { ?>

            <table>

                <thead>

                    <tr>

                        <th>Image</th>

                        <th>ID</th>

                        <th>Product</th>

                        <th>Category</th>

                        <th>Selling Price</th>

                        <th>Quantity</th>

                        <th>Actions</th>

                    </tr>

                </thead>


                <tbody>

                    <?php foreach ($products as $product) { ?>

                        <tr>

                            <td>

                                <?php if (!empty($product['image'])) { ?>

                                    <img
                                        src="../<?php echo htmlspecialchars(ltrim($product['image'], '/')); ?>"
                                        alt="<?php echo htmlspecialchars($product['name']); ?>"
                                        class="product-image"
                                    >

                                <?php } else { ?>

                                    <div class="no-image">
                                        🛒
                                    </div>

                                <?php } ?>

                            </td>


                            <td>
                                <?php echo $product['id']; ?>
                            </td>


                            <td>
                                <strong>
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </strong>
                            </td>


                            <td>
                                <?php echo htmlspecialchars($product['category']); ?>
                            </td>


                            <td>
                                ₹<?php echo number_format($product['selling_price'], 2); ?>
                            </td>


                            <td>
                                <?php echo $product['quantity']; ?>
                            </td>


                            <td>

                                <a
                                    href="edit_product.php?id=<?php echo $product['id']; ?>"
                                    class="edit"
                                >
                                    Edit
                                </a>

                                <a
                                    href="delete_product.php?id=<?php echo $product['id']; ?>"
                                    class="delete"
                                    onclick="return confirm('Are you sure you want to delete this product?');"
                                >
                                    Delete
                                </a>

                            </td>

                        </tr>

                    <?php } ?>

                </tbody>

            </table>

        <?php } else { ?>

            <div class="empty">

                <p>No products available.</p>

                <br>

                <a href="add_product.php" class="add-button">
                    Add Your First Product
                </a>

            </div>

        <?php } ?>

    </div>

</main>

</body>

</html>