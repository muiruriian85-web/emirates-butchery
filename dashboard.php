<?php

session_start();

/* =========================
   ADMIN LOGIN CHECK
========================= */

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

require_once "../database.php";


/* =========================
   SEARCH AND STATUS FILTER
========================= */

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';


/* =========================
   ORDER QUERY
========================= */

$sql = "SELECT * FROM orders WHERE 1=1";

$params = [];
$types = "";


/* SEARCH */

if ($search !== "") {

    $sql .= " AND (
        customer_name LIKE ?
        OR phone LIKE ?
        OR email LIKE ?
        OR id LIKE ?
    )";

    $searchValue = "%" . $search . "%";

    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;

    $types .= "ssss";
}


/* STATUS FILTER */

if ($status !== "") {

    $sql .= " AND order_status = ?";

    $params[] = $status;

    $types .= "s";
}


$sql .= " ORDER BY created_at DESC";


$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . htmlspecialchars($conn->error));
}


if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}


$stmt->execute();

$result = $stmt->get_result();


/* =========================
   DASHBOARD STATISTICS
========================= */

$totalOrders = 0;
$pendingOrders = 0;
$confirmedOrders = 0;
$preparingOrders = 0;
$deliveryOrders = 0;
$deliveredOrders = 0;
$cancelledOrders = 0;

$actualSales = 0;
$totalMeatSold = 0;


/* =========================
   MEAT PRICES
========================= */

$prices = [

    "Premium Beef" => 650,

    "Beef Steak" => 900,

    "Fresh Chicken" => 550,

    "Goat Meat" => 800

];


/* Load prices from session if available */

if (isset($_SESSION['meat_prices']) && is_array($_SESSION['meat_prices'])) {

    $prices = array_merge($prices, $_SESSION['meat_prices']);

}


/* =========================
   GET ALL ORDERS
========================= */

$statsQuery = "SELECT * FROM orders";

$statsResult = $conn->query($statsQuery);


if ($statsResult) {

    while ($order = $statsResult->fetch_assoc()) {

        $totalOrders++;

        $currentStatus = trim($order['order_status']);


        /* STATUS COUNTS */

        switch ($currentStatus) {

            case "Pending":
                $pendingOrders++;
                break;

            case "Confirmed":
                $confirmedOrders++;
                break;

            case "Preparing":
                $preparingOrders++;
                break;

            case "Out for Delivery":
                $deliveryOrders++;
                break;

            case "Delivered":
                $deliveredOrders++;
                break;

            case "Cancelled":
                $cancelledOrders++;
                break;

        }


        /* =========================
           ACTUAL SALES
           DELIVERED ORDERS ONLY
        ========================= */

        if ($currentStatus === "Delivered") {

            $meat = $order['meat_type'];

            $quantity = (float)$order['quantity'];


            if (isset($prices[$meat])) {

                $actualSales += $prices[$meat] * $quantity;

                $totalMeatSold += $quantity;

            }

        }

    }

}


/* =========================
   TOTAL CUSTOMERS
========================= */

$totalCustomers = 0;


$customerQuery = "

    SELECT COUNT(*) AS total_customers

    FROM (

        SELECT customer_name, email, phone

        FROM orders

        GROUP BY customer_name, email, phone

    ) AS customers

";


$customerResult = $conn->query($customerQuery);


if ($customerResult) {

    $customerRow = $customerResult->fetch_assoc();

    $totalCustomers = (int)$customerRow['total_customers'];

}


/* =========================
   RECENT CUSTOMERS
========================= */

$customersQuery = "

    SELECT

        customer_name,
        email,
        phone,
        delivery_location,
        COUNT(*) AS total_orders,
        MAX(created_at) AS last_order

    FROM orders

    GROUP BY customer_name, email, phone, delivery_location

    ORDER BY last_order DESC

    LIMIT 6

";


$customersResult = $conn->query($customersQuery);

?>


<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Emirates Butchery Admin</title>


<style>

/* =========================
   GENERAL
========================= */

* {

    box-sizing: border-box;

    margin: 0;

    padding: 0;

    font-family: Arial, Helvetica, sans-serif;

}


html {

    scroll-behavior: smooth;

}


