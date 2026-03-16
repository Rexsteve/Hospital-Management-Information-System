<?php
session_start();
include "../config/db.php";

$consultation_id = $_GET['consultation_id'] ?? 0;

// Get consultation info
$consult = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT c.*, p.name as patient_name, p.patient_id
     FROM consultation c
     JOIN appointment a ON c.appointment_id = a.appointment_id
     JOIN patient p ON a.patient_id = p.patient_id
     WHERE c.consultation_id = $consultation_id"));

// Get prescriptions for this consultation
$prescriptions = mysqli_query($conn, 
    "SELECT p.*, d.name as drug_name, d.price 
     FROM prescription p
     JOIN drug d ON p.drug_id = d.drug_id
     WHERE p.consultation_id = $consultation_id");

// Calculate total
$total = 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Prescription List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="row mb-3">
            <div class="col-md-6">
                <h2>Prescriptions</h2>
            </div>
            <div class="col-md-6 text-end">
                <a href="add.php?consultation_id=<?= $consultation_id ?>" class="btn btn-primary">Add More Drugs</a>
            </div>
        </div>
        
        <?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success">Prescription added successfully!</div>
<?php endif; ?>

<?php if($consult): ?>
    <div class="alert alert-info">
        <strong>Patient:</strong> <?= $consult['patient_name'] ?>
    </div>
<?php else: ?>
    <div class="alert alert-warning">
        No consultation selected. Please go to Consultations first.
    </div>
<?php endif; ?>
        <div class="card">
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Drug</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($prescriptions) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($prescriptions)): 
                                $subtotal = $row['quantity'] * $row['price'];
                                $total += $subtotal;
                            ?>
                            <tr>
                                <td><?= $row['drug_name'] ?></td>
                                <td><?= $row['quantity'] ?></td>
                                <td>Ksh <?= number_format($row['price'], 2) ?></td>
                                <td>Ksh <?= number_format($subtotal, 2) ?></td>
                            </tr>
                            <?php endwhile; ?>
                            <tr class="fw-bold">
                                <td colspan="3" class="text-end">Total:</td>
                                <td>Ksh <?= number_format($total, 2) ?></td>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">No prescriptions added yet</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <?php if(mysqli_num_rows($prescriptions) > 0): ?>
                    <a href="../invoice/generate.php?consultation_id=<?= $consultation_id ?>&total=<?= $total ?>" 
                       class="btn btn-success">Generate Invoice</a>
                <?php endif; ?>
                
                <a href="../consultations/list.php" class="btn btn-secondary">Back to Consultations</a>
            </div>
        </div>
    </div>
</body>
</html>