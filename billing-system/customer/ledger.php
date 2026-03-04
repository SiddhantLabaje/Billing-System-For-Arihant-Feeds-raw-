<?php
include "../config/db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'customer') {
    die("Access denied");
}

$user_id = $_SESSION['user_id'];

/* Summary */
$summary = mysqli_query($conn,
    "SELECT 
        SUM(debit) as total_debit,
        SUM(credit) as total_credit
     FROM ledger
     WHERE user_id = $user_id"
);

$data = mysqli_fetch_assoc($summary);

$total_debit = $data['total_debit'] ? $data['total_debit'] : 0;
$total_credit = $data['total_credit'] ? $data['total_credit'] : 0;
$current_balance = $total_debit - $total_credit;

/* Ledger entries */
$result = mysqli_query($conn,
    "SELECT l.*, f.feed_name
     FROM ledger l
     LEFT JOIN feed f ON l.feed_id = f.feed_id
     WHERE l.user_id = $user_id
     ORDER BY l.transaction_date ASC, l.ledger_id ASC"
);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Ledger</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }
        th {
            background: #4f46e5;
            color: white;
        }
        .summary-box {
            background: #f3f4f6;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="dashboard">
    <h2>My Ledger</h2>

    <div class="summary-box">
        <strong>Total Purchase:</strong> ₹<?= $total_debit ?><br>
        <strong>Total Paid:</strong> ₹<?= $total_credit ?><br>
        <strong>Current Balance:</strong> ₹<?= $current_balance ?>
    </div>

    <table>
        <tr>
            <th>Date</th>
            <th>Feed</th>
            <th>Description</th>
            <th>Debit</th>
            <th>Credit</th>
            <th>Balance</th>
        </tr>

        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
        <tr>
            <td><?= $row['transaction_date'] ?></td>
            <td><?= $row['feed_name'] ? $row['feed_name'] : '-' ?></td>
            <td><?= $row['description'] ?></td>
            <td><?= $row['debit'] ?></td>
            <td><?= $row['credit'] ?></td>
            <td><?= $row['balance'] ?></td>
        </tr>
        <?php } ?>

    </table>

    <br>
    <a href="dashboard.php" class="nav-btn">Back</a>
</div>

</body>
</html>