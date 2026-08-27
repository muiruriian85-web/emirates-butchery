<?php

session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

require_once "../database.php";


/* =====================================================
   PRICES PER KG
===================================================== */

$prices = [

    "Premium Beef" => 650,

    "Beef Steak" => 900,

    "Fresh Chicken" => 550,

    "Goat Meat" => 800

];


/* =====================================================
   SALES VARIABLES
===================================================== */

$totalSales = 0;

$totalItemsSold = 0;

$totalOrders = 0;


/* =====================================================
   SALES BY MEAT
===================================================== */

$beefSales = 0;

$steakSales = 0;

$chickenSales = 0;

$goatSales = 0;


/* =====================================================
   GET ORDERS
===================================================== */

$sql = "

    SELECT *

    FROM orders

    WHERE order_status != 'Cancelled'

    ORDER BY created_at DESC

";


$result = $conn->query($sql);


/* =====================================================
   SALES RECORDS
===================================================== */

$sales = [];


if ($result) {


    while ($order = $result->fetch_assoc()) {


        $meat = $order['meat_type'];


        $quantity = (float)$order['quantity'];


        /* Make sure the meat exists in our price list */

        if (!isset($prices[$meat])) {

            continue;

        }


        $pricePerKg = $prices[$meat];


        $amount = $pricePerKg * $quantity;


        /* =================================================
           COUNT ONLY ACTUAL SALES

           Pending orders are not counted as completed sales.
        ================================================= */

        if (
            $order['order_status'] === "Confirmed" ||
            $order['order_status'] === "Preparing" ||
            $order['order_status'] === "Out for Delivery" ||
            $order['order_status'] === "Delivered"
        ) {


            $totalSales += $amount;


            $totalItemsSold += $quantity;


            $totalOrders++;


            /* Meat sales */

            if ($meat === "Premium Beef") {

                $beefSales += $amount;

            }


            if ($meat === "Beef Steak") {

                $steakSales += $amount;

            }


            if ($meat === "Fresh Chicken") {

                $chickenSales += $amount;

            }


            if ($meat === "Goat Meat") {

                $goatSales += $amount;

            }


        }


        /* Add order to table */

        $sales[] = [

            "id" => $order['id'],

            "customer" => $order['customer_name'],

            "meat" => $meat,

            "quantity" => $quantity,

            "price" => $pricePerKg,

            "amount" => $amount,

            "status" => $order['order_status'],

            "date" => $order['created_at']

        ];

    }

}

?>


<!DOCTYPE html>

<html lang="en">


<head>


<meta charset="UTF-8">


<meta name="viewport" content="width=device-width, initial-scale=1.0">


<title>Sales | Emirates Butchery</title>


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


/* =====================================================
   SIDEBAR
===================================================== */


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


/* =====================================================
   MAIN
===================================================== */


.main {

    margin-left: 240px;

    padding: 30px;

}


/* =====================================================
   TOP BAR
===================================================== */


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


/* =====================================================
   SUMMARY CARDS
===================================================== */


.summary {

    display: grid;

    grid-template-columns: repeat(3, 1fr);

    gap: 20px;

    margin-bottom: 25px;

}


.summary-card {

    background: white;

    padding: 25px;

    border-radius: 10px;

    box-shadow: 0 3px 12px rgba(0,0,0,.08);

}


.summary-card h3 {

    color: #666;

    font-size: 15px;

    margin-bottom: 10px;

}


.summary-number {

    font-size: 30px;

    font-weight: bold;

}


.sales-card {

    border-left: 5px solid #e63946;

}


.orders-card {

    border-left: 5px solid #4285f4;

}


.items-card {

    border-left: 5px solid #28a745;

}


/* =====================================================
   MEAT SALES
===================================================== */


.meat-sales {

    display: grid;

    grid-template-columns: repeat(4, 1fr);

    gap: 15px;

    margin-bottom: 25px;

}


.meat-card {

    background: white;

    padding: 20px;

    border-radius: 10px;

    box-shadow: 0 3px 12px rgba(0,0,0,.08);

}


.meat-card h3 {

    font-size: 15px;

    margin-bottom: 8px;

    color: #555;

}


.meat-card p {

    font-size: 22px;

    font-weight: bold;

}


/* =====================================================
   SALES TABLE
===================================================== */


.sales-section {

    background: white;

    padding: 25px;

    border-radius: 10px;

    box-shadow: 0 3px 12px rgba(0,0,0,.08);

}


.sales-section h2 {

    margin-bottom: 20px;

}


.table-wrapper {

    overflow-x: auto;

}


table {

    width: 100%;

    border-collapse: collapse;

    min-width: 900px;

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

}


tr:hover {

    background: #fafafa;

}


/* =====================================================
   STATUS
===================================================== */


.status {

    display: inline-block;

    padding: 6px 10px;

    border-radius: 20px;

    font-size: 12px;

    font-weight: bold;

}


.status-Pending {

    background: #fff3cd;

    color: #856404;

}


.status-Confirmed {

    background: #dbeafe;

    color: #1d4ed8;

}


