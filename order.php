<?php

// ==========================================
// EMIRATES BUTCHERY ORDER SYSTEM
// ==========================================


// DATABASE DETAILS

$host = "localhost";
$username = "root";
$password = "";
$database = "emirates_butchery";


// CONNECT TO MYSQL

$conn = new mysqli(
    $host,
    $username,
    $password,
    $database
);


// CHECK CONNECTION

if ($conn->connect_error) {

    die(
        "Database connection failed: " .
        $conn->connect_error
    );

}


// MAKE SURE REQUEST IS POST

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    die("Invalid request.");

}


// GET FORM INFORMATION

$customerName =
    trim($_POST["customerName"] ?? "");

$email =
    trim($_POST["email"] ?? "");

$phone =
    trim($_POST["phone"] ?? "");

$meatType =
    trim($_POST["meatType"] ?? "");

$quantity =
    intval($_POST["quantity"] ?? 0);

$location =
    trim($_POST["location"] ?? "");

$instructions =
    trim($_POST["instructions"] ?? "");


// VALIDATE INFORMATION

if (
    empty($customerName) ||
    empty($email) ||
    empty($phone) ||
    empty($meatType) ||
    $quantity <= 0 ||
    empty($location)
) {

    die("Please fill in all required fields.");

}


// VALIDATE EMAIL

if (
    !filter_var(
        $email,
        FILTER_VALIDATE_EMAIL
    )
) {

    die("Please enter a valid email address.");

}


// PREPARE DATABASE QUERY

$sql = "
    INSERT INTO orders
    (
        customer_name,
        email,
        phone,
        meat_type,
        quantity,
        delivery_location,
        instructions
    )
    VALUES (?, ?, ?, ?, ?, ?, ?)
";


$stmt = $conn->prepare($sql);


if (!$stmt) {

    die(
        "Database error: " .
        $conn->error
    );

}


// CONNECT VALUES TO QUERY

$stmt->bind_param(
    "ssssiss",
    $customerName,
    $email,
    $phone,
    $meatType,
    $quantity,
    $location,
    $instructions
);


// SAVE ORDER

if ($stmt->execute()) {

    ?>

    <!DOCTYPE html>

    <html lang="en">

    <head>

        <meta charset="UTF-8">

        <meta
            name="viewport"
            content="width=device-width, initial-scale=1.0">

        <title>
            Order Successful | Emirates Butchery
        </title>

        <style>

            body {

                margin: 0;

                font-family: Arial, sans-serif;

                background: #f5f5f5;

                min-height: 100vh;

                display: flex;

                justify-content: center;

                align-items: center;

            }


            .success-box {

                width: 90%;

                max-width: 500px;

                background: white;

                padding: 40px;

                text-align: center;

                border-radius: 12px;

                box-shadow:
                    0 5px 25px
                    rgba(0,0,0,.15);

            }


            .success-icon {

                font-size: 60px;

            }


            h1 {

                color: #e63946;

            }


            p {

                color: #555;

                line-height: 1.6;

            }


            .back-button {

                display: inline-block;

                margin-top: 20px;

                padding: 14px 25px;

                background: #111;

                color: white;

                text-decoration: none;

                border-radius: 6px;

                font-weight: bold;

            }


            .back-button:hover {

                background: #e63946;

            }

        </style>

    </head>


    <body>

        <div class="success-box">

            <div class="success-icon">
                ✅
            </div>

            <h1>
                Order Received!
            </h1>

            <p>

                Thank you,
                <strong>
                    <?php
                    echo htmlspecialchars(
                        $customerName
                    );
                    ?>
                </strong>.

            </p>

            <p>
                Your order has been successfully
                received by Emirates Butchery.
            </p>

            <p>

                We will contact you on

                <strong>
                    <?php
                    echo htmlspecialchars(
                        $phone
                    );
                    ?>
                </strong>

                to confirm your order.

            </p>

            <a
                href="index.html"
                class="back-button">

                Back to Website

            </a>

        </div>

    </body>

    </html>

    <?php

} else {

    die(
        "Unable to save order. Please try again."
    );

}


// CLOSE DATABASE

$stmt->close();

$conn->close();

?>