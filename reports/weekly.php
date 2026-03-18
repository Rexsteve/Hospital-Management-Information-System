<?php
session_start();
include "../config/db.php";
include "../includes/header.php";

// Get start and end of current week (Monday to Sunday)
$week_start = date('Y-m-d', strtotime('monday this week'));
$week_end = date('Y-m-d', strtotime('sunday this week'));

// If viewing a different week
if(isset($_GET['week_start'])) {
    $week_start = $_GET['week_start'];
    $week_end = date('Y-m-d', strtotime($week_start . ' +6 days'));
}

$prev_week = date('Y-m-d', strtotime($week_start . ' -7 days'));
$next_week = date('Y-m-d', strtotime($week_start . ' +7 days'));

// Weekly appointments
$appointments = mysqli_query($conn, 
    "SELECT a.*, p.name as patient_name, d.name as doctor_name,
            DAYNAME(a.appointment_date) as day_name
     FROM appointment a
     JOIN patient p ON a.patient_id = p.patient_id
     JOIN doctor d ON a.doctor_id = d.doctor_id
     WHERE a.appointment_date BETWEEN '$week_start' AND '$week_end'
     ORDER BY a.appointment_date, a.appointment_time");

// Weekly payments
$payments = mysqli_query($conn, 
    "SELECT p.*, pt.name as patient_name, DATE(p.payment_date) as pay_date
     FROM payment p
     JOIN invoice i ON p.invoice_id = i.invoice_id
     JOIN patient pt ON i.patient_id = pt.patient_id
     WHERE DATE(p.payment_date) BETWEEN '$week_start' AND '$week_end'
     ORDER BY p.payment_date");

// Daily totals for chart
$daily_totals = [];
for($i = 0; $i < 7; $i++) {
    $date = date('Y-m-d', strtotime($week_start . " +$i days"));
    $day_name = date('l', strtotime($date));
    
    $total = mysqli_fetch_assoc(mysqli_query($conn, 
        "SELECT COALESCE(SUM(amount_paid), 0) as total 
         FROM payment 
         WHERE DATE(payment_date) = '$date'"))['total'];
    
    $daily_totals[] = [
        'day' => substr($day_name, 0, 3),
        'date' => $date,
        'total' => $total
    ];
}

// Summary stats
$total_appointments = mysqli_num_rows($appointments);
$total_payments = 0;
$payment_count = 0;
while($row = mysqli_fetch_assoc($payments)) {
    $total_payments += $row['amount_paid'];
    $payment_count++;
}
// Reset payments pointer
mysqli_data_seek($payments, 0);
?>

<div class="main-content">
    <div class="navbar-top">
        <h4><i class="bi bi-calendar-week"></i> Weekly Report</h4>
        <div>
            <a href="weekly.php?week_start=<?= $prev_week ?>" class="btn btn-outline-primary btn-sm">← Previous Week</a>
            <a href="weekly.php" class="btn btn-outline-secondary btn-sm">Current Week</a>
            <a href="weekly.php?week_start=<?= $next_week ?>" class="btn btn-outline-primary btn-sm">Next Week →</a>
            <a href="index.php" class="btn btn-secondary btn-sm">Back</a>
        </div>
    </div>

    <!-- Week Info -->
    <div class="alert alert-info">
        <strong><?= date('d M Y', strtotime($week_start)) ?> - <?= date('d M Y', strtotime($week_end)) ?></strong>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <h5>Total Appointments</h5>
                <h2><?= $total_appointments ?></h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <h5>Total Payments</h5>
                <h2 class="text-success">Ksh <?= number_format($total_payments, 2) ?></h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <h5>Payment Count</h5>
                <h2><?= $payment_count ?></h2>
            </div>
        </div>
    </div>

    <!-- Daily Breakdown Chart (Simple Table) -->
    <div class="table-card mb-4">
        <h5>Daily Breakdown</h5>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Day</th>
                    <th>Date</th>
                    <th>Payments</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($daily_totals as $day): ?>
                <tr>
                    <td><strong><?= $day['day'] ?></strong></td>
                    <td><?= date('d M Y', strtotime($day['date'])) ?></td>
                    <td>Ksh <?= number_format($day['total'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
                <tr class="fw-bold">
                    <td colspan="2" class="text-end">Total:</td>
                    <td>Ksh <?= number_format($total_payments, 2) ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Appointments List -->
    <div class="table-card mb-4">
        <h5>Weekly Appointments</h5>
        <table class="table">
            <thead>
                <tr>
                    <th>Day</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Patient</th>
                    <th>Doctor</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if($total_appointments > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($appointments)): ?>
                    <tr>
                        <td><?= date('D', strtotime($row['appointment_date'])) ?></td>
                        <td><?= $row['appointment_date'] ?></td>
                        <td><?= $row['appointment_time'] ?></td>
                        <td><?= $row['patient_name'] ?></td>
                        <td><?= $row['doctor_name'] ?></td>
                        <td><span class="badge bg-<?= $row['status'] == 'completed' ? 'success' : 'warning' ?>"><?= $row['status'] ?></span></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center">No appointments this week</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Payments List -->
    <div class="table-card">
        <h5>Weekly Payments</h5>
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Patient</th>
                    <th>Amount</th>
                    <th>Method</th>
                </tr>
            </thead>
            <tbody>
                <?php if($payment_count > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($payments)): ?>
                    <tr>
                        <td><?= date('d M Y', strtotime($row['payment_date'])) ?></td>
                        <td><?= $row['patient_name'] ?></td>
                        <td>Ksh <?= number_format($row['amount_paid'], 2) ?></td>
                        <td><?= $row['payment_method'] ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center">No payments this week</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include "../includes/footer.php"; ?>