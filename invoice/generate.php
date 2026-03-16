<?php
session_start();
include "../config/db.php";

$consultation_id = $_GET['consultation_id'] ?? 0;
$total = $_GET['total'] ?? 0;

// Get patient_id from consultation
$consult = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT a.patient_id 
     FROM consultation c
     JOIN appointment a ON c.appointment_id = a.appointment_id
     WHERE c.consultation_id = $consultation_id"));
$patient_id = $consult['patient_id'] ?? 0;

// Get patient name
$patient = mysqli_fetch_assoc(mysqli_query($conn, "SELECT name FROM patient WHERE patient_id = $patient_id"));

// Generate invoice
if(isset($_POST['generate'])) {
    $patient_id = $_POST['patient_id'];
    $total_amount = $_POST['total_amount'];
    
    $query = "INSERT INTO invoice (patient_id, total_amount, status) 
              VALUES ('$patient_id', '$total_amount', 'unpaid')";
    
    if(mysqli_query($conn, $query)) {
        $invoice_id = mysqli_insert_id($conn);
        header("Location: ../payment/make.php?invoice_id=$invoice_id");
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Generate Invoice</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4>Generate Invoice</h4>
                    </div>
                    <div class="card-body">
                        
                        <form method="POST">
                            <input type="hidden" name="patient_id" value="<?= $patient_id ?>">
                            
                            <div class="mb-3">
                                <label>Patient Name</label>
                                <input type="text" class="form-control" value="<?= $patient['name'] ?? 'N/A' ?>" readonly>
                            </div>
                            
                            <div class="mb-3">
                                <label>Consultation Fee</label>
                                <input type="text" class="form-control" value="Ksh 500.00" readonly>
                            </div>
                            
                            <div class="mb-3">
                                <label>Medication Total</label>
                                <input type="text" class="form-control" value="Ksh <?= number_format($total, 2) ?>" readonly>
                            </div>
                            
                            <div class="mb-3">
                                <label>Total Amount</label>
                                <input type="text" name="total_amount" class="form-control form-control-lg fw-bold" 
                                       value="<?= 500 + $total ?>" readonly>
                            </div>
                            
                            <button type="submit" name="generate" class="btn btn-primary w-100">Generate Invoice</button>
                            <a href="../prescription/list.php?consultation_id=<?= $consultation_id ?>" 
                               class="btn btn-secondary w-100 mt-2">Back</a>
                        </form>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>