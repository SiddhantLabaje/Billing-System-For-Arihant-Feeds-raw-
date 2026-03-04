<?php
include "../config/db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Access denied");
}

$message = "";

/* Fetch customers */
$customers = mysqli_query($conn, "SELECT user_id, name FROM users WHERE role='customer'");

/* Fetch feeds */
$feeds = mysqli_query($conn, "SELECT feed_id, feed_name FROM feed");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user_id = $_POST['user_id'];
    $feed_id = $_POST['feed_id'] ? $_POST['feed_id'] : "NULL";
    $date = $_POST['date'];
    $description = $_POST['description'];
    $debit = $_POST['debit'] ? $_POST['debit'] : 0;
    $credit = $_POST['credit'] ? $_POST['credit'] : 0;

    if (empty($user_id) || empty($date)) {
        $message = "Customer and date required";
    } else {

        /* Get last balance */
        $last = mysqli_query($conn,
            "SELECT balance FROM ledger 
             WHERE user_id=$user_id 
             ORDER BY ledger_id DESC LIMIT 1"
        );

        if (mysqli_num_rows($last) > 0) {
            $row = mysqli_fetch_assoc($last);
            $last_balance = $row['balance'];
        } else {
            $last_balance = 0;
        }

        $new_balance = $last_balance + $debit - $credit;

        $insert = mysqli_query($conn,
            "INSERT INTO ledger 
            (user_id, feed_id, transaction_date, description, debit, credit, balance)
            VALUES 
            ($user_id, $feed_id, '$date', '$description', $debit, $credit, $new_balance)"
        );

        if ($insert) {
            $message = "Ledger entry added successfully";
        } else {
            $message = "Something went wrong";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Ledger Entry</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="dashboard">
    <h2>Add Ledger Entry</h2>

    <?php if ($message) echo "<p style='margin-bottom:15px; font-weight:bold;'>$message</p>"; ?>

    <form method="post">

        <select name="user_id" required>
            <option value="">Select Customer</option>
            <?php while($c = mysqli_fetch_assoc($customers)) { ?>
                <option value="<?= $c['user_id']; ?>">
                    <?= $c['name']; ?>
                </option>
            <?php } ?>
        </select>

        <select name="feed_id">
            <option value="">Select Feed (optional)</option>
            <?php while($f = mysqli_fetch_assoc($feeds)) { ?>
                <option value="<?= $f['feed_id']; ?>">
                    <?= $f['feed_name']; ?>
                </option>
            <?php } ?>
        </select>

        <input type="date" name="date" required>

        <input type="text" name="description" placeholder="Description">

        <input type="number" step="0.01" name="debit" placeholder="Debit (Purchase)">

        <input type="number" step="0.01" name="credit" placeholder="Credit (Payment)">

        <button type="submit">Save Entry</button>
    </form>

    <br>
    <a href="dashboard.php" class="nav-btn">Back</a>
</div>

</body>
</html>