body {

    background: #f4f5f7;

    color: #222;

}


/* =========================
   SIDEBAR
========================= */

.sidebar {

    position: fixed;

    left: 0;

    top: 0;

    width: 240px;

    height: 100vh;

    background: #111;

    color: white;

    z-index: 1000;

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

    transition: 0.2s;

}


.sidebar-menu a:hover {

    background: #e63946;

    color: white;

}


.sidebar-menu a.active {

    background: #e63946;

    color: white;

}


.sidebar-menu a span {

    margin-right: 10px;

}


/* =========================
   LOGOUT
========================= */

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


.logout:hover {

    background: #c1121f;

}


/* =========================
   MAIN CONTENT
========================= */

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

    align-items: center;

    position: sticky;

    top: 0;

    z-index: 500;

}


.top-header h2 {

    font-size: 22px;

}


.admin-label {

    color: #777;

    font-size: 14px;

}


.container {

    max-width: 1400px;

    margin: auto;

    padding: 30px 25px;

}


/* =========================
   PAGE TITLE
========================= */

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


/* =========================
   STATISTICS
========================= */

.stats {

    display: grid;

    grid-template-columns: repeat(4, 1fr);

    gap: 20px;

    margin-bottom: 40px;

}


.stat-card {

    background: white;

    padding: 25px;

    border-radius: 10px;

    box-shadow: 0 3px 12px rgba(0,0,0,.08);

}


.stat-card h3 {

    color: #666;

    font-size: 15px;

    margin-bottom: 10px;

}


.stat-card .number {

    font-size: 30px;

    font-weight: bold;

}


.customers-stat {

    border-left: 5px solid #17a2b8;

}


.pending {

    border-left: 5px solid #f4b400;

}


.confirmed {

    border-left: 5px solid #4285f4;

}


.preparing {

    border-left: 5px solid #ff9800;

}


.delivery {

    border-left: 5px solid #9c27b0;

}


.delivered {

    border-left: 5px solid #28a745;

}


.cancelled {

    border-left: 5px solid #dc3545;

}


.sales {

    border-left: 5px solid #e63946;

}


/* =========================
   SECTIONS
========================= */

.section {

    background: white;

    border-radius: 10px;

    padding: 25px;

    margin-bottom: 35px;

    box-shadow: 0 3px 12px rgba(0,0,0,.08);

}


.section-title {

    font-size: 24px;

    margin-bottom: 20px;

}


/* =========================
   ORDERS
========================= */

.orders-header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 20px;

}


.filters {

    display: flex;

    gap: 10px;

    margin-bottom: 20px;

}


.filters input,
.filters select {

    padding: 12px;

    border: 1px solid #ccc;

    border-radius: 6px;

    font-size: 14px;

}


.filters input {

    flex: 1;

}


.search-btn {

    background: #111;

    color: white;

    border: none;

    padding: 12px 20px;

    border-radius: 6px;

    cursor: pointer;

}


.search-btn:hover {

    background: #e63946;

}


.clear-btn {

    background: #ddd;

    color: #222;

    padding: 12px 20px;

    border-radius: 6px;

    text-decoration: none;

}


/* =========================
   TABLE
========================= */

.table-wrapper {

    overflow-x: auto;

}


table {

    width: 100%;

    border-collapse: collapse;

    min-width: 1100px;

}


th {

    background: #111;

    color: white;

    padding: 14px;

    text-align: left;

}


td {

    padding: 14px;

    border-bottom: 1px solid #eee;

    vertical-align: top;

}


tr:hover {

    background: #fafafa;

}


/* =========================
   STATUS
========================= */

.status {

    display: inline-block;

    padding: 6px 10px;

    border-radius: 20px;

    font-size: 12px;

    font-weight: bold;

}


.status-pending {

    background: #fff3cd;

    color: #856404;

}


.status-confirmed {

    background: #dbeafe;

    color: #1d4ed8;

}


.status-preparing {

    background: #ffe0b2;

    color: #e65100;

}


.status-delivery {

    background: #eadcf8;

    color: #7b1fa2;

}


.status-delivered {

    background: #d4edda;

    color: #155724;

}


.status-cancelled {

    background: #f8d7da;

    color: #721c24;

}


