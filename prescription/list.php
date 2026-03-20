<?php
session_start();
include "../config/db.php";

$result = mysqli_query($conn, "
    SELECT pr.*, 
           p.name AS patient_name, 
           d.name AS doctor_name, 
           dr.name AS drug_name
    FROM prescription pr
    JOIN consultation c ON pr.consultation_id = c.consultation_id
    JOIN appointment a ON c.appointment_id = a.appointment_id
    JOIN patient p ON a.patient_id = p.patient_id
    JOIN doctor d ON a.doctor_id = d.doctor_id
    JOIN drug dr ON pr.drug_id = dr.drug_id
    ORDER BY pr.prescription_id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Prescriptions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<h3>Prescriptions</h3>

<a href="../dashboard.php" class="btn btn-secondary mb-3">← Back</a>
<a href="add.php" class="btn btn-primary mb-3">+ Add Prescription</a>

<?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success">Prescription added successfully!</div>
<?php endif; ?>

<table class="table table-bordered">
    <tr>
        <th>Patient</th>
        <th>Doctor</th>
        <th>Drug</th>
        <th>Quantity</th>
        <th>Date</th>
    </tr>

    <?php if(mysqli_num_rows($result) > 0): ?>
        <?php while($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?= $row['patient_name'] ?></td>
            <td><?= $row['doctor_name'] ?></td>
            <td><?= $row['drug_name'] ?></td>
            <td><?= $row['quantity'] ?></td>
            <td><?= $row['created_at'] ?? 'N/A' ?></td>
        </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="5" class="text-center">No prescriptions found</td>
        </tr>
    <?php endif; ?>

</table>

</body>
</html>