<?php
session_start();
include "../config/db.php";

if(!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

if(!isset($_GET['id'])) {
    echo "Patient ID not provided.";
    exit();
}

$patient_id = $_GET['id'];

/* Get Patient Details */
$patient = $conn->query("SELECT * FROM patient WHERE patient_id=$patient_id")->fetch_assoc();

/* Get Medical History (Consultations) */
$history = $conn->query("
    SELECT c.*, d.name AS doctor_name
    FROM consultation c
    LEFT JOIN doctor d ON c.doctor_id = d.doctor_id
    WHERE c.patient_id = $patient_id
    ORDER BY c.date DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Patient Profile</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<a href="list.php" class="btn btn-secondary mb-3">← Back</a>

<h3>Patient Information</h3>

<table class="table table-bordered">
<tr>
<td><b>Name</b></td>
<td><?= $patient['name']; ?></td>
</tr>

<tr>
<td><b>Gender</b></td>
<td><?= $patient['gender']; ?></td>
</tr>

<tr>
<td><b>Date of Birth</b></td>
<td><?= $patient['dob']; ?></td>
</tr>

<tr>
<td><b>Contact</b></td>
<td><?= $patient['contact']; ?></td>
</tr>

<tr>
<td><b>Address</b></td>
<td><?= $patient['address']; ?></td>
</tr>
</table>


<h3 class="mt-4">Medical History</h3>

<table class="table table-striped table-bordered">

<tr>
<th>Date</th>
<th>Doctor</th>
<th>Diagnosis</th>
<th>Treatment</th>
</tr>

<?php if($history->num_rows > 0): ?>

<?php while($row = $history->fetch_assoc()): ?>

<tr>
<td><?= $row['date']; ?></td>
<td><?= $row['doctor_name']; ?></td>
<td><?= $row['diagnosis']; ?></td>
<td><?= $row['treatment']; ?></td>
</tr>

<?php endwhile; ?>

<?php else: ?>

<tr>
<td colspan="4" class="text-center">No medical history found</td>
</tr>

<?php endif; ?>

</table>

</body>
</html>