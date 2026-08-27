<?php

session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

require_once "../database.php";

$message = "";

/* =========================
   DEFAULT VALUES
========================= */

$businessName = "Emirates Butchery";
$businessPhone = "0700 000 000";
$businessWhatsapp = "254700000000";
$businessEmail = "info@emiratesbutchery.co.ke";
$businessLocation = "Nairobi, Kenya";
$openingHours = "7:00 AM - 9:00 PM";


/* =========================
   LOAD SETTINGS
========================= */

$stmt = $conn->prepare("
    SELECT *
    FROM business_settings
    WHERE id = 1
    LIMIT 1
");

$stmt->execute();

$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {

    $settings = $result->fetch_assoc();

    $businessName = $settings['business_name'];
    $businessPhone = $settings['phone'];
    $businessWhatsapp = $settings['whatsapp'];
    $businessEmail = $settings['email'];
    $businessLocation = $settings['location'];
    $openingHours = $settings['opening_hours'];
}


/* =========================
   SAVE BUSINESS SETTINGS
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['save_business'])) {

    $businessName = trim($_POST['business_name']);
    $businessPhone = trim($_POST['business_phone']);
    $businessWhatsapp = trim($_POST['business_whatsapp']);
    $businessEmail = trim($_POST['business_email']);
    $businessLocation = trim($_POST['business_location']);
    $openingHours = trim($_POST['opening_hours']);


    $stmt = $conn->prepare("
        UPDATE business_settings
        SET
            business_name = ?,
            phone = ?,
            whatsapp = ?,
            email = ?,
            location = ?,
            opening_hours = ?
        WHERE id = 1
    ");


    $stmt->bind_param(
        "ssssss",
        $businessName,
        $businessPhone,
        $businessWhatsapp,
        $businessEmail,
        $businessLocation,
        $openingHours
    );


    if ($stmt->execute()) {

        $message = "Business settings updated successfully.";

    } else {

        $message = "Error updating business settings.";

    }
}


/* =========================
   MEAT PRICES
========================= */

$prices = [

    "Premium Beef" => 650,
    "Beef Steak" => 900,
    "Fresh Chicken" => 550,
    "Goat Meat" => 800

];


/* =========================
   LOAD SAVED PRICES
========================= */

if (isset($_SESSION['meat_prices'])) {

    $prices = $_SESSION['meat_prices'];

}


/* =========================
   SAVE PRICES
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['save_prices'])) {

    foreach ($prices as $meat => $oldPrice) {

        $fieldName = strtolower(
            str_replace(" ", "_", $meat)
        );

        if (isset($_POST[$fieldName])) {

            $newPrice = (float) $_POST[$fieldName];

            if ($newPrice > 0) {

                $prices[$meat] = $newPrice;

            }

        }

    }

    $_SESSION['meat_prices'] = $prices;

    $message = "Meat prices updated successfully.";
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Settings | Emirates Butchery</title>

<style>

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: Arial, Helvetica, sans-serif;
}

body {
    background: #f4f5f7;
    color: #222;
}

/* SIDEBAR */

.sidebar {
    position: fixed;
    left: 0;
    top: 0;
    width: 240px;
    height: 100vh;
    background: #111;
    color: white;
    display: flex;
    flex-direction: column;
}

.sidebar-logo {
    padding: 25px 20px;
    border-bottom: 1px solid #333;
}

.sidebar-logo h2 {
    font-size: 22px;
}

.sidebar-logo span {
    color: #e63946;
}

.sidebar-logo p {
    color: #aaa;
    font-size: 12px;
    margin-top: 5px;
}

.sidebar-menu {
    list-style: none;
    padding: 20px 10px;
}

.sidebar-menu li {
    margin-bottom: 5px;
}

.sidebar-menu a {
    display: block;
    padding: 13px 15px;
    color: #ddd;
    text-decoration: none;
    border-radius: 6px;
    font-weight: bold;
}

.sidebar-menu a:hover,
.sidebar-menu a.active {
    background: #e63946;
    color: white;
}

.sidebar-menu span {
    margin-right: 10px;
}

.sidebar-bottom {
    margin-top: auto;
    padding: 15px;
    border-top: 1px solid #333;
}

.logout {
    display: block;
    background: #e63946;
    color: white;
    padding: 12px;
    text-align: center;
    text-decoration: none;
    border-radius: 6px;
    font-weight: bold;
}

/* MAIN */

.main-content {
    margin-left: 240px;
    min-height: 100vh;
}

.top-header {
    background: white;
    padding: 20px 30px;
    border-bottom: 1px solid #ddd;
    display: flex;
    justify-content: space-between;
}

.admin-label {
    color: #777;
}

.container {
    max-width: 1100px;
    margin: auto;
    padding: 30px 25px;
}

.page-title {
    margin-bottom: 25px;
}

.page-title h1 {
    font-size: 32px;
    margin-bottom: 8px;
}

.page-title p {
    color: #666;
}

/* MESSAGE */

.message {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
    padding: 15px;
    border-radius: 6px;
    margin-bottom: 25px;
    font-weight: bold;
}

/* CARD */

.settings-card {
    background: white;
    padding: 30px;
    border-radius: 10px;
    margin-bottom: 25px;
    box-shadow: 0 3px 12px rgba(0,0,0,.08);
}

.settings-card h2 {
    margin-bottom: 8px;
}

.settings-card > p {
    color: #666;
    margin-bottom: 25px;
}

/* FORM */

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-weight: bold;
    margin-bottom: 8px;
}

