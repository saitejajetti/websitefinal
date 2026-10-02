<?php

require_once "database/database.php";

$result = $db->query("
    SELECT *
    FROM products
    ORDER BY id DESC
    LIMIT 8
");

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Saiteja Grocers</title>

    <!-- Modern Font -->

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="css/style.css">


    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }


        body {
            background: #ffffff;
            color: #183b2a;
        }


        /* =========================
           HEADER
        ========================= */

        header {
            padding: 22px 7%;
            border-bottom: 1px solid #dceee3;
            background: white;
        }


        /* =========================
           BIG WEBSITE NAME
        ========================= */

        .site-name {
            font-size: 42px;
            font-weight: 800;
            letter-spacing: 2px;
            color: #247a45;
            margin-bottom: 20px;
        }


        /* =========================
           HEADER BOTTOM
        ========================= */

        .header-bottom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 30px;
        }


        /* =========================
           LOGO
        ========================= */

        .logo {
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: #183b2a;
        }


        /* =========================
           NAVIGATION
        ========================= */

        nav {
            display: flex;
            align-items: center;
            gap: 7px;
        }


        nav a {
            text-decoration: none;
            color: #245238;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 0.2px;
            padding: 10px 14px;
            border-radius: 8px;
            transition:
                background 0.25s ease,
                color 0.25s ease,
                transform 0.25s ease;
        }


        nav a:hover {
            background: #247a45;
            color: white;
            transform: translateY(-2px);
        }


        /* =========================
           HERO
        ========================= */

        .hero {
            min-height: 420px;
            padding: 70px 7%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 50px;
            background: #eef9f1;
        }


        .hero-text {
            max-width: 550px;
        }


        .hero-text h1 {
            font-size: 48px;
            line-height: 1.1;
            margin-bottom: 20px;
            font-weight: 800;
            letter-spacing: -1.5px;
            color: #183b2a;
        }


        .hero-text p {
            font-size: 18px;
            line-height: 1.6;
            color: #52705d;
            margin-bottom: 30px;
        }


        /* =========================
           SHOP BUTTON
        ========================= */

        .shop-btn {
            display: inline-block;
            background: #247a45;
            color: white;
            padding: 14px 28px;
            text-decoration: none;
            border-radius: 7px;
            font-weight: 600;
            transition: 0.25s ease;
        }


        .shop-btn:hover {
            background: #185d32;
            transform: translateY(-2px);
        }


        /* =========================
           HERO BOX
        ========================= */

        .hero-box {
            width: 350px;
            height: 250px;
            background: #d9f0df;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 70px;
            border-radius: 15px;
        }


        /* =========================
           SECTIONS
        ========================= */

        .section {
            padding: 55px 7%;
        }


        .section h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 30px;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: #183b2a;
        }


        /* =========================
           CATEGORIES
        ========================= */

        .categories {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 15px;
        }


        .category {
            border: 1px solid #dceee3;
            padding: 25px 10px;
            text-align: center;
            border-radius: 10px;
            background: white;
            color: #245238;
            font-weight: 600;
            text-decoration: none;
            transition: 0.25s ease;
        }


        .category:hover {
            background: #247a45;
            color: white;
            transform: translateY(-3px);
        }


        .category-icon {
            font-size: 35px;
            margin-bottom: 10px;
        }


        /* =========================
           PRODUCTS
        ========================= */

        .products {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }


        .product {
            border: 1px solid #dceee3;
            border-radius: 10px;
            padding: 15px;
            background: white;
            transition: 0.25s ease;
        }


        .product:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(36, 122, 69, 0.12);
            border-color: #b8dec5;
        }


        .product img {
            width: 100%;
            height: 180px;
            object-fit: contain;
            margin-bottom: 15px;
        }


        .product h3 {
            margin-bottom: 8px;
            font-size: 17px;
            color: #183b2a;
        }


        .price {
            font-weight: 700;
            margin-bottom: 15px;
            font-size: 16px;
            color: #247a45;
        }


        .view-btn {
            display: inline-block;
            padding: 9px 15px;
            background: #247a45;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 600;
            transition: 0.25s ease;
        }


        .view-btn:hover {
            background: #185d32;
        }


        /* =========================
           OFFER
        ========================= */

        .offer {
            margin: 20px 7% 50px;
            padding: 45px;
            background: #247a45;
            color: white;
            text-align: center;
            border-radius: 12px;
        }


        .offer h2 {
            margin-bottom: 10px;
            font-size: 28px;
        }


        .offer p {
            color: #e8f7ed;
        }


        /* =========================
           FOOTER
        ========================= */

        footer {
            background: #183b2a;
            color: white;
            padding: 35px 7%;
            text-align: center;
        }


        footer h3 {
            font-size: 20px;
            margin-bottom: 10px;
        }


        footer p {
            margin: 8px;
            color: #d7eadc;
        }


        footer a {
            color: white;
            text-decoration: none;
        }


        footer a:hover {
            color: #a9e6bb;
        }


        /* =========================
           TABLET
        ========================= */

        @media (max-width: 900px) {

            .hero {
                flex-direction: column;
                text-align: center;
                gap: 30px;
            }


            .categories {
                grid-template-columns: repeat(3, 1fr);
            }


            .products {
                grid-template-columns: repeat(2, 1fr);
            }


            .header-bottom {
                flex-direction: column;
                gap: 15px;
            }


            nav {
                flex-wrap: wrap;
                justify-content: center;
            }

        }


        /* =========================
           MOBILE
        ========================= */

        @media (max-width: 600px) {

            header {
                text-align: center;
            }


            .site-name {
                font-size: 32px;
                letter-spacing: 1px;
            }


            .header-bottom {
                flex-direction: column;
                gap: 15px;
            }


            .logo {
                display: block;
            }


            nav {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                gap: 4px;
            }


            nav a {
                padding: 8px 10px;
                font-size: 13px;
            }


            .hero {
                padding: 50px 5%;
            }


            .hero-text h1 {
                font-size: 35px;
            }


            .hero-text p {
                font-size: 16px;
            }


            .hero-box {
                width: 100%;
                height: 220px;
            }


            .categories {
                grid-template-columns: repeat(2, 1fr);
            }


            .products {
                grid-template-columns: 1fr;
            }


            .section {
                padding: 40px 5%;
            }


            .offer {
                margin: 20px 5% 40px;
                padding: 35px 20px;
            }

        }

    </style>

