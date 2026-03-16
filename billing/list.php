<?php
session_start();
include "../config/db.php";

if(!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$result = $conn->query("
    SELECT i.*, p.name as patient_name
    FROM invoice i
    JOIN patient p ON i.patient_id = p.patient_id
    ORDER BY i.created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Billing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<h3>Billing / Invoices</h3>
<a href="../dashboard.php" class="btn btn-secondary mb-3">Back</a>

<table class="table table-bordered">
    <tr>
        <th>#</th>
        <th>Patient</th>
        <th>Total Amount</th>
        <th>Status</th>
        <th>Date</th>
    </tr>

    <?php while($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $row['invoice_id'] ?></td>
        <td><?= $row['patient_name'] ?></td>
        <td>Ksh <?= number_format($row['total_amount'],2) ?></td>
        <td>
            <?php if($row['status'] == 'paid'): ?>
                <span class="badge bg-success">Paid</span>
            <?php else: ?>
                <span class="badge bg-danger">Unpaid</span>
            <?php endif; ?>
        </td>
        <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
    </tr>
    <?php endwhile; ?>

</table>

</body>
</html>