.form-group input {
    width: 100%;
    padding: 13px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 15px;
}

.form-group input:focus {
    outline: none;
    border-color: #e63946;
}

.save-btn {
    background: #e63946;
    color: white;
    border: none;
    padding: 13px 22px;
    border-radius: 6px;
    font-size: 15px;
    font-weight: bold;
    cursor: pointer;
}

.save-btn:hover {
    background: #c1121f;
}

/* PRICE TABLE */

.price-table {
    width: 100%;
    border-collapse: collapse;
}

.price-table th {
    background: #111;
    color: white;
    padding: 14px;
    text-align: left;
}

.price-table td {
    padding: 14px;
    border-bottom: 1px solid #eee;
}

.price-input {
    width: 150px;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
}

/* SYSTEM */

.system-info {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
}

.system-info p {
    margin-bottom: 12px;
}

/* MOBILE */

@media(max-width:800px) {

    .sidebar {
        position: relative;
        width: 100%;
        height: auto;
    }

    .sidebar-menu {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
    }

    .main-content {
        margin-left: 0;
    }

    .container {
        padding: 20px 15px;
    }

}

</style>

</head>

<body>


<!-- SIDEBAR -->

<aside class="sidebar">

    <div class="sidebar-logo">

        <h2>
            Emirates <span>Butchery</span>
        </h2>

        <p>
            ADMIN PANEL
        </p>

    </div>


    <ul class="sidebar-menu">

        <li>
            <a href="dashboard.php">
                <span>🏠</span>
                Dashboard
            </a>
        </li>

        <li>
            <a href="dashboard.php#orders">
                <span>📦</span>
                Orders
            </a>
        </li>

        <li>
            <a href="customers.php">
                <span>👥</span>
                Customers
            </a>
        </li>

        <li>
            <a href="sales.php">
                <span>📊</span>
                Sales
            </a>
        </li>

        <li>
            <a href="settings.php" class="active">
                <span>⚙️</span>
                Settings
            </a>
        </li>

    </ul>


    <div class="sidebar-bottom">

        <a href="logout.php" class="logout">
            🚪 Logout
        </a>

    </div>

</aside>


<!-- MAIN -->

<div class="main-content">


<header class="top-header">

    <h2>
        Administration Settings
    </h2>

    <div class="admin-label">
        🔐 Administrator
    </div>

