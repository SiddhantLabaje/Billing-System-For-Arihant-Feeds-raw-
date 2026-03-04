<?php
include "../config/db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Access denied");
}

$customers = mysqli_query($conn,
    "SELECT user_id, name FROM users WHERE role='customer'"
);

$user_id = "";
$from = "";
$to = "";
$ledger = null;
$opening_balance = 0;

if (isset($_GET['user_id'])) {

    $user_id = intval($_GET['user_id']);
    /* Get customer details */
$userData = mysqli_fetch_assoc(
    mysqli_query($conn,
        "SELECT name, phone FROM users WHERE user_id=$user_id"
    )
);

$customer_name = $userData['name'];
$customer_phone = $userData['phone'];
    $from = $_GET['from'];
    $to = $_GET['to'];

    /* Opening balance before selected date */
    $openQuery = mysqli_query($conn,
        "SELECT 
            IFNULL(SUM(debit),0) as debit,
            IFNULL(SUM(credit),0) as credit
         FROM ledger
         WHERE user_id=$user_id
         AND transaction_date < '$from'"
    );

    $openData = mysqli_fetch_assoc($openQuery);
    $opening_balance = $openData['debit'] - $openData['credit'];

    /* Get ledger between dates */
    $ledger = mysqli_query($conn,
        "SELECT * FROM ledger
         WHERE user_id=$user_id
         AND transaction_date BETWEEN '$from' AND '$to'
         ORDER BY transaction_date ASC"
    );
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ledger Report</title>
    <style>
        body { font-family: Arial; padding:20px; }
        table { width:100%; border-collapse: collapse; margin-top:20px; }
        th, td { border:1px solid #ddd; padding:8px; text-align:center; }
        th { background:#4f46e5; color:white; }
        .header { margin-bottom:20px; }
        .print-btn { margin-top:20px; padding:8px 15px; }
        @media print {
            .no-print { display:none; }
        }
    </style>
</head>
<body>

<h2>Ledger Report (Taleband)</h2>

<form method="get" class="no-print">

    <select name="user_id" required>
        <option value="">Select Customer</option>
        <?php while($c = mysqli_fetch_assoc($customers)) { ?>
            <option value="<?= $c['user_id']; ?>">
                <?= $c['name']; ?>
            </option>
        <?php } ?>
    </select>

    From:
    <input type="date" name="from" required>

    To:
    <input type="date" name="to" required>

    <button type="submit">View</button>
</form>

<?php if ($ledger) { ?>

<hr>

<h3>
ARIHANT FEEDS<br><br>

Customer Name: <?= $customer_name ?><br>
Mobile: <?= $customer_phone ?><br>
Period: <?= $from ?> to <?= $to ?>
</h3>

<table>
<tr>
    <th>Date</th>
    <th>Description</th>
    <th>Debit</th>
    <th>Credit</th>
    <th>Balance</th>
</tr>

<tr>
    <td colspan="4"><strong>Opening Balance</strong></td>
    <td><strong>₹<?= $opening_balance ?></strong></td>
</tr>

<?php
$running_balance = $opening_balance;

while($row = mysqli_fetch_assoc($ledger)) {

    $running_balance += $row['debit'];
    $running_balance -= $row['credit'];
?>

<tr>
    <td><?= $row['transaction_date'] ?></td>
    <td><?= $row['description'] ?></td>
    <td><?= $row['debit'] ?></td>
    <td><?= $row['credit'] ?></td>
    <td><?= $running_balance ?></td>
</tr>

<?php } ?>

</table>

<button class="print-btn no-print" onclick="window.print()">Print</button>

<?php } ?>

<br>
<a href="dashboard.php" class="no-print">Back to Dashboard</a>

</body>
</html>