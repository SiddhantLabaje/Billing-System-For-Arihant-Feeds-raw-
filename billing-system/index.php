<?php
session_start();
$error = $_SESSION['login_error'] ?? "";
unset($_SESSION['login_error']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Arihant Feeds | Login</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: #0f172a; /* Dark navy */
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-wrapper {
            width: 100%;
            max-width: 420px;
        }

        .login-card {
            background: #1e293b;
            border-radius: 16px;
            padding: 40px 35px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
            color: white;
        }

        .brand-title {
            text-align: center;
            font-weight: 700;
            font-size: 24px;
            color: #6366f1;
            margin-bottom: 10px;
        }

        .brand-sub {
            text-align: center;
            font-size: 14px;
            color: #94a3b8;
            margin-bottom: 30px;
        }

        .form-label {
            color: #cbd5e1;
        }

        .form-control {
            background: #0f172a;
            border: 1px solid #334155;
            color: white;
            border-radius: 10px;
            padding: 10px 15px;
        }

        .form-control:focus {
            background: #0f172a;
            border-color: #6366f1;
            box-shadow: none;
            color: white;
        }

        .btn-login {
            background: #6366f1;
            border: none;
            border-radius: 10px;
            padding: 10px;
            font-weight: 600;
            transition: 0.3s;
        }

        .btn-login:hover {
            background: #4f46e5;
            transform: translateY(-2px);
        }

        .footer-note {
            text-align: center;
            font-size: 13px;
            color: #64748b;
            margin-top: 20px;
        }
    </style>
</head>

<body>

<div class="login-wrapper">
    <div class="login-card">

        <div class="brand-title">Arihant Feeds</div>
        <div class="brand-sub">Billing & Ledger Management System</div>

        <?php if ($error) { ?>
            <div class="alert alert-danger text-center">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php } ?>

        <form method="post" action="login_process.php">

            <div class="mb-3">
                <label class="form-label">Phone Number</label>
                <input type="text" name="phone" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-login text-white w-100">
                Login
            </button>

        </form>

        <div class="footer-note">
            © <?= date("Y") ?> Arihant Feeds
        </div>

    </div>
</div>

</body>
</html>