<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Billing System</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Optional custom style -->
    <style>
        body { background: #f3f4f6; }
        .sidebar {
            min-height: 100vh;
            background: #1f2937;
        }
        .sidebar a {
            color: #d1d5db;
            display: block;
            padding: 12px;
            text-decoration: none;
        }
        .sidebar a:hover {
            background: #374151;
            color: #fff;
        }
        .content-area {
            padding: 20px;
        }
    </style>
</head>
<body>