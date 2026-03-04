<?php
include "../config/db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Access denied");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);

    if (empty($name) || empty($phone) || empty($password)) {
        $message = "All fields are required";
    } else {

        // Check if phone already exists
        $check = mysqli_query($conn, "SELECT user_id FROM users WHERE phone='$phone'");

        if (mysqli_num_rows($check) > 0) {
            $message = "Phone number already exists";
        } else {

            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $insert = mysqli_query($conn,
                "INSERT INTO users (name, phone, password, role)
                 VALUES ('$name', '$phone', '$hashed', 'customer')"
            );

            if ($insert) {
                $message = "Customer added successfully";
            } else {
                $message = "Something went wrong";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Customer</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="dashboard">
    <h2>Add Customer</h2>

    <?php if ($message) { ?>
        <p style="margin-bottom:15px; font-weight:bold;">
            <?= $message ?>
        </p>
    <?php } ?>

    <form method="post">
        <input type="text" name="name" placeholder="Customer Name" required>
        <input type="text" name="phone" placeholder="Phone Number" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Add Customer</button>
    </form>

    <br>
    <a href="dashboard.php" class="nav-btn">Back to Dashboard</a>
</div>

</body>
</html>