</head>


<body>


<!-- =========================
     HEADER
========================= -->

<header>

    <div class="site-name">
        SAITEJA GROCERS
    </div>


    <div class="header-bottom">

        <div class="logo">
            SAITEJA Grocers
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

            <a href="login.php">
                Login
            </a>

            <a href="helpdesk.php">
                Help Desk❓
            </a>

            <a href="Admin/login.php">
                Admin Login
            </a>

        </nav>

    </div>

</header>


<!-- =========================
     HERO
========================= -->

<section class="hero">

    <div class="hero-text">

        <h1>
            Fresh Groceries,<br>
            Delivered Easily.
        </h1>

        <p>
            Shop vegetables, fruits, groceries, dairy products,
            snacks and more from Saiteja Grocers.
        </p>

        <a
            href="products.php"
            class="shop-btn"
        >
            Shop Now
        </a>

    </div>


    <div class="hero-box">
        🛒
    </div>

</section>


<!-- =========================
     CATEGORIES
========================= -->

<section class="section">

    <h2>
        Shop by Category
    </h2>


    <div class="categories">

        <a href="products.php" class="category">

            <div class="category-icon">
                🥦
            </div>

            Vegetables

        </a>


        <a href="products.php" class="category">

            <div class="category-icon">
                🍎
            </div>

            Fruits

        </a>


        <a href="products.php" class="category">

            <div class="category-icon">
                🌾
            </div>

            Grains

        </a>


        <a href="products.php" class="category">

            <div class="category-icon">
                🥛
            </div>

            Dairy

        </a>


        <a href="products.php" class="category">

            <div class="category-icon">
                🥤
            </div>

            Beverages

        </a>


        <a href="products.php" class="category">

            <div class="category-icon">
                🍪
            </div>

            Snacks

        </a>

    </div>

</section>


<!-- =========================
     OFFER
========================= -->

<section class="offer">

    <h2>
        Special Offers
    </h2>

    <p>
        Discover great deals and discounts
        on your daily groceries.
    </p>

</section>


<!-- =========================
     PRODUCTS
========================= -->

<section class="section">

    <h2>
        Latest Products
    </h2>


    <div class="products">

        <?php while ($product = $result->fetchArray(SQLITE3_ASSOC)): ?>

            <div class="product">

                <?php if (!empty($product['image'])): ?>

                    <img
                        src="<?php echo htmlspecialchars($product['image']); ?>"
                        alt="<?php echo htmlspecialchars($product['name']); ?>"
                        onerror="this.style.display='none';"
                    >

                <?php else: ?>

                    <div style="
                        height:180px;
                        display:flex;
                        align-items:center;
                        justify-content:center;
                        background:#eef9f1;
                        font-size:50px;
                    ">
                        🛒
                    </div>

                <?php endif; ?>


                <h3>

                    <?php
                    echo htmlspecialchars(
                        $product['name']
                    );
                    ?>

                </h3>


                <div class="price">

                    ₹<?php
                    echo number_format(
                        $product['selling_price'],
                        2
                    );
                    ?>

                </div>


                <a
                    href="product.php?id=<?php echo $product['id']; ?>"
                    class="view-btn"
                >
                    View Product
                </a>


            </div>

        <?php endwhile; ?>

    </div>

</section>


<!-- =========================
     FOOTER
========================= -->

<footer>

    <h3>
        Saiteja Grocers
    </h3>

    <p>
        Your local groceries shopping website.
    </p>

    <p>
        Fresh Products • Easy Shopping • Great Prices
    </p>

    <p>
        © 2026 Saiteja Grocers
        contact
    </p>

</footer>


</body>

</html>
