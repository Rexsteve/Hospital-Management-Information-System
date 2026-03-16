<?php
session_start();
include "../config/db.php";

$invoice_id = $_GET['invoice_id'] ?? 0;

// Get invoice details
$invoice = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT i.*, p.name as patient_name 
     FROM invoice i
     JOIN patient p ON i.patient_id = p.patient_id
     WHERE i.invoice_id = $invoice_id"));

// Process payment
if(isset($_POST['pay'])) {
    $invoice_id = $_POST['invoice_id'];
    $amount_paid = $_POST['amount_paid'];
    $payment_method = $_POST['payment_method'];
    
    // Insert payment record
    $query = "INSERT INTO payment (invoice_id, amount_paid, payment_method) 
              VALUES ('$invoice_id', '$amount_paid', '$payment_method')";
    
    if(mysqli_query($conn, $query)) {
        // Update invoice status to paid
        mysqli_query($conn, "UPDATE invoice SET status = 'paid' WHERE invoice_id = $invoice_id");
        $success = "Payment recorded successfully!";
    } else {
        $error = "Error: " . mysqli_error($conn);
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
                            
                        <?php elseif(isset($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                            
                        <?php else: ?>
                            
                            <div class="mb-3">
                                <label>Invoice #<?= $invoice_id ?></label>
                                <input type="text" class="form-control" value="Patient: <?= $invoice['patient_name'] ?>" readonly>
                            </div>
                            
                            <div class="mb-3">
                                <label>Total Amount</label>
                                <input type="text" class="form-control fw-bold" 
                                       value="Ksh <?= number_format($invoice['total_amount'], 2) ?>" readonly>
                            </div>
                            
                            <form method="POST">
                                <input type="hidden" name="invoice_id" value="<?= $invoice_id ?>">
                                <input type="hidden" name="amount_paid" value="<?= $invoice['total_amount'] ?>">
                                
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
                                
                                <button type="submit" name="pay" class="btn btn-success w-100">Confirm Payment</button>
                                <a href="../invoice/list.php" class="btn btn-secondary w-100 mt-2">Cancel</a>
                            </form>
                            
                        <?php endif; ?>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>