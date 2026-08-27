<?php

session_start();

// Clear all session variables
$_SESSION = [];

// Destroy the admin session
session_destroy();

// Prevent browser from caching the admin page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Redirect to Emirates Butchery main website
header("Location: ../emirate.html");
exit();

?>