.status-Preparing {

    background: #ffe0b2;

    color: #e65100;

}


.status-Out {

    background: #eadcf8;

    color: #7b1fa2;

}


.status-Delivered {

    background: #d4edda;

    color: #155724;

}


.status-Cancelled {

    background: #f8d7da;

    color: #721c24;

}


/* =====================================================
   MOBILE
===================================================== */


@media(max-width: 1000px) {

    .summary {

        grid-template-columns: 1fr;

    }


    .meat-sales {

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


    .topbar {

        flex-direction: column;

        align-items: flex-start;

        gap: 15px;

    }


    .meat-sales {

        grid-template-columns: 1fr;

    }

}


</style>


</head>


<body>


<!-- =====================================================
     SIDEBAR
===================================================== -->


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


    <a href="customers.php">

        👥 Customers

    </a>


    <a href="sales.php" class="active">

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



<!-- =====================================================
     MAIN CONTENT
===================================================== -->


<div class="main">


    <div class="topbar">


        <h1>

            📊 Sales

        </h1>


        <a href="logout.php" class="logout">

            Logout

        </a>


    </div>



    <!-- =================================================
         SUMMARY
    ================================================= -->


    <div class="summary">


        <div class="summary-card sales-card">


            <h3>

                Total Sales

            </h3>


            <div class="summary-number">

                KSh <?php echo number_format($totalSales); ?>

            </div>


        </div>



        <div class="summary-card orders-card">


            <h3>

                Sales Orders

            </h3>


            <div class="summary-number">

                <?php echo $totalOrders; ?>

            </div>


        </div>



        <div class="summary-card items-card">


            <h3>

                Total Meat Sold

            </h3>


            <div class="summary-number">

                <?php echo number_format($totalItemsSold, 2); ?> kg

            </div>


        </div>


    </div>



    <!-- =================================================
         SALES BY PRODUCT
    ================================================= -->


    <div class="meat-sales">


        <div class="meat-card">

            <h3>

                🥩 Premium Beef

            </h3>

            <p>

                KSh <?php echo number_format($beefSales); ?>

            </p>

        </div>



        <div class="meat-card">

            <h3>

                🥩 Beef Steak

            </h3>

            <p>

                KSh <?php echo number_format($steakSales); ?>

            </p>

        </div>



        <div class="meat-card">

            <h3>

                🍗 Fresh Chicken

            </h3>

            <p>

                KSh <?php echo number_format($chickenSales); ?>

            </p>

        </div>



        <div class="meat-card">

            <h3>

                🐐 Goat Meat

            </h3>

            <p>

                KSh <?php echo number_format($goatSales); ?>

            </p>

        </div>


    </div>



    <!-- =================================================
         SALES HISTORY
    ================================================= -->


    <div class="sales-section">


        <h2>

            Sales History

        </h2>


        <div class="table-wrapper">


            <table>


                <thead>


                    <tr>

                        <th>Order</th>

                        <th>Customer</th>

                        <th>Meat</th>

                        <th>Quantity</th>

                        <th>Price / Kg</th>

                        <th>Total</th>

                        <th>Status</th>

                        <th>Date</th>

                    </tr>


                </thead>



                <tbody>


                <?php if (count($sales) > 0): ?>


                    <?php foreach ($sales as $sale): ?>


                        <tr>


                            <td>

                                <strong>

                                    #<?php echo $sale['id']; ?>

                                </strong>

                            </td>


                            <td>

                                <?php echo htmlspecialchars($sale['customer']); ?>

                            </td>


                            <td>

                                <?php echo htmlspecialchars($sale['meat']); ?>

                            </td>


                            <td>

                                <?php echo $sale['quantity']; ?> kg

                            </td>


                            <td>

                                KSh <?php echo number_format($sale['price']); ?>

                            </td>


                            <td>

                                <strong>

                                    KSh <?php echo number_format($sale['amount']); ?>

                                </strong>

                            </td>


                            <td>


                                <?php

                                $statusClass = "status-Pending";


                                if ($sale['status'] === "Confirmed") {

                                    $statusClass = "status-Confirmed";

                                }


                                if ($sale['status'] === "Preparing") {

                                    $statusClass = "status-Preparing";

                                }


                                if ($sale['status'] === "Out for Delivery") {

                                    $statusClass = "status-Out";

                                }


                                if ($sale['status'] === "Delivered") {

                                    $statusClass = "status-Delivered";

                                }


                                if ($sale['status'] === "Cancelled") {

                                    $statusClass = "status-Cancelled";

                                }

                                ?>


                                <span class="status <?php echo $statusClass; ?>">

                                    <?php echo htmlspecialchars($sale['status']); ?>

                                </span>


                            </td>


                            <td>

                                <?php echo htmlspecialchars($sale['date']); ?>

                            </td>


                        </tr>


                    <?php endforeach; ?>


                <?php else: ?>


                    <tr>


                        <td colspan="8" style="text-align:center;padding:30px;">

                            No sales found.

                        </td>


                    </tr>


                <?php endif; ?>


                </tbody>


            </table>


        </div>


    </div>


</div>


</body>


</html>