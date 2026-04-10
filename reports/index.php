<?php
session_start();
include "../config/db.php";

// Auth check
if(!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Admin and only
if($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'cashier') {
    header("Location: ../dashboard.php");
    exit();
}

include "../includes/header.php";

// Date range for current month
$month_start = date('Y-m-01');
$month_end = date('Y-m-t');

/* Total Payments Received This Month */
$stmt1 = $conn->prepare("
    SELECT COALESCE(SUM(amount_paid), 0) as total 
    FROM payment 
    WHERE DATE(payment_date) BETWEEN ? AND ?
");
$stmt1->bind_param("ss", $month_start, $month_end);
$stmt1->execute();
$total_payments = $stmt1->get_result()->fetch_assoc()['total'];

/* Total Invoiced This Month */
$stmt2 = $conn->prepare("
    SELECT COALESCE(SUM(total_amount), 0) as total 
    FROM invoice 
    WHERE DATE(created_at) BETWEEN ? AND ?
");
$stmt2->bind_param("ss", $month_start, $month_end);
$stmt2->execute();
$total_invoiced = $stmt2->get_result()->fetch_assoc()['total'];

/* Pending Payments (All unpaid invoices) */
$stmt3 = $conn->prepare("
    SELECT COALESCE(SUM(total_amount), 0) as total 
    FROM invoice 
    WHERE status = 'unpaid'
");
$stmt3->execute();
$pending = $stmt3->get_result()->fetch_assoc()['total'];
?>

<div class="main-content">
    <div class="navbar-top d-flex justify-content-between align-items-center">
        <h4><i class="bi bi-bar-chart"></i> Reports</h4>
        <div>
            <a href="../dashboard.php" class="btn btn-sm btn-secondary me-3">
                <i class="bi bi-house-door"></i> Dashboard
            </a>
            <span>
                Welcome, <strong><?php echo $_SESSION['username']; ?></strong> 
                (<?php echo $_SESSION['role']; ?>)
            </span>
            <div class="user-avatar d-inline-block ms-2">
                <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Daily -->
        <div class="col-md-4 mb-4">
            <div class="stat-card">
                <div class="stat-icon text-primary">
                    <i class="bi bi-calendar-day"></i>
                </div>
                <h5>Daily Report</h5>
                <p class="text-muted">View today's activities</p>
                <a href="daily.php" class="btn btn-outline-primary">
                    View Report
                </a>
            </div>
        </div>

        <!-- Weekly -->
        <div class="col-md-4 mb-4">
            <div class="stat-card">
                <div class="stat-icon text-success">
                    <i class="bi bi-calendar-week"></i>
                </div>
                <h5>Weekly Report</h5>
                <p class="text-muted">Last 7 days summary</p>
                <a href="weekly.php" class="btn btn-outline-success">
                    View Report
                </a>
            </div>
        </div>

        <!-- Monthly -->
        <div class="col-md-4 mb-4">
            <div class="stat-card">
                <div class="stat-icon text-warning">
                    <i class="bi bi-calendar-month"></i>
                </div>
                <h5>Monthly Report</h5>
                <p class="text-muted">Current month statistics</p>
                <a href="monthly.php" class="btn btn-outline-warning">
                    View Report
                </a>
            </div>
        </div>
    </div>

    <!-- Financial Summary -->
    <div class="table-card mt-3">
        <h5 class="mb-3">
            <i class="bi bi-cash-stack"></i> Financial Summary (This Month)
        </h5>

        <div class="row mt-3">
            <div class="col-md-4">
                <div class="p-3 bg-light rounded">
                    <small class="text-muted">Total Payments Received</small>
                    <h4 class="text-success">
                        Ksh <?= number_format($total_payments, 2) ?>
                    </h4>
                </div>
            </div>

            <div class="col-md-4">
                <div class="p-3 bg-light rounded">
                    <small class="text-muted">Total Invoiced</small>
                    <h4 class="text-primary">
                        Ksh <?= number_format($total_invoiced, 2) ?>
                    </h4>
                </div>
            </div>

            <div class="col-md-4">
                <div class="p-3 bg-light rounded">
                    <small class="text-muted">Pending Payments</small>
                    <h4 class="text-danger">
                        Ksh <?= number_format($pending, 2) ?>
                    </h4>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>