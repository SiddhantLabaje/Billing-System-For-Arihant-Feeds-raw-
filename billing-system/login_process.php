<?php
include "config/db.php";
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $phone = $_POST['phone'];
    $password = $_POST['password'];

    $stmt = $conn->prepare(
        "SELECT user_id, name, password, role FROM users WHERE phone=?"
    );
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {

        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {

            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] == 'admin') {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: customer/dashboard.php");
            }
            exit;

        } else {
            $_SESSION['login_error'] = "Invalid Password";
        }

    } else {
        $_SESSION['login_error'] = "User Not Found";
    }

    header("Location: index.php");
    exit;
}