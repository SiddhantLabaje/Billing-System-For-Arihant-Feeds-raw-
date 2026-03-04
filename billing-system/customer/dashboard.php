<?php
include "../config/db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'customer') {
    die("Access denied");
}

$user_id = $_SESSION['user_id'];

/* Get totals */
$summary = mysqli_query($conn,
    "SELECT 
        IFNULL(SUM(debit),0) as total_debit,
        IFNULL(SUM(credit),0) as total_credit
     FROM ledger
     WHERE user_id = $user_id"
);

$data = mysqli_fetch_assoc($summary);

$total_debit = $data['total_debit'];
$total_credit = $data['total_credit'];
$current_balance = $total_debit - $total_credit;

/* Get Purchases (Debit entries) */
$purchases = mysqli_query($conn,
    "SELECT l.*, f.feed_name
     FROM ledger l
     LEFT JOIN feed f ON l.feed_id = f.feed_id
     WHERE l.user_id = $user_id AND l.debit > 0
     ORDER BY l.transaction_date DESC"
);

/* Get Payments (Credit entries) */
$payments = mysqli_query($conn,
    "SELECT *
     FROM ledger
     WHERE user_id = $user_id AND credit > 0
     ORDER BY transaction_date DESC"
);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Customer Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .container {
            max-width: 1000px;
            margin: 30px auto;
        }
        .cards {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        .card {
            flex: 1;
            padding: 15px;
            border-radius: 8px;
            color: white;
            text-align: center;
            font-weight: bold;
        }
        .balance { background: #dc2626; }
        .purchase { background: #4f46e5; }
        .paid { background: #16a34a; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: center;
        }
        th {
            background: #4f46e5;
            color: white;
        }
        h3 {
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="container">

    <h2>Welcome <?= $_SESSION['name']; ?></h2>

    <!-- Summary Cards -->
    <div class="cards">
        <div class="card balance">
            Current Balance<br>
            ₹<?= $current_balance ?>
        </div>

        <div class="card purchase">
            Total Purchase<br>
            ₹<?= $total_debit ?>
        </div>

        <div class="card paid">
            Total Paid<br>
            ₹<?= $total_credit ?>
        </div>
    </div>

    <!-- Feed Purchases -->
    <h3>Feed Purchases</h3>
    <table>
        <tr>
            <th>Date</th>
            <th>Feed</th>
            <th>Description</th>
            <th>Amount</th>
        </tr>

        <?php while ($row = mysqli_fetch_assoc($purchases)) { ?>
        <tr>
            <td><?= $row['transaction_date'] ?></td>
            <td><?= $row['feed_name'] ?? '-' ?></td>
            <td><?= $row['description'] ?></td>
            <td>₹<?= $row['debit'] ?></td>
        </tr>
        <?php } ?>
    </table>

    <!-- Payments -->
    <h3>Payments</h3>
    <table>
        <tr>
            <th>Date</th>
            <th>Description</th>
            <th>Amount Paid</th>
        </tr>

        <?php while ($row = mysqli_fetch_assoc($payments)) { ?>
        <tr>
            <td><?= $row['transaction_date'] ?></td>
            <td><?= $row['description'] ?></td>
            <td>₹<?= $row['credit'] ?></td>
        </tr>
        <?php } ?>
    </table>

    <a href="../logout.php">Logout</a>

</div>

</body>
</html>