<?php
include "../config/db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Access denied");
}

$message = "";

/* Fetch customers */
$customers = mysqli_query($conn, 
    "SELECT user_id, name FROM users WHERE role='customer'"
);

/* Fetch feeds */
$feeds = mysqli_query($conn, 
    "SELECT feed_id, feed_name, price FROM feed"
);

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user_id = $_POST['user_id'];
    $feed_id = $_POST['feed_id'];
    $quantity = $_POST['quantity'];
    $date = $_POST['date'];

    if (empty($user_id) || empty($feed_id) || empty($quantity)) {
        $message = "All fields required";
    } else {

        /* Get feed price */
        $feed_data = mysqli_fetch_assoc(
            mysqli_query($conn,
                "SELECT price, feed_name FROM feed WHERE feed_id=$feed_id"
            )
        );

        $price = $feed_data['price'];
        $feed_name = $feed_data['feed_name'];

        $total = $price * $quantity;

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

        $new_balance = $last_balance + $total;

        /* Insert ledger */
        mysqli_query($conn,
            "INSERT INTO ledger
            (user_id, feed_id, transaction_date, description, debit, credit, balance)
            VALUES
            ($user_id, $feed_id, '$date',
             'Feed Purchase ($quantity bags)',
             $total, 0, $new_balance)"
        );

        $message = "Purchase entry added successfully";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Purchase</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="dashboard">
    <h2>Add Feed Purchase</h2>

    <?php if ($message) echo "<p><strong>$message</strong></p>"; ?>

    <form method="post">

        <select name="user_id" required>
            <option value="">Select Customer</option>
            <?php while($c = mysqli_fetch_assoc($customers)) { ?>
                <option value="<?= $c['user_id']; ?>">
                    <?= $c['name']; ?>
                </option>
            <?php } ?>
        </select>

        <select name="feed_id" required>
            <option value="">Select Feed</option>
            <?php while($f = mysqli_fetch_assoc($feeds)) { ?>
                <option value="<?= $f['feed_id']; ?>">
                    <?= $f['feed_name']; ?> (₹<?= $f['price']; ?>)
                </option>
            <?php } ?>
        </select>

        <input type="number" name="quantity" placeholder="Quantity (bags)" required>

        <input type="date" name="date" required>

        <button type="submit">Save Purchase</button>
    </form>

    <br>
    <a href="dashboard.php" class="nav-btn">Back</a>
</div>

</body>
</html>