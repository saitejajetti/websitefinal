<?php
session_start();

require_once "database/database.php";

/*
|--------------------------------------------------------------------------
| Create Help Desk Table
|--------------------------------------------------------------------------
*/

$db->exec("
    CREATE TABLE IF NOT EXISTS helpdesk_questions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        topic TEXT NOT NULL,
        question TEXT NOT NULL,
        answer TEXT NOT NULL,
        status TEXT DEFAULT 'Active'
    )
");

/*
|--------------------------------------------------------------------------
| Add Default Questions If Table Is Empty
|--------------------------------------------------------------------------
*/

$count = $db->querySingle("SELECT COUNT(*) FROM helpdesk_questions");

if ($count == 0) {

    $questions = [

        // PRODUCTS
        [
            "Products",
            "Is a product available?",
            "You can check product availability from the Products section. If the product is shown with stock available, you can add it to your cart."
        ],
        [
            "Products",
            "How can I find a product?",
            "Use the search option at the top of the website or open the Products section to browse available grocery products."
        ],
        [
            "Products",
            "Can I see product details?",
            "Yes. Click on any product to view its price, discount, stock availability and product information."
        ],

        // PRICES & DISCOUNTS
        [
            "Prices & Discounts",
            "Where can I see the product price?",
            "The current selling price is displayed on the product card and product details page."
        ],
        [
            "Prices & Discounts",
            "How can I get a discount?",
            "Check the available offers and coupons during checkout. Enter a valid coupon code and press Claim Discount."
        ],
        [
            "Prices & Discounts",
            "Where can I see offers?",
            "Available discounts and offers can be applied during checkout when a valid coupon is available."
        ],

        // ORDERS
        [
            "Orders",
            "How can I place an order?",
            "Add the products you want to your cart, open the Cart page, continue to Checkout, enter your details and place the order."
        ],
        [
            "Orders",
            "How can I cancel my order?",
            "If cancellation is available for your order, contact the store support team with your order number."
        ],
        [
            "Orders",
            "How can I check my order status?",
            "Open the Orders section after logging in to view your order details and current order status."
        ],
        [
            "Orders",
            "Where can I see my previous orders?",
            "Log in to your account and open the Orders section to view your previous orders."
        ],

        // PAYMENTS
        [
            "Payments",
            "What payment methods are available?",
            "The website supports UPI payment using the store QR code and Cash on Delivery when available."
        ],
        [
            "Payments",
            "How can I pay using UPI?",
            "At checkout, select UPI payment, scan the displayed QR code using your UPI application and complete the payment."
        ],
        [
            "Payments",
            "Where do I enter my transaction ID?",
            "After making the UPI payment, enter your transaction or UPI reference number in the payment section before placing the order."
        ],
        [
            "Payments",
            "Is Cash on Delivery available?",
            "Cash on Delivery can be selected at checkout when the store has enabled this payment option."
        ],

        // DELIVERY
        [
            "Delivery",
            "Do you provide home delivery?",
            "Home delivery is available according to the store's delivery service and delivery area."
        ],
        [
            "Delivery",
            "How can I check my delivery status?",
            "You can check the current order status from the Orders section of your account."
        ],
        [
            "Delivery",
            "Can I change my delivery address?",
            "If you need to change the delivery address after placing an order, contact the store as soon as possible with your order number."
        ],

        // ACCOUNT
        [
            "Account",
            "How can I create an account?",
            "Click Register, enter your name, email and password, and submit the registration form."
        ],
        [
            "Account",
            "How can I log in?",
            "Click Login and enter the email and password associated with your account."
        ],
        [
            "Account",
            "What should I do if I forget my password?",
            "Contact the store support team for help with your account access."
        ],

        // RETURNS & REFUNDS
        [
            "Returns & Refunds",
            "Can I return a product?",
            "Return availability depends on the product and store return policy. Contact the store support team with your order details."
        ],
        [
            "Returns & Refunds",
            "What if I receive a damaged product?",
            "Contact the store support team as soon as possible and provide your order number and details about the damaged product."
        ],
        [
            "Returns & Refunds",
            "How can I request a refund?",
            "Contact the store support team with your order number and payment details to request assistance with a refund."
        ],

        // CART & CHECKOUT
        [
            "Cart & Checkout",
            "How can I add a product to my cart?",
            "Open the product and select the quantity you want. Then click Add to Cart."
        ],
        [
            "Cart & Checkout",
            "How can I remove a product from my cart?",
            "Open the Cart page and use the Remove option next to the product."
        ],
        [
            "Cart & Checkout",
            "How can I apply a coupon?",
            "At checkout, enter your coupon code in the discount section and click Claim Discount."
        ],
        [
            "Cart & Checkout",
            "Why is my coupon not working?",
            "The coupon may be inactive, expired, below the minimum order value, or the code may be incorrect."
        ]
    ];

    $stmt = $db->prepare("
        INSERT INTO helpdesk_questions
        (topic, question, answer, status)
        VALUES (:topic, :question, :answer, 'Active')
    ");

    foreach ($questions as $item) {

        $stmt->bindValue(":topic", $item[0], SQLITE3_TEXT);
        $stmt->bindValue(":question", $item[1], SQLITE3_TEXT);
        $stmt->bindValue(":answer", $item[2], SQLITE3_TEXT);

        $stmt->execute();
    }
}


/*
|--------------------------------------------------------------------------
| Selected Topic
|--------------------------------------------------------------------------
*/

$selectedTopic = $_GET["topic"] ?? "";


/*
|--------------------------------------------------------------------------
| Selected Question
|--------------------------------------------------------------------------
*/

$selectedQuestion = isset($_GET["question"])
    ? (int)$_GET["question"]
    : 0;


/*
|--------------------------------------------------------------------------
| Search
|--------------------------------------------------------------------------
*/

$search = trim($_GET["search"] ?? "");

$searchResults = [];

if ($search !== "") {

    $stmt = $db->prepare("
        SELECT *
        FROM helpdesk_questions
        WHERE status = 'Active'
        AND (
            question LIKE :search
            OR answer LIKE :search
            OR topic LIKE :search
        )
        ORDER BY topic, id
    ");

    $stmt->bindValue(
        ":search",
        "%" . $search . "%",
        SQLITE3_TEXT
    );

    $result = $stmt->execute();

    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $searchResults[] = $row;
    }
}


/*
|--------------------------------------------------------------------------
| Get Topics
|--------------------------------------------------------------------------
*/

$topics = [];

$result = $db->query("
    SELECT DISTINCT topic
    FROM helpdesk_questions
    WHERE status = 'Active'
    ORDER BY id
");

while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $topics[] = $row["topic"];
}


/*
|--------------------------------------------------------------------------
| Get Questions For Selected Topic
|--------------------------------------------------------------------------
*/

$topicQuestions = [];

if ($selectedTopic !== "") {

    $stmt = $db->prepare("
        SELECT *
        FROM helpdesk_questions
        WHERE topic = :topic
        AND status = 'Active'
        ORDER BY id
    ");

    $stmt->bindValue(
        ":topic",
        $selectedTopic,
        SQLITE3_TEXT
    );

    $result = $stmt->execute();

    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $topicQuestions[] = $row;
    }
}


/*
|--------------------------------------------------------------------------
| Get Selected Question Answer
|--------------------------------------------------------------------------
*/

$answerData = null;

if ($selectedQuestion > 0) {

    $stmt = $db->prepare("
        SELECT *
        FROM helpdesk_questions
        WHERE id = :id
        AND status = 'Active'
        LIMIT 1
    ");

    $stmt->bindValue(
        ":id",
        $selectedQuestion,
        SQLITE3_INTEGER
    );

    $result = $stmt->execute();

    $answerData = $result->fetchArray(SQLITE3_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Help Desk - Saiteja grocery </title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, Helvetica, sans-serif;
    background: #f7f7f7;
    color: #111;
}


/* HEADER */

header {
    background: #ffffff;
    border-bottom: 1px solid #ddd;
    padding: 18px 7%;
}

.header-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
}

.logo {
    font-size: 25px;
    font-weight: 800;
}

nav {
    display: flex;
    gap: 22px;
    flex-wrap: wrap;
}

nav a {
    text-decoration: none;
    color: #111;
    font-weight: 600;
}

nav a:hover {
    text-decoration: underline;
}


/* MAIN */

.container {
    width: 90%;
    max-width: 1100px;
    margin: 45px auto;
}


/* TITLE */

.page-title {
    text-align: center;
    margin-bottom: 35px;
}

.page-title h1 {
    font-size: 38px;
    margin-bottom: 10px;
}

.page-title p {
    color: #666;
    font-size: 16px;
}


/* SEARCH */

.search-box {
    background: white;
    padding: 25px;
    border-radius: 12px;
    border: 1px solid #ddd;
    margin-bottom: 35px;
}

.search-form {
    display: flex;
    gap: 10px;
}

.search-form input {
    flex: 1;
    padding: 15px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 16px;
}

.search-form button {
    padding: 15px 25px;
    background: #111;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
}

.search-form button:hover {
    background: #333;
}


/* SEARCH RESULTS */

.search-results {
    margin-top: 20px;
}

.search-results h2 {
    font-size: 22px;
    margin-bottom: 15px;
}

.search-result {
    display: block;
    background: white;
    border: 1px solid #ddd;
    padding: 18px;
    margin-bottom: 10px;
    border-radius: 8px;
    text-decoration: none;
    color: #111;
}

.search-result:hover {
    border-color: #111;
}

.search-result-topic {
    font-size: 13px;
    color: #777;
    margin-bottom: 6px;
}

.search-result-question {
    font-size: 17px;
    font-weight: 700;
}


/* TOPICS */

.section-title {
    font-size: 26px;
    margin-bottom: 20px;
}

.topics {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 15px;
    margin-bottom: 40px;
}

.topic-button {
    background: white;
    border: 1px solid #ddd;
    border-radius: 12px;
    padding: 25px 15px;
    text-align: center;
    text-decoration: none;
    color: #111;
    font-size: 16px;
    font-weight: 700;
    transition: 0.2s;
}

.topic-button:hover {
    background: #111;
    color: white;
    transform: translateY(-2px);
}


/* QUESTIONS */

.questions-section {
    margin-top: 20px;
}

.questions {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.question-button {
    background: white;
    border: 1px solid #ddd;
    border-radius: 9px;
    padding: 18px 20px;
    text-decoration: none;
    color: #111;
    font-size: 16px;
    font-weight: 600;
    transition: 0.2s;
}

.question-button:hover {
    background: #111;
    color: white;
}


/* ANSWER */

.answer-box {
    margin-top: 30px;
    background: white;
    border: 1px solid #ddd;
    border-radius: 12px;
    padding: 30px;
}

.answer-topic {
    color: #777;
    font-size: 14px;
    margin-bottom: 10px;
}

.answer-question {
    font-size: 24px;
    font-weight: 800;
    margin-bottom: 20px;
}

.answer-text {
    font-size: 17px;
    line-height: 1.7;
    color: #333;
}

.back-button {
    display: inline-block;
    margin-top: 20px;
    background: #111;
    color: white;
    padding: 12px 20px;
    border-radius: 7px;
    text-decoration: none;
    font-weight: 600;
}

.back-button:hover {
    background: #333;
}


/* EMPTY */

.empty {
    background: white;
    border: 1px solid #ddd;
    border-radius: 10px;
    padding: 25px;
    color: #666;
}


/* FOOTER */

footer {
    margin-top: 70px;
    background: #111;
    color: white;
    text-align: center;
    padding: 25px;
}


/* RESPONSIVE */

@media (max-width: 800px) {

    .topics {
        grid-template-columns: repeat(2, 1fr);
    }

    .header-container {
        flex-direction: column;
        align-items: flex-start;
    }

    .search-form {
        flex-direction: column;
    }

}

@media (max-width: 500px) {

    .topics {
        grid-template-columns: 1fr;
    }

    .page-title h1 {
        font-size: 30px;
    }

    nav {
        gap: 12px;
    }

}

</style>

</head>

<body>


<!-- HEADER -->

<header>

    <div class="header-container">

        <div class="logo">
            🛒 Saiteja grocery 
        </div>

        <nav>

            <a href="index.php">Home</a>

            <a href="products.php">Products</a>

            <a href="cart.php">Cart</a>

            <a href="orders.php">Orders</a>

            <a href="helpdesk.php">Help Desk</a>

            <a href="login.php">Login</a>

        </nav>

    </div>

</header>


<!-- MAIN -->

<div class="container">


    <!-- TITLE -->

    <div class="page-title">

        <h1>Help Desk</h1>

        <p>
            Find answers about products, orders, payments and delivery.
        </p>

    </div>


    <!-- SEARCH -->

    <div class="search-box">

        <form
            method="GET"
            action="helpdesk.php"
            class="search-form"
        >

            <input
                type="text"
                name="search"
                placeholder="Search your question..."
                value="<?php echo htmlspecialchars($search); ?>"
            >

            <button type="submit">
                Search
            </button>

        </form>


        <?php if ($search !== ""): ?>

            <div class="search-results">

                <h2>
                    Search Results
                </h2>


                <?php if (count($searchResults) > 0): ?>

                    <?php foreach ($searchResults as $result): ?>

                        <a
                            class="search-result"
                            href="helpdesk.php?topic=<?php echo urlencode($result["topic"]); ?>&question=<?php echo $result["id"]; ?>"
                        >

                            <div class="search-result-topic">

                                <?php echo htmlspecialchars($result["topic"]); ?>

                            </div>

                            <div class="search-result-question">

                                <?php echo htmlspecialchars($result["question"]); ?>

                            </div>

                        </a>

                    <?php endforeach; ?>

                <?php else: ?>

                    <div class="empty">

                        No matching question found.

                    </div>

                <?php endif; ?>

            </div>

        <?php endif; ?>

    </div>


    <!-- TOPICS -->

    <h2 class="section-title">
        Browse Help Topics
    </h2>


    <div class="topics">

        <?php foreach ($topics as $topic): ?>

            <a
                class="topic-button"
                href="helpdesk.php?topic=<?php echo urlencode($topic); ?>"
            >

                <?php

                $icons = [

                    "Products" => "🛒",

                    "Prices & Discounts" => "💰",

                    "Orders" => "📦",

                    "Payments" => "💳",

                    "Delivery" => "🚚",

                    "Account" => "👤",

                    "Returns & Refunds" => "🔄",

                    "Cart & Checkout" => "🛍️"

                ];

                echo $icons[$topic] ?? "❓";

                ?>

                <br>

                <?php echo htmlspecialchars($topic); ?>

            </a>

        <?php endforeach; ?>

    </div>


    <!-- QUESTIONS -->

    <?php if ($selectedTopic !== "" && count($topicQuestions) > 0): ?>

        <div class="questions-section">

            <h2 class="section-title">

                <?php echo htmlspecialchars($selectedTopic); ?>

            </h2>


            <div class="questions">

                <?php foreach ($topicQuestions as $question): ?>

                    <a
                        class="question-button"
                        href="helpdesk.php?topic=<?php echo urlencode($selectedTopic); ?>&question=<?php echo $question["id"]; ?>"
                    >

                        <?php echo htmlspecialchars($question["question"]); ?>

                    </a>

                <?php endforeach; ?>

            </div>

        </div>

    <?php endif; ?>


    <!-- ANSWER -->

    <?php if ($answerData): ?>

        <div class="answer-box">

            <div class="answer-topic">

                <?php echo htmlspecialchars($answerData["topic"]); ?>

            </div>


            <div class="answer-question">

                <?php echo htmlspecialchars($answerData["question"]); ?>

            </div>


            <div class="answer-text">

                <?php echo nl2br(htmlspecialchars($answerData["answer"])); ?>

            </div>


            <a
                class="back-button"
                href="helpdesk.php?topic=<?php echo urlencode($answerData["topic"]); ?>"
            >

                ← Back to Questions

            </a>

        </div>

    <?php endif; ?>


</div>


<!-- FOOTER -->

<footer>

    © <?php echo date("Y"); ?> Saiteja grocery . All Rights Reserved.

</footer>


</body>

</html>