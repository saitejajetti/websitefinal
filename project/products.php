<?php

require_once "database/database.php";

$result = $db->query("
    SELECT *
    FROM products
    ORDER BY id DESC
");

$page_title = "Products";

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
        <?php echo $page_title; ?> - Saiteja grocery 
    </title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }

        body {
            background: #f8f8f8;
            color: #111;
        }

        /* =========================
           HEADER
        ========================= */

        header {
            background: #fff;
            border-bottom: 1px solid #e5e5e5;
            padding: 22px 7%;
        }

        .header-top {
            margin-bottom: 18px;
        }

        .site-name {
            font-size: 34px;
            font-weight: 800;
            letter-spacing: 1px;
        }

        .header-bottom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 25px;
        }

        .logo {
            font-size: 20px;
            font-weight: 700;
        }

        nav {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        nav a {
            text-decoration: none;
            color: #111;
            font-size: 14px;
            font-weight: 600;
            padding: 10px 14px;
            border-radius: 7px;
            transition: 0.25s ease;
        }

        nav a:hover {
            background: #111;
            color: #fff;
        }

        /* =========================
           MAIN
        ========================= */

        .container {
            width: 86%;
            max-width: 1400px;
            margin: auto;
            padding: 55px 0;
        }

        .page-heading {
            text-align: center;
            margin-bottom: 45px;
        }

        .page-heading h1 {
            font-size: 38px;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .page-heading p {
            color: #666;
            font-size: 15px;
        }

        /* =========================
           PRODUCTS GRID
        ========================= */

        .products {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 25px;
        }

        .product-card {
            background: #fff;
            border: 1px solid #e5e5e5;
            border-radius: 12px;
            padding: 18px;
            transition: 0.25s ease;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
        }

        /* =========================
           PRODUCT IMAGE
        ========================= */

        .product-image {
            width: 100%;
            height: 220px;
            background: #f5f5f5;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 18px;
            overflow: hidden;
            font-size: 55px;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 10px;
        }

        /* =========================
           PRODUCT INFO
        ========================= */

        .product-card h3 {
            font-size: 18px;
            margin-bottom: 9px;
        }

        .description {
            color: #666;
            font-size: 14px;
            line-height: 1.5;
            min-height: 42px;
            margin-bottom: 12px;
        }

        .price {
            font-size: 19px;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .discount {
            display: inline-block;
            background: #f1f1f1;
            color: #111;
            font-size: 12px;
            font-weight: 700;
            padding: 5px 8px;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        /* =========================
           BUTTON
        ========================= */

        .button {
            display: inline-block;
            width: 100%;
            text-align: center;
            background: #111;
            color: #fff;
            text-decoration: none;
            padding: 11px 15px;
            border-radius: 7px;
            font-size: 14px;
            font-weight: 600;
            transition: 0.25s ease;
        }

        .button:hover {
            background: #333;
        }

        /* =========================
           NO PRODUCTS
        ========================= */

        .no-products {
            grid-column: 1 / -1;
            text-align: center;
            padding: 70px 20px;
            background: #fff;
            border: 1px solid #e5e5e5;
            border-radius: 12px;
        }

        .no-products h2 {
            margin-bottom: 10px;
        }

        .no-products p {
            color: #666;
        }

        /* =========================
           FOOTER
        ========================= */

        footer {
            background: #111;
            color: #fff;
            text-align: center;
            padding: 40px 20px;
            margin-top: 30px;
        }

        footer h3 {
            font-size: 21px;
            margin-bottom: 10px;
        }

        footer p {
            color: #ccc;
            margin: 7px;
            font-size: 14px;
        }

        /* =========================
           TABLET
        ========================= */

        @media (max-width: 1000px) {

            .products {
                grid-template-columns: repeat(3, 1fr);
            }

        }

        /* =========================
           MOBILE
        ========================= */

        @media (max-width: 750px) {

            header {
                text-align: center;
            }

            .header-bottom {
                flex-direction: column;
            }

            nav {
                justify-content: center;
            }

            .products {
                grid-template-columns: repeat(2, 1fr);
            }

        }

        @media (max-width: 500px) {

            .container {
                width: 92%;
                padding: 40px 0;
            }

            .site-name {
                font-size: 28px;
            }

            .page-heading h1 {
                font-size: 30px;
            }

            .products {
                grid-template-columns: 1fr;
            }

            .product-image {
                height: 240px;
            }

            nav a {
                padding: 8px 9px;
                font-size: 13px;
            }

        }

    </style>

</head>


<body>


<!-- =========================
     HEADER
========================= -->

<header>

    <div class="header-top">

        <div class="site-name">
            SAITEJA grocery 
        </div>

    </div>


    <div class="header-bottom">

        <div class="logo">
            🛒 Saiteja grocery 
        </div>


        <nav>

            <a href="index.php">
                Home
            </a>

            <a href="products.php">
                Products
            </a>

            <a href="cart.php">
                Cart
            </a>

            <a href="orders.php">
                Orders
            </a>

            <a href="login.php">
                Login
            </a>

            <a href="helpdesk.php">
                Help Desk
            </a>

        </nav>

    </div>

</header>


<!-- =========================
     PRODUCTS
========================= -->

<main class="container">


    <div class="page-heading">

        <h1>
            Our Products
        </h1>

        <p>
            Browse fresh groceries and everyday essentials.
        </p>

    </div>


    <div class="products">


        <?php

        $hasProducts = false;

        while ($product = $result->fetchArray(SQLITE3_ASSOC)) {

            $hasProducts = true;

        ?>


            <div class="product-card">


                <!-- PRODUCT IMAGE -->

                <div class="product-image">

                    <?php if (!empty($product['image'])) { ?>

                        <img
                            src="<?php echo htmlspecialchars($product['image']); ?>"
                            alt="<?php echo htmlspecialchars($product['name']); ?>"
                            onerror="this.style.display='none';"
                        >

                    <?php } else { ?>

                        🛒

                    <?php } ?>

                </div>


                <!-- PRODUCT NAME -->

                <h3>

                    <?php
                    echo htmlspecialchars(
                        $product['name']
                    );
                    ?>

                </h3>


                <!-- DESCRIPTION -->

                <p class="description">

                    <?php

                    if (!empty($product['description'])) {

                        echo htmlspecialchars(
                            $product['description']
                        );

                    } else {

                        echo "Fresh quality grocery product.";

                    }

                    ?>

                </p>


                <!-- PRICE -->

                <div class="price">

                    ₹<?php

                    echo number_format(
                        $product['selling_price'],
                        2
                    );

                    ?>

                </div>


                <!-- DISCOUNT -->

                <?php if ($product['discount'] > 0) { ?>

                    <div class="discount">

                        <?php
                        echo htmlspecialchars(
                            $product['discount']
                        );
                        ?>% OFF

                    </div>

                <?php } ?>


                <!-- VIEW PRODUCT -->

                <a
                    href="product.php?id=<?php echo $product['id']; ?>"
                    class="button"
                >
                    View Product
                </a>


            </div>


        <?php } ?>


        <?php if (!$hasProducts) { ?>

            <div class="no-products">

                <h2>
                    No Products Available
                </h2>

                <p>
                    Products will appear here once they are added.
                </p>

            </div>

        <?php } ?>


    </div>


</main>


<!-- =========================
     FOOTER
========================= -->

<footer>

    <h3>
        Saiteja grocery 
    </h3>

    <p>
        Your local online grocery store.
    </p>

    <p>
        Fresh Products • Easy Shopping • Great Prices
    </p>

    <p>
        © 2026 Saiteja grocery 
    </p>

</footer>


</body>

</html>