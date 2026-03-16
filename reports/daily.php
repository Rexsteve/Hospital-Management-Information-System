<?php
session_start();
include "../config/db.php";
include "../includes/header.php";
include "../includes/sidebar.php";

$today = date('Y-m-d');

// Today's appointments - FIXED table names
$appointments = mysqli_query($conn, 
    "SELECT a.*, p.name as patient_name, d.name as doctor_name 
     FROM appointment a
     JOIN patient p ON a.patient_id = p.patient_id
     JOIN doctor d ON a.doctor_id = d.doctor_id
     WHERE a.appointment_date = '$today'");

// Today's payments - FIXED table names
$payments = mysqli_query($conn, 
    "SELECT p.*, pt.name as patient_name 
     FROM payment p
     JOIN invoice i ON p.invoice_id = i.invoice_id
     JOIN patient pt ON i.patient_id = pt.patient_id
     WHERE DATE(p.payment_date) = '$today'");

$total_payments = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COALESCE(SUM(amount_paid), 0) as total 
     FROM payment 
     WHERE DATE(payment_date) = '$today'"))['total'];
?>

<div class="main-content">
    <div class="navbar-top">
        <h4><i class="bi bi-calendar-day"></i> Daily Report - <?= date('d M Y') ?></h4>
        <a href="index.php" class="btn btn-secondary">Back to Reports</a>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="stat-card">
                <h5>Today's Appointments</h5>
                <h2><?= mysqli_num_rows($appointments) ?></h2>
            </div>
        </div>
        <div class="col-md-6">
            <div class="stat-card">
                <h5>Today's Payments</h5>
                <h2 class="text-success">Ksh <?= number_format($total_payments, 2) ?></h2>
            </div>
        </div>
    </div>

    <!-- Appointments List -->
    <div class="table-card mb-4">
        <h5>Today's Appointments</h5>
        <table class="table">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Patient</th>
                    <th>Doctor</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if(mysqli_num_rows($appointments) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($appointments)): ?>
                    <tr>
                        <td><?= $row['appointment_time'] ?></td>
                        <td><?= $row['patient_name'] ?></td>
                        <td><?= $row['doctor_name'] ?></td>
                        <td><span class="badge bg-<?= $row['status'] == 'completed' ? 'success' : 'warning' ?>"><?= $row['status'] ?></span></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center">No appointments today</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Payments List -->
    <div class="table-card">
        <h5>Today's Payments</h5>
        <table class="table">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Patient</th>
                    <th>Amount</th>
                    <th>Method</th>
                </tr>
            </thead>
            <tbody>
                <?php if(mysqli_num_rows($payments) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($payments)): ?>
                    <tr>
                        <td><?= date('H:i', strtotime($row['payment_date'])) ?></td>
                        <td><?= $row['patient_name'] ?></td>
                        <td>Ksh <?= number_format($row['amount_paid'], 2) ?></td>
                        <td><?= $row['payment_method'] ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center">No payments today</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include "../includes/footer.php"; ?>