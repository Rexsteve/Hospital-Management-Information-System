<?php
session_start();
include "../config/db.php";

$invoices = mysqli_query($conn, 
    "SELECT i.*, p.name as patient_name 
     FROM invoice i
     JOIN patient p ON i.patient_id = p.patient_id
     ORDER BY i.created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Invoices</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="p-3">
    <a href="../dashboard.php" class="btn btn-secondary mb-3">
        ← Back to Dashboard
    </a>

    <a href="generate.php" class="btn btn-primary mb-3">
        + Generate Invoice
    </a>
</div>

<div class="container mt-2">
    <h2>All Invoices</h2>

    <div class="card">
        <div class="card-body">

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Invoice ID</th>
                        <th>Patient</th>
                        <th>Total Amount</th>
                        <th>Paid</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    <?php while($row = mysqli_fetch_assoc($invoices)): ?>

                        <?php
                        $invoice_id = $row['invoice_id'];
                        $total = $row['total_amount'];

                        // ✅ Get total paid from payment table
                        $paid_query = mysqli_query($conn,
                            "SELECT COALESCE(SUM(amount_paid),0) as total_paid 
                             FROM payment 
                             WHERE invoice_id = $invoice_id");

                        $paid_row = mysqli_fetch_assoc($paid_query);
                        $paid = $paid_row['total_paid'];

                        $balance = $total - $paid;

                        // ✅ Determine status
                        if($paid == 0){
                            $status = "Unpaid";
                            $badge = "danger";
                        } elseif($paid < $total){
                            $status = "Partially Paid";
                            $badge = "warning";
                        } else {
                            $status = "Fully Paid";
                            $badge = "success";
                        }
                        ?>

                        <tr>
                            <td><?= $invoice_id ?></td>
                            <td><?= $row['patient_name'] ?></td>
                            <td>Ksh <?= number_format($total, 2) ?></td>
                            <td>Ksh <?= number_format($paid, 2) ?></td>
                            <td>Ksh <?= number_format($balance, 2) ?></td>
                            <td>
                                <span class="badge bg-<?= $badge ?>">
                                    <?= $status ?>
                                </span>
                            </td>
                            <td><?= $row['created_at'] ?></td>
                            <td>
                                <?php if($status != "Fully Paid"): ?>
                                    <a href="../payment/make.php?invoice_id=<?= $invoice_id ?>" 
                                       class="btn btn-success btn-sm">
                                       Pay
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>

                    <?php endwhile; ?>
                </tbody>

            </table>

        </div>
    </div>

</div>
</body>
</html>