/* =========================
   UPDATE FORM
========================= */

.update-form select {

    padding: 8px;

    border: 1px solid #ccc;

    border-radius: 5px;

}


.update-form button {

    margin-top: 6px;

    padding: 8px 12px;

    background: #111;

    color: white;

    border: none;

    border-radius: 5px;

    cursor: pointer;

}


.update-form button:hover {

    background: #e63946;

}


/* =========================
   CONTACT BUTTONS
========================= */

.action-btn {

    display: inline-block;

    padding: 7px 10px;

    border-radius: 5px;

    text-decoration: none;

    color: white;

    font-size: 12px;

    margin-top: 5px;

}


.call-btn {

    background: #333;

}


.whatsapp-btn {

    background: #25D366;

}


/* =========================
   CUSTOMER SECTION
========================= */

.customer-grid {

    display: grid;

    grid-template-columns: repeat(3, 1fr);

    gap: 20px;

}


.customer-card {

    background: #f8f9fa;

    padding: 20px;

    border-radius: 10px;

    border: 1px solid #eee;

}


.customer-card h3 {

    margin-bottom: 15px;

}


.customer-info {

    margin-bottom: 10px;

    color: #555;

}


.customer-info strong {

    color: #222;

}


.customer-orders {

    margin-top: 15px;

    padding: 10px;

    background: white;

    border-radius: 6px;

    font-weight: bold;

}


.customer-actions {

    margin-top: 15px;

}


/* VIEW ALL */

.view-all {

    display: inline-block;

    margin-top: 20px;

    padding: 11px 18px;

    background: #111;

    color: white;

    text-decoration: none;

    border-radius: 6px;

}


.view-all:hover {

    background: #e63946;

}


/* =========================
   SALES
========================= */

.sales-box {

    display: grid;

    grid-template-columns: repeat(3, 1fr);

    gap: 20px;

}


.sales-card {

    padding: 25px;

    background: #f8f9fa;

    border-radius: 10px;

    border-left: 5px solid #e63946;

}


.sales-card h3 {

    color: #666;

    font-size: 15px;

    margin-bottom: 10px;

}


.sales-card strong {

    font-size: 28px;

}


/* =========================
   SETTINGS
========================= */

.settings-box {

    background: #f8f9fa;

    padding: 20px;

    border-radius: 10px;

}


.settings-box p {

    margin-bottom: 10px;

}


/* =========================
   MOBILE
========================= */

@media(max-width:1100px) {

    .stats {

        grid-template-columns: repeat(2, 1fr);

    }


    .customer-grid {

        grid-template-columns: repeat(2, 1fr);

    }

}


@media(max-width:800px) {

    .sidebar {

        position: relative;

        width: 100%;

        height: auto;

    }


    .sidebar-logo {

        text-align: center;

    }


    .sidebar-menu {

        display: flex;

        flex-wrap: wrap;

        justify-content: center;

        padding: 10px;

    }


    .sidebar-menu li {

        margin: 3px;

    }


    .main-content {

        margin-left: 0;

    }


    .top-header {

        padding: 15px 20px;

    }


    .customer-grid {

        grid-template-columns: 1fr;

    }


    .sales-box {

        grid-template-columns: 1fr;

    }

}


@media(max-width:600px) {

    .stats {

        grid-template-columns: 1fr;

    }


    .filters {

        flex-direction: column;

    }


    .container {

        padding: 20px 12px;

    }


    .page-title h1 {

        font-size: 26px;

    }

}

</style>

</head>


<body>


<!-- =========================
     SIDEBAR
========================= -->

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


        <!-- DASHBOARD -->

        <li>

            <a href="dashboard.php" class="active">

                <span>🏠</span>

                Dashboard

            </a>

        </li>


        <!-- ORDERS -->

        <li>

            <a href="dashboard.php#orders">

                <span>📦</span>

                Orders

            </a>

        </li>


        <!-- CUSTOMERS -->

        <li>

            <a href="customers.php">

                <span>👥</span>

                Customers

            </a>

        </li>


        <!-- SALES -->

        <li>

            <a href="sales.php">

                <span>📊</span>

                Sales

            </a>

        </li>


        <!-- SETTINGS -->

        <!-- IMPORTANT:
             This opens settings.php.
             It does NOT use #settings.
        -->

        <li>

            <a href="settings.php">

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


