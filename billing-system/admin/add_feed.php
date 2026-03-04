<?php
include "../config/db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Access denied");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $feed_name = trim($_POST['feed_name']);
    $price = trim($_POST['price']);

    if (empty($feed_name) || empty($price)) {
        $message = "All fields required";
    } else {

        $check = mysqli_query($conn, "SELECT feed_id FROM feed WHERE feed_name='$feed_name'");

        if (mysqli_num_rows($check) > 0) {
            $message = "Feed already exists";
        } else {

            $insert = mysqli_query($conn,
                "INSERT INTO feed (feed_name, price)
                 VALUES ('$feed_name', '$price')"
            );

            if ($insert) {
                $message = "Feed added successfully";
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
    <title>Add Feed</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="dashboard">
    <h2>Add Feed</h2>

    <?php if ($message) { ?>
        <p style="margin-bottom:15px; font-weight:bold;">
            <?= $message ?>
        </p>
    <?php } ?>

    <form method="post">
        <input type="text" name="feed_name" placeholder="Feed Name" required>
        <input type="number" step="0.01" name="price" placeholder="Price per bag" required>
        <button type="submit">Add Feed</button>
    </form>

    <br>
    <a href="dashboard.php" class="nav-btn">Back to Dashboard</a>
</div>

</body>
</html>