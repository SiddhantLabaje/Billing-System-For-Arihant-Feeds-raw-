<?php
include "../config/db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Access denied");
}

/* Total Customers */
$total_customers = mysqli_fetch_assoc(
    mysqli_query($conn,
        "SELECT COUNT(*) as total FROM users WHERE role='customer'"
    )
)['total'];

/* Total Outstanding */
$outstanding_query = mysqli_query($conn,
    "SELECT 
        IFNULL(SUM(debit),0) as total_debit,
        IFNULL(SUM(credit),0) as total_credit
     FROM ledger"
);

$data = mysqli_fetch_assoc($outstanding_query);
$total_outstanding = $data['total_debit'] - $data['total_credit'];

/* Today Collection */
$today = date("Y-m-d");

$today_collection = mysqli_fetch_assoc(
    mysqli_query($conn,
        "SELECT IFNULL(SUM(credit),0) as today_total
         FROM ledger
         WHERE transaction_date='$today'"
    )
)['today_total'];

/* Monthly Sales */
$month = date("m");
$year = date("Y");

$monthly_sales = mysqli_fetch_assoc(
    mysqli_query($conn,
        "SELECT IFNULL(SUM(debit),0) as month_total
         FROM ledger
         WHERE MONTH(transaction_date)=$month 
         AND YEAR(transaction_date)=$year"
    )
)['month_total'];

/* Outstanding table */
$result = mysqli_query($conn,
    "SELECT u.user_id, u.name,
        IFNULL(SUM(l.debit),0) as total_debit,
        IFNULL(SUM(l.credit),0) as total_credit
     FROM users u
     LEFT JOIN ledger l ON u.user_id = l.user_id
     WHERE u.role='customer'
     GROUP BY u.user_id"
);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background:#f3f4f6;">

<div class="container-fluid">
    <div class="row">

        <!-- Sidebar -->
        <div class="col-md-2 bg-dark text-white min-vh-100 p-3">
            <h4 class="text-center mb-4">Admin Panel</h4>

            <a href="add_customer.php" class="d-block text-white mb-2">Add Customer</a>
            <a href="add_feed.php" class="d-block text-white mb-2">Add Feed</a>
            <a href="add_purchase.php" class="d-block text-white mb-2">Add Purchase</a>
            <a href="add_payment.php" class="d-block text-white mb-2">Add Payment</a>
            <a href="ledger_report.php" class="d-block text-white mb-2">Ledger Report</a>
            <a href="../logout.php" class="d-block text-danger">Logout</a>
        </div>

        <!-- Main Content -->
        <div class="col-md-10 p-4">

            <h2 class="mb-4">Welcome, <?= htmlspecialchars($_SESSION['name']); ?></h2>

            <!-- Summary Cards -->
            <div class="row g-4">

                <div class="col-md-3">
                    <div class="card shadow text-center">
                        <div class="card-body">
                            <h6>Total Customers</h6>
                            <h3><?= $total_customers ?></h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card shadow text-center">
                        <div class="card-body">
                            <h6>Total Outstanding</h6>
                            <h3 class="text-danger">₹<?= $total_outstanding ?></h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card shadow text-center">
                        <div class="card-body">
                            <h6>Today Collection</h6>
                            <h3 class="text-success">₹<?= $today_collection ?></h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card shadow text-center">
                        <div class="card-body">
                            <h6>This Month Sales</h6>
                            <h3 class="text-warning">₹<?= $monthly_sales ?></h3>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Outstanding Table -->
            <div class="card shadow mt-5">
                <div class="card-body">

                    <h4 class="mb-3">Outstanding Customers</h4>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped text-center">
                            <thead class="table-dark">
                                <tr>
                                    <th>Customer</th>
                                    <th>Total Purchase</th>
                                    <th>Total Paid</th>
                                    <th>Balance</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>

                            <?php while ($row = mysqli_fetch_assoc($result)) {
                                $balance = $row['total_debit'] - $row['total_credit'];
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['name']) ?></td>
                                    <td>₹<?= $row['total_debit'] ?></td>
                                    <td>₹<?= $row['total_credit'] ?></td>
                                    <td class="<?= $balance > 0 ? 'text-danger fw-bold' : 'text-success fw-bold' ?>">
                                        ₹<?= $balance ?>
                                    </td>
                                    <td>
                                        <?php if ($balance > 0) { ?>
                                            <span class="badge bg-danger">Pending</span>
                                        <?php } else { ?>
                                            <span class="badge bg-success">Clear</span>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php } ?>

                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>