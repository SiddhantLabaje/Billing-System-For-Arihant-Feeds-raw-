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

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user_id = intval($_POST['user_id']);
    $amount = floatval($_POST['amount']);
    $mode = $_POST['payment_mode'];
    $date = $_POST['date'];

    if ($user_id <= 0 || $amount <= 0 || empty($mode) || empty($date)) {
        $message = "All fields are required.";
    } else {

        /* Get total balance using SUM */
        $balanceQuery = mysqli_query($conn,
            "SELECT 
                IFNULL(SUM(debit),0) as total_debit,
                IFNULL(SUM(credit),0) as total_credit
             FROM ledger 
             WHERE user_id=$user_id"
        );

        $balData = mysqli_fetch_assoc($balanceQuery);
        $current_balance = $balData['total_debit'] - $balData['total_credit'];

        if ($amount > $current_balance) {
            $message = "Payment cannot exceed current balance (₹$current_balance)";
        } else {

            $new_balance = $current_balance - $amount;

            /* Insert payment into ledger */
            mysqli_query($conn,
                "INSERT INTO ledger
                (user_id, transaction_date, description, debit, credit, balance, payment_mode)
                VALUES
                ($user_id, '$date',
                 'Payment Received ($mode)',
                 0, $amount, $new_balance, '$mode')"
            );

            /* Get customer details */
            $user = mysqli_fetch_assoc(
                mysqli_query($conn,
                    "SELECT phone, name FROM users WHERE user_id=$user_id"
                )
            );

            $phone = $user['phone'];
            $name = $user['name'];

            /* Prepare WhatsApp message */
            $messageText = "Hello $name,\n\n".
               "Payment Received Successfully!!!\n\n".
               "Amount: ₹$amount\n".
               "Payment Mode: $mode\n".
               "Date: $date\n\n".
               "Remaining Balance: ₹$new_balance\n\n".
               "Thank you for your payment.\n".
               "Arihant Feeds";

            $encodedMessage = urlencode($messageText);

            /* Redirect to WhatsApp */
            header("Location: https://wa.me/91$phone?text=$encodedMessage");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Payment</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { font-family: Arial; background: #f3f4f6; }
        .form-box {
            max-width: 400px;
            margin: 50px auto;
            padding: 25px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-box h2 { text-align: center; margin-bottom: 20px; }
        .form-box input,
        .form-box select {
            width: 100%;
            padding: 8px;
            margin-bottom: 12px;
        }
        .form-box button {
            width: 100%;
            padding: 10px;
            background: #4f46e5;
            color: white;
            border: none;
            cursor: pointer;
        }
        .form-box button:hover {
            background: #4338ca;
        }
        .message {
            text-align: center;
            margin-bottom: 15px;
            color: red;
            font-weight: bold;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 15px;
        }
    </style>
</head>
<body>

<div class="form-box">
    <h2>Add Payment</h2>

    <?php if ($message) { ?>
        <div class="message"><?= $message ?></div>
    <?php } ?>

    <form method="post">

        <select name="user_id" required>
            <option value="">Select Customer</option>
            <?php while($c = mysqli_fetch_assoc($customers)) { ?>
                <option value="<?= $c['user_id']; ?>">
                    <?= $c['name']; ?>
                </option>
            <?php } ?>
        </select>

        <input type="number" step="0.01" name="amount" placeholder="Payment Amount" required>

        <select name="payment_mode" required>
            <option value="">Select Payment Mode</option>
            <option value="Cash">Cash</option>
            <option value="UPI">UPI</option>
            <option value="Bank Transfer">Bank Transfer</option>
        </select>

        <input type="date" name="date" required>

        <button type="submit">Save Payment</button>
    </form>

    <a class="back-link" href="dashboard.php">Back to Dashboard</a>
</div>

</body>
</html>