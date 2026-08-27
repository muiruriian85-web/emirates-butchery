<?php

session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

require_once "../database.php";

/* Get customers from existing orders table */

$sql = "
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
";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Customers | Emirates Butchery</title>

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
    padding: 25px 15px;
}

.logo {
    font-size: 22px;
    font-weight: bold;
    padding: 10px;
    margin-bottom: 30px;
}

.logo span {
    color: #e63946;
}

.admin-title {
    color: #aaa;
    font-size: 12px;
    margin: 0 10px 10px;
    text-transform: uppercase;
}

.sidebar a {
    display: block;
    color: white;
    text-decoration: none;
    padding: 13px 15px;
    margin-bottom: 5px;
    border-radius: 6px;
}

.sidebar a:hover {
    background: #e63946;
}

.sidebar a.active {
    background: #e63946;
}

/* MAIN */

.main {
    margin-left: 240px;
    padding: 30px;
}

.topbar {
    background: white;
    padding: 20px 25px;
    border-radius: 10px;
    margin-bottom: 25px;
    box-shadow: 0 3px 12px rgba(0,0,0,.08);

    display: flex;
    justify-content: space-between;
    align-items: center;
}

.topbar h1 {
    font-size: 28px;
}

.logout {
    background: #e63946;
    color: white;
    padding: 10px 18px;
    text-decoration: none;
    border-radius: 6px;
    font-weight: bold;
}

/* CUSTOMER CARDS */

.customer-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

.customer-card {
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 3px 12px rgba(0,0,0,.08);
}

.customer-card h2 {
    margin-bottom: 15px;
    color: #222;
}

.customer-info {
    margin-bottom: 10px;
    color: #555;
}

.customer-info strong {
    color: #222;
}

.orders-count {
    margin-top: 15px;
    background: #f4f5f7;
    padding: 10px;
    border-radius: 6px;
    font-weight: bold;
}

.last-order {
    margin-top: 10px;
    color: #777;
    font-size: 13px;
}

/* BUTTONS */

.actions {
    margin-top: 15px;
}

.action-btn {
    display: inline-block;
    padding: 8px 12px;
    border-radius: 5px;
    text-decoration: none;
    color: white;
    font-size: 13px;
    margin-right: 5px;
}

.call {
    background: #333;
}

.whatsapp {
    background: #25D366;
}

/* MOBILE */

@media(max-width: 1000px) {

    .customer-grid {
        grid-template-columns: repeat(2, 1fr);
    }

}

@media(max-width: 700px) {

    .sidebar {
        position: relative;
        width: 100%;
        height: auto;
    }

    .main {
        margin-left: 0;
        padding: 15px;
    }

    .customer-grid {
        grid-template-columns: 1fr;
    }

    .topbar {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }

}

</style>

</head>

<body>

<!-- SIDEBAR -->

<div class="sidebar">

    <div class="logo">
        Emirates<span> Butchery</span>
    </div>

    <div class="admin-title">
        Admin Panel
    </div>

    <a href="dashboard.php">
        🏠 Dashboard
    </a>

    <a href="dashboard.php#orders">
        📦 Orders
    </a>

    <a href="customers.php" class="active">
        👥 Customers
    </a>

    <a href="dashboard.php#sales">
        📊 Sales
    </a>

    <a href="dashboard.php#settings">
        ⚙️ Settings
    </a>

    <br>

    <a href="logout.php">
        🚪 Logout
    </a>

</div>


<!-- MAIN CONTENT -->

<div class="main">

    <div class="topbar">

        <h1>
            👥 Customers
        </h1>

        <a href="logout.php" class="logout">
            Logout
        </a>

    </div>


    <div class="customer-grid">

        <?php if ($result && $result->num_rows > 0): ?>

            <?php while ($customer = $result->fetch_assoc()): ?>

                <div class="customer-card">

                    <h2>
                        👤
                        <?php echo htmlspecialchars($customer['customer_name']); ?>
                    </h2>

                    <div class="customer-info">

                        <strong>Email:</strong><br>

                        <?php echo htmlspecialchars($customer['email']); ?>

                    </div>


                    <div class="customer-info">

                        <strong>Phone:</strong><br>

                        <?php echo htmlspecialchars($customer['phone']); ?>

                    </div>


                    <div class="customer-info">

                        <strong>Delivery Location:</strong><br>

                        <?php echo htmlspecialchars($customer['delivery_location']); ?>

                    </div>


                    <div class="orders-count">

                        📦 Total Orders:
                        <?php echo $customer['total_orders']; ?>

                    </div>


                    <div class="last-order">

                        Last Order:
                        <?php echo htmlspecialchars($customer['last_order']); ?>

                    </div>


                    <div class="actions">

                        <a
                            class="action-btn call"
                            href="tel:<?php echo htmlspecialchars($customer['phone']); ?>"
                        >
                            📞 Call
                        </a>


                        <a
                            class="action-btn whatsapp"
                            target="_blank"
                            href="https://wa.me/<?php echo htmlspecialchars($customer['phone']); ?>"
                        >
                            💬 WhatsApp
                        </a>

                    </div>

                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <div class="customer-card">

                <h2>No Customers Found</h2>

                <p>
                    Customers will appear here when orders are placed.
                </p>

            </div>

        <?php endif; ?>

    </div>

</div>

</body>

</html>