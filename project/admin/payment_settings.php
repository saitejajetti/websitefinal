<?php

require_once "../database/database.php";

/* Existing QR folder */
$paymentFolder = __DIR__ . "/../images/payment/";

/* Check folder */
if (!is_dir($paymentFolder)) {
    die("QR folder not found: " . $paymentFolder);
}


/* =========================
   SAVE SETTINGS
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $upi_id = trim($_POST["upi_id"] ?? "");
    $instructions = trim($_POST["instructions"] ?? "");

    $qr_image = "";


    /* =========================
       QR UPLOAD
    ========================= */

    if (isset($_FILES["qr_image"]) &&
        $_FILES["qr_image"]["error"] === UPLOAD_ERR_OK) {

        $extension = strtolower(
            pathinfo($_FILES["qr_image"]["name"], PATHINFO_EXTENSION)
        );

        $allowed = ["jpg", "jpeg", "png", "webp"];

        if (!in_array($extension, $allowed)) {
            die("Invalid QR image format.");
        }


        $newFileName = "qr_" . time() . "." . $extension;

        $destination = $paymentFolder . $newFileName;


        if (!move_uploaded_file(
            $_FILES["qr_image"]["tmp_name"],
            $destination
        )) {

            die(
                "Unable to upload QR image.<br><br>" .
                "Destination: " . htmlspecialchars($destination)
            );
        }


        /* Path stored in database */

        $qr_image = "images/payment/" . $newFileName;
    }


    /* =========================
       CHECK EXISTING SETTINGS
    ========================= */

    $existing = $db->query("
        SELECT *
        FROM payment_settings
        LIMIT 1
    ");

    $settings = $existing->fetchArray(SQLITE3_ASSOC);


    /* =========================
       UPDATE
    ========================= */

    if ($settings) {

        if ($qr_image !== "") {

            $stmt = $db->prepare("
                UPDATE payment_settings
                SET
                    upi_id = :upi_id,
                    qr_image = :qr_image,
                    instructions = :instructions,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id
            ");

            $stmt->bindValue(
                ":qr_image",
                $qr_image,
                SQLITE3_TEXT
            );

        } else {

            $stmt = $db->prepare("
                UPDATE payment_settings
                SET
                    upi_id = :upi_id,
                    instructions = :instructions,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id
            ");
        }


        $stmt->bindValue(
            ":upi_id",
            $upi_id,
            SQLITE3_TEXT
        );

        $stmt->bindValue(
            ":instructions",
            $instructions,
            SQLITE3_TEXT
        );

        $stmt->bindValue(
            ":id",
            $settings["id"],
            SQLITE3_INTEGER
        );

        $stmt->execute();

    }


    /* =========================
       INSERT
    ========================= */

    else {

        $stmt = $db->prepare("
            INSERT INTO payment_settings
            (
                upi_id,
                qr_image,
                instructions
            )
            VALUES
            (
                :upi_id,
                :qr_image,
                :instructions
            )
        ");

        $stmt->bindValue(
            ":upi_id",
            $upi_id,
            SQLITE3_TEXT
        );

        $stmt->bindValue(
            ":qr_image",
            $qr_image,
            SQLITE3_TEXT
        );

        $stmt->bindValue(
            ":instructions",
            $instructions,
            SQLITE3_TEXT
        );

        $stmt->execute();
    }


    header("Location: payment_settings.php?saved=1");
    exit;
}


/* =========================
   GET CURRENT SETTINGS
========================= */

$result = $db->query("
    SELECT *
    FROM payment_settings
    LIMIT 1
");

$settings = $result->fetchArray(SQLITE3_ASSOC);

$upi_id = $settings["upi_id"] ?? "";
$qr_image = $settings["qr_image"] ?? "";
$instructions = $settings["instructions"] ?? "";

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Payment Settings - Saiteja grocery </title>

<style>

body {
    font-family: Arial, sans-serif;
    background: #f5f5f5;
    margin: 0;
}

.container {
    width: 90%;
    max-width: 700px;
    margin: 40px auto;
}

.card {
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

h1 {
    margin-bottom: 10px;
}

label {
    display: block;
    margin-top: 20px;
    margin-bottom: 8px;
    font-weight: bold;
}

input,
textarea {
    width: 100%;
    padding: 12px;
    box-sizing: border-box;
    border: 1px solid #ccc;
    border-radius: 6px;
}

textarea {
    height: 100px;
}

button {
    margin-top: 20px;
    padding: 12px 20px;
    background: black;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}

button:hover {
    opacity: 0.8;
}

.qr-preview {
    margin-top: 20px;
}

.qr-preview img {
    width: 220px;
    border: 1px solid #ddd;
    padding: 10px;
}

.success {
    background: #d4edda;
    padding: 12px;
    margin-bottom: 20px;
    border-radius: 6px;
}

</style>

</head>

<body>

<div class="container">

<div class="card">

<h1>⚙️ Payment Settings</h1>

<p>
Change the QR code and UPI details customers see during checkout.
</p>


<?php if (isset($_GET["saved"])) { ?>

<div class="success">
Payment settings saved successfully.
</div>

<?php } ?>


<form
    method="POST"
    enctype="multipart/form-data"
>


<label>UPI ID</label>

<input
    type="text"
    name="upi_id"
    value="<?php echo htmlspecialchars($upi_id); ?>"
    placeholder="example@upi"
    required
>


<label>Upload QR Code</label>

<input
    type="file"
    name="qr_image"
    accept=".jpg,.jpeg,.png,.webp"
>


<?php if (!empty($qr_image)) { ?>

<div class="qr-preview">

<p>Current QR Code:</p>

<img
    src="../<?php echo htmlspecialchars($qr_image); ?>"
    alt="UPI QR Code"
>

</div>

<?php } ?>


<label>Payment Instructions</label>

<textarea
    name="instructions"
    placeholder="Scan the QR code and enter your transaction ID."
><?php echo htmlspecialchars($instructions); ?></textarea>


<button type="submit">
Save Payment Settings
</button>


</form>

</div>

</div>

</body>

</html>