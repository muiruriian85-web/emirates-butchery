<?php

session_start();

/* Make sure admin is logged in */
if (!isset($_SESSION["admin_logged_in"])) {
    header("Location: login.php");
    exit;
}

/* Connect to database */
require_once "../database.php";

/* Make sure the required information was submitted */
if (
    !isset($_POST["order_id"]) ||
    !isset($_POST["order_status"])
) {
    header("Location: dashboard.php");
    exit;
}

$order_id = intval($_POST["order_id"]);
$order_status = $_POST["order_status"];

/* Allowed order statuses */
$allowed_statuses = [
    "Pending",
    "Confirmed",
    "Preparing",
    "Out for Delivery",
    "Delivered",
    "Cancelled"
];

/* Check that the selected status is valid */
if (!in_array($order_status, $allowed_statuses)) {
    die("Invalid order status.");
}

/* Update the order */
$sql = "
    UPDATE orders
    SET order_status = ?
    WHERE id = ?
";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "si",
    $order_status,
    $order_id
);

if ($stmt->execute()) {

    /* Return to dashboard */
    header("Location: dashboard.php");
    exit;

} else {

    echo "Error updating order: " .
         $conn->error;
}

$stmt->close();
$conn->close();

?>