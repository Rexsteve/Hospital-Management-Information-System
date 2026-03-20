<?php
session_start();
include "../config/db.php";

$invoice_id = $_GET['invoice_id'] ?? 0;

// Validate invoice_id
if(!$invoice_id) {
    header("Location: ../invoice/list.php");
    exit();
}

// Get invoice details
$invoice = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT i.*, p.name as patient_name 
     FROM invoice i
     JOIN patient p ON i.patient_id = p.patient_id
     WHERE i.invoice_id = $invoice_id"));

if(!$invoice) {
    header("Location: ../invoice/list.php");
    exit();
}

// Get total paid so far
$total_paid_row = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COALESCE(SUM(amount_paid), 0) AS total_paid 
     FROM payment 
     WHERE invoice_id = $invoice_id"));

$total_paid = $total_paid_row['total_paid'] ?? 0;

// Remaining balance
$remaining = $invoice['total_amount'] - $total_paid;

// Process payment
if(isset($_POST['pay'])) {

    $amount_paid = floatval($_POST['amount_paid']);
    $payment_method = $_POST['payment_method'];

    // Prevent overpayment
    if($amount_paid > $remaining) {
        $error = "Cannot pay more than remaining balance: Ksh " . number_format($remaining, 2);
    } else {

        // Insert payment record
        $stmt = $conn->prepare("
            INSERT INTO payment (invoice_id, amount_paid, payment_method) 
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("ids", $invoice_id, $amount_paid, $payment_method);
        $stmt->execute();

        // Recalculate total paid AFTER insertion
        $total_paid += $amount_paid;
        $balance = $invoice['total_amount'] - $total_paid;

        // Determine status
        if($total_paid == 0) {
            $status = 'unpaid';
        } elseif($total_paid < $invoice['total_amount']) {
            $status = 'partial';
        } else {
            $status = 'paid';
        }

        // Update invoice
        $update = $conn->prepare("
            UPDATE invoice 
            SET amount_paid = ?, balance = ?, status = ?
            WHERE invoice_id = ?
        ");
        $update->bind_param("ddsi", $total_paid, $balance, $status, $invoice_id);
        $update->execute();

        $success = "Payment recorded successfully!";
        $remaining = $balance;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Make Payment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4>Process Payment</h4>
                </div>
                <div class="card-body">

                    <?php if(isset($success)): ?>
                        <div class="alert alert-success"><?= $success ?></div>
                        <a href="../invoice/list.php" class="btn btn-primary w-100">View All Invoices</a>
                    <?php else: ?>

                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label>Invoice #<?= $invoice_id ?></label>
                            <input type="text" class="form-control" 
                                   value="Patient: <?= htmlspecialchars($invoice['patient_name']) ?>" readonly>
                        </div>

                        <div class="mb-3">
                            <label>Total Amount</label>
                            <input type="text" class="form-control fw-bold" 
                                   value="Ksh <?= number_format($invoice['total_amount'], 2) ?>" readonly>
                        </div>

                        <div class="mb-3">
                            <label>Total Paid So Far</label>
                            <input type="text" class="form-control fw-bold" 
                                   value="Ksh <?= number_format($total_paid, 2) ?>" readonly>
                        </div>

                        <div class="mb-3">
                            <label>Remaining Balance</label>
                            <input type="text" class="form-control fw-bold text-danger" 
                                   value="Ksh <?= number_format($remaining, 2) ?>" readonly>
                        </div>

                        <form method="POST">

                            <div class="mb-3">
                                <label>Payment Amount</label>
                                <input type="number" name="amount_paid" class="form-control" 
                                       max="<?= $remaining ?>" min="0.01" step="0.01" required>
                            </div>

                            <div class="mb-3">
                                <label>Payment Method</label>
                                <select name="payment_method" class="form-control" required>
                                    <option value="">Select Payment Method</option>
                                    <option value="Cash">Cash</option>
                                    <option value="M-PESA">M-PESA</option>
                                    <option value="Card">Card</option>
                                    <option value="Insurance">Insurance</option>
                                </select>
                            </div>

                            <button type="submit" name="pay" class="btn btn-success w-100">
                                Confirm Payment
                            </button>

                            <a href="../invoice/list.php" class="btn btn-secondary w-100 mt-2">
                                Cancel
                            </a>

                        </form>

                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>