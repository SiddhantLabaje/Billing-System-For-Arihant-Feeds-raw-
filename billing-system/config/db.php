<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "billing_system";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Database connection failed");
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>