<!-- =========================
     MAIN CONTENT
========================= -->

<div class="main-content">


    <!-- TOP HEADER -->

    <div class="top-header">

        <h2>

            Administration Dashboard

        </h2>


        <div class="admin-label">

            🔐 Administrator

        </div>

    </div>


    <main class="container">


        <!-- PAGE TITLE -->

        <div class="page-title">

            <h1>

                Welcome to Emirates Butchery

            </h1>


            <p>

                Monitor and manage Emirates Butchery customer orders.

            </p>

        </div>


        <!-- =========================
             STATISTICS
        ========================= -->

        <div class="stats">


            <div class="stat-card">

                <h3>Total Orders</h3>

                <div class="number">

                    <?php echo $totalOrders; ?>

                </div>

            </div>


            <div class="stat-card customers-stat">

                <h3>Total Customers</h3>

                <div class="number">

                    <?php echo $totalCustomers; ?>

                </div>

            </div>


            <div class="stat-card pending">

                <h3>Pending Orders</h3>

                <div class="number">

                    <?php echo $pendingOrders; ?>

                </div>

            </div>


            <div class="stat-card confirmed">

                <h3>Confirmed</h3>

                <div class="number">

                    <?php echo $confirmedOrders; ?>

                </div>

            </div>


            <div class="stat-card preparing">

                <h3>Preparing</h3>

                <div class="number">

                    <?php echo $preparingOrders; ?>

                </div>

            </div>


            <div class="stat-card delivery">

                <h3>Out for Delivery</h3>

                <div class="number">

                    <?php echo $deliveryOrders; ?>

                </div>

            </div>


            <div class="stat-card delivered">

                <h3>Delivered</h3>

                <div class="number">

                    <?php echo $deliveredOrders; ?>

                </div>

            </div>


            <div class="stat-card cancelled">

                <h3>Cancelled</h3>

                <div class="number">

                    <?php echo $cancelledOrders; ?>

                </div>

            </div>


            <div class="stat-card sales">

                <h3>Actual Sales</h3>

                <div class="number">

                    KSh <?php echo number_format($actualSales); ?>

                </div>

            </div>


        </div>


        <!-- =========================
             ORDERS
        ========================= -->

        <section class="section" id="orders">


            <div class="orders-header">

                <h2 class="section-title">

                    📦 Customer Orders

                </h2>

            </div>


            <!-- SEARCH -->

            <form method="GET" class="filters">


                <input
                    type="text"
                    name="search"
                    placeholder="Search by customer, phone, email or order ID..."
                    value="<?php echo htmlspecialchars($search); ?>"
                >


                <select name="status">


                    <option value="">

                        All Orders

                    </option>


                    <option value="Pending"
                        <?php echo ($status === "Pending") ? "selected" : ""; ?>>

                        Pending

                    </option>


                    <option value="Confirmed"
                        <?php echo ($status === "Confirmed") ? "selected" : ""; ?>>

                        Confirmed

                    </option>


                    <option value="Preparing"
                        <?php echo ($status === "Preparing") ? "selected" : ""; ?>>

                        Preparing

                    </option>


                    <option value="Out for Delivery"
                        <?php echo ($status === "Out for Delivery") ? "selected" : ""; ?>>

                        Out for Delivery

                    </option>


                    <option value="Delivered"
                        <?php echo ($status === "Delivered") ? "selected" : ""; ?>>

                        Delivered

                    </option>


                    <option value="Cancelled"
                        <?php echo ($status === "Cancelled") ? "selected" : ""; ?>>

                        Cancelled

                    </option>


                </select>


                <button type="submit" class="search-btn">

                    Search

                </button>


                <a href="dashboard.php" class="clear-btn">

                    Clear

                </a>


            </form>


            <!-- ORDERS TABLE -->

            <div class="table-wrapper">


                <table>


                    <thead>

                        <tr>

                            <th>Order</th>

                            <th>Customer</th>

                            <th>Contact</th>

                            <th>Meat</th>

                            <th>Quantity</th>

                            <th>Delivery Location</th>

                            <th>Instructions</th>

                            <th>Status</th>

                            <th>Date</th>

                            <th>Update</th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php if ($result->num_rows > 0): ?>


                        <?php while ($order = $result->fetch_assoc()): ?>


                            <?php

                            $statusClass = "status-pending";


                            switch ($order['order_status']) {

                                case "Confirmed":
                                    $statusClass = "status-confirmed";
                                    break;

                                case "Preparing":
                                    $statusClass = "status-preparing";
                                    break;

                                case "Out for Delivery":
                                    $statusClass = "status-delivery";
                                    break;

                                case "Delivered":
                                    $statusClass = "status-delivered";
                                    break;

                                case "Cancelled":
                                    $statusClass = "status-cancelled";
                                    break;

                            }

                            ?>


                            <tr>


                                <td>

                                    <strong>

                                        #<?php echo (int)$order['id']; ?>

                                    </strong>

                                </td>


                                <td>

                                    <strong>

                                        <?php echo htmlspecialchars($order['customer_name']); ?>

                                    </strong>

                                    <br>

                                    <?php echo htmlspecialchars($order['email']); ?>

                                </td>


                                <td>

                                    <?php echo htmlspecialchars($order['phone']); ?>

                                    <br>


                                    <a
                                        class="action-btn call-btn"
                                        href="tel:<?php echo htmlspecialchars($order['phone']); ?>"
                                    >

                                        📞 Call

                                    </a>


                                    <a
                                        class="action-btn whatsapp-btn"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        href="https://wa.me/<?php echo htmlspecialchars($order['phone']); ?>"
                                    >

                                        💬 WhatsApp

                                    </a>

                                </td>


                                <td>

                                    <?php echo htmlspecialchars($order['meat_type']); ?>

                                </td>


                                <td>

                                    <?php echo htmlspecialchars($order['quantity']); ?> kg

                                </td>


                                <td>

                                    <?php echo htmlspecialchars($order['delivery_location']); ?>

                                </td>


                                <td>

                                    <?php

                                    echo !empty($order['instructions'])

                                        ? htmlspecialchars($order['instructions'])

                                        : "None";

                                    ?>

                                </td>


                                <td>

                                    <span class="status <?php echo $statusClass; ?>">

                                        <?php echo htmlspecialchars($order['order_status']); ?>

                                    </span>

                                </td>


                                <td>

                                    <?php echo htmlspecialchars($order['created_at']); ?>

                                </td>


                                <td>


                                    <form
                                        class="update-form"
                                        action="update_order.php"
                                        method="POST"
                                    >


                                        <input
                                            type="hidden"
                                            name="order_id"
                                            value="<?php echo (int)$order['id']; ?>"
                                        >


                                        <select name="order_status">


                                            <option value="Pending"
                                                <?php echo ($order['order_status'] === "Pending") ? "selected" : ""; ?>>

                                                Pending

                                            </option>


                                            <option value="Confirmed"
                                                <?php echo ($order['order_status'] === "Confirmed") ? "selected" : ""; ?>>

                                                Confirmed

                                            </option>


                                            <option value="Preparing"
                                                <?php echo ($order['order_status'] === "Preparing") ? "selected" : ""; ?>>

                                                Preparing

                                            </option>


                                            <option value="Out for Delivery"
                                                <?php echo ($order['order_status'] === "Out for Delivery") ? "selected" : ""; ?>>

                                                Out for Delivery

                                            </option>


                                            <option value="Delivered"
                                                <?php echo ($order['order_status'] === "Delivered") ? "selected" : ""; ?>>

                                                Delivered

                                            </option>


                                            <option value="Cancelled"
                                                <?php echo ($order['order_status'] === "Cancelled") ? "selected" : ""; ?>>

                                                Cancelled

                                            </option>


                                        </select>


                                        <button type="submit">

                                            Update

                                        </button>


                                    </form>


                                </td>


                            </tr>


                        <?php endwhile; ?>


                    <?php else: ?>


                        <tr>

                            <td colspan="10" style="text-align:center;padding:30px;">

                                No orders found.

                            </td>

                        </tr>


                    <?php endif; ?>


                    </tbody>


                </table>


            </div>


        </section>


        <!-- =========================
             CUSTOMERS
        ========================= -->

        <section class="section" id="customers">


            <h2 class="section-title">

                👥 Customers

            </h2>


            <p style="color:#666;margin-bottom:20px;">

                Customer information and recent order history.

            </p>


            <div class="customer-grid">


                <?php if ($customersResult && $customersResult->num_rows > 0): ?>


                    <?php while ($customer = $customersResult->fetch_assoc()): ?>


                        <div class="customer-card">


                            <h3>

                                👤

                                <?php echo htmlspecialchars($customer['customer_name']); ?>

                            </h3>


                            <div class="customer-info">

                                <strong>Email:</strong>

                                <br>

                                <?php echo htmlspecialchars($customer['email']); ?>

                            </div>


                            <div class="customer-info">

                                <strong>Phone:</strong>

                                <br>

                                <?php echo htmlspecialchars($customer['phone']); ?>

                            </div>


                            <div class="customer-info">

                                <strong>Location:</strong>

                                <br>

                                <?php echo htmlspecialchars($customer['delivery_location']); ?>

                            </div>


                            <div class="customer-orders">

                                📦 Total Orders:

                                <?php echo (int)$customer['total_orders']; ?>

                            </div>


                            <div class="customer-info" style="margin-top:10px;">

                                <strong>Last Order:</strong>

                                <br>

                                <?php echo htmlspecialchars($customer['last_order']); ?>

                            </div>


                            <div class="customer-actions">


                                <a
                                    class="action-btn call-btn"
                                    href="tel:<?php echo htmlspecialchars($customer['phone']); ?>"
                                >

                                    📞 Call

                                </a>


                                <a
                                    class="action-btn whatsapp-btn"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    href="https://wa.me/<?php echo htmlspecialchars($customer['phone']); ?>"
                                >

                                    💬 WhatsApp

                                </a>


                            </div>


                        </div>


                    <?php endwhile; ?>


                <?php else: ?>


                    <p>

                        No customers found.

                    </p>


                <?php endif; ?>


            </div>


            <a href="customers.php" class="view-all">

                👥 View All Customers

            </a>


        </section>


        <!-- =========================
             SALES
        ========================= -->

        <section class="section" id="sales">


            <h2 class="section-title">

                📊 Sales

            </h2>


            <p style="color:#666;margin-bottom:20px;">

                Sales are calculated from delivered orders only.

            </p>


            <div class="sales-box">


                <div class="sales-card">

                    <h3>

                        Actual Sales

                    </h3>


                    <strong>

                        KSh <?php echo number_format($actualSales); ?>

                    </strong>

                </div>


                <div class="sales-card">

                    <h3>

                        Delivered Orders

                    </h3>


                    <strong>

                        <?php echo $deliveredOrders; ?>

                    </strong>

                </div>


                <div class="sales-card">

                    <h3>

                        Meat Sold

                    </h3>


                    <strong>

                        <?php echo number_format($totalMeatSold, 2); ?> kg

                    </strong>

                </div>


            </div>


            <a href="sales.php" class="view-all">

                📊 View Full Sales

            </a>


        </section>


        <!-- =========================
             SETTINGS SUMMARY
        ========================= -->

        <section class="section" id="settings">


            <h2 class="section-title">

                ⚙️ Settings

            </h2>


            <div class="settings-box">


                <p>

                    <strong>Business:</strong>

                    Emirates Butchery

                </p>


                <p>

                    <strong>Admin Panel:</strong>

                    Active

                </p>


                <p>

                    <strong>Database:</strong>

                    Connected

                </p>


                <p>

                    <strong>Total Customers:</strong>

                    <?php echo $totalCustomers; ?>

                </p>


                <p>

                    <strong>Total Orders:</strong>

                    <?php echo $totalOrders; ?>

                </p>


                <p>

                    Manage your business information and meat prices from the Settings page.

                </p>


                <!-- IMPORTANT:
                     Opens the separate settings.php page.
                -->

                <a href="settings.php" class="view-all">

                    ⚙️ Open Settings

                </a>


            </div>


        </section>


    </main>


</div>


</body>

</html>