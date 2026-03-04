<?php
include "../config/db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Access denied");
}

/* Fetch customers */
$customers = mysqli_query($conn, "SELECT user_id, name FROM users WHERE role='customer'");

$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : "";

$total_debit = 0;
$total_credit = 0;
$current_balance = 0;
$result = null;

if ($user_id) {

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
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Customer Ledger</title>
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
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="dashboard">
    <h2>View Customer Ledger</h2>

    <form method="get">
        <select name="user_id" required>
            <option value="">Select Customer</option>
            <?php while($c = mysqli_fetch_assoc($customers)) { ?>
                <option value="<?= $c['user_id']; ?>"
                    <?= ($user_id == $c['user_id']) ? 'selected' : '' ?>>
                    <?= $c['name']; ?>
                </option>
            <?php } ?>
        </select>
        <button type="submit">View</button>
    </form>

    <?php if ($user_id) { ?>

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

    <?php } ?>

    <br>
    <a href="dashboard.php" class="nav-btn">Back</a>
</div>

</body>
</html>