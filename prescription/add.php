<?php
session_start();
include "../config/db.php";

$consultation_id = $_GET['consultation_id'] ?? 0;

// Get consultation details
$consult = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT c.*, a.patient_id, p.name as patient_name, d.name as doctor_name 
     FROM consultation c
     JOIN appointment a ON c.appointment_id = a.appointment_id
     JOIN patient p ON a.patient_id = p.patient_id
     JOIN doctor d ON a.doctor_id = d.doctor_id
     WHERE c.consultation_id = $consultation_id"));

// Add prescription
if(isset($_POST['submit'])) {
    $consultation_id = $_POST['consultation_id'];
    $drug_id = $_POST['drug_id'];
    $quantity = $_POST['quantity'];
    
    // Insert prescription
    $query = "INSERT INTO prescription (consultation_id, drug_id, quantity) 
              VALUES ('$consultation_id', '$drug_id', '$quantity')";
    
    if(mysqli_query($conn, $query)) {
        // Reduce drug stock
        mysqli_query($conn, "UPDATE drug SET quantity = quantity - $quantity WHERE drug_id = $drug_id");
        header("Location: list.php?consultation_id=$consultation_id&success=1");
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}

// Get available drugs
$drugs = mysqli_query($conn, "SELECT * FROM drug WHERE quantity > 0 ORDER BY name");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Prescription</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Add Prescription</h4>
                    </div>
                    <div class="card-body">
                        
                        <div class="alert alert-info">
                            <strong>Patient:</strong> <?= $consult['patient_name'] ?><br>
                            <strong>Doctor:</strong> <?= $consult['doctor_name'] ?><br>
                            <strong>Diagnosis:</strong> <?= $consult['diagnosis'] ?? 'N/A' ?>
                        </div>
                        
                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <input type="hidden" name="consultation_id" value="<?= $consultation_id ?>">
                            
                            <div class="mb-3">
                                <label>Select Drug</label>
                                <select name="drug_id" class="form-control" required>
                                    <option value="">-- Choose Drug --</option>
                                    <?php while($drug = mysqli_fetch_assoc($drugs)): ?>
                                    <option value="<?= $drug['drug_id'] ?>">
                                        <?= $drug['name'] ?> (Stock: <?= $drug['quantity'] ?> | Price: Ksh <?= $drug['price'] ?>)
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label>Quantity</label>
                                <input type="number" name="quantity" class="form-control" required min="1">
                            </div>
                            
                            <button type="submit" name="submit" class="btn btn-primary">Add to Prescription</button>
                            <a href="list.php?consultation_id=<?= $consultation_id ?>" class="btn btn-info">View Prescribed</a>
                            <a href="../consultations/list.php" class="btn btn-secondary">Back</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>