</header>


<main class="container">


<div class="page-title">

    <h1>
        ⚙️ Settings
    </h1>

    <p>
        Manage Emirates Butchery business information and prices.
    </p>

</div>


<?php if ($message !== ""): ?>

<div class="message">

    ✅ <?php echo htmlspecialchars($message); ?>

</div>

<?php endif; ?>


<!-- BUSINESS INFORMATION -->

<div class="settings-card">

<h2>
    🏪 Business Information
</h2>

<p>
    Changes made here will be used by the Emirates Butchery website.
</p>


<form method="POST">


<div class="form-group">

<label>
Business Name
</label>

<input
type="text"
name="business_name"
value="<?php echo htmlspecialchars($businessName); ?>"
required
>

</div>


<div class="form-group">

<label>
📞 Phone Number
</label>

<input
type="text"
name="business_phone"
value="<?php echo htmlspecialchars($businessPhone); ?>"
required
>

</div>


<div class="form-group">

<label>
💬 WhatsApp Number
</label>

<input
type="text"
name="business_whatsapp"
value="<?php echo htmlspecialchars($businessWhatsapp); ?>"
required
>

</div>


<div class="form-group">

<label>
📧 Business Email
</label>

<input
type="email"
name="business_email"
value="<?php echo htmlspecialchars($businessEmail); ?>"
required
>

</div>


<div class="form-group">

<label>
📍 Business Location
</label>

<input
type="text"
name="business_location"
value="<?php echo htmlspecialchars($businessLocation); ?>"
required
>

</div>


<div class="form-group">

<label>
🕒 Opening Hours
</label>

<input
type="text"
name="opening_hours"
value="<?php echo htmlspecialchars($openingHours); ?>"
required
>

</div>


<button
type="submit"
name="save_business"
class="save-btn"
>

💾 Save Business Information

</button>


</form>

</div>


<!-- MEAT PRICES -->

<div class="settings-card">

<h2>
💰 Meat Prices
</h2>

<p>
Update the selling price per kilogram.
</p>


<form method="POST">

<table class="price-table">

<thead>

<tr>
<th>Meat Product</th>
<th>Price Per Kg</th>
</tr>

</thead>

<tbody>

<?php foreach ($prices as $meat => $price): ?>

<?php

$fieldName = strtolower(
str_replace(" ", "_", $meat)
);

?>

<tr>

<td>
<strong>
<?php echo htmlspecialchars($meat); ?>
</strong>
</td>

<td>

<input
type="number"
class="price-input"
name="<?php echo htmlspecialchars($fieldName); ?>"
value="<?php echo htmlspecialchars($price); ?>"
min="1"
required
>

 KSh

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

<br>

<button
type="submit"
name="save_prices"
class="save-btn"
>

💾 Save Meat Prices

</button>

</form>

</div>


<!-- SYSTEM INFORMATION -->

<div class="settings-card">

<h2>
🖥️ System Information
</h2>

<p>
Current Emirates Butchery administration information.
</p>


<div class="system-info">

<p>
<strong>Business:</strong>
<?php echo htmlspecialchars($businessName); ?>
</p>

<p>
<strong>Phone:</strong>
<?php echo htmlspecialchars($businessPhone); ?>
</p>

<p>
<strong>Email:</strong>
<?php echo htmlspecialchars($businessEmail); ?>
</p>

<p>
<strong>Location:</strong>
<?php echo htmlspecialchars($businessLocation); ?>
</p>

<p>
<strong>Opening Hours:</strong>
<?php echo htmlspecialchars($openingHours); ?>
</p>

<p>
<strong>Admin Panel:</strong>
✓ Active
</p>

<p>
<strong>Database:</strong>
✓ Connected
</p>

<p>
<strong>System:</strong>
Emirates Butchery Management System
</p>

</div>

</div>


</main>

</div>

</body>

</html>