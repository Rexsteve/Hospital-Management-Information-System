<?php
session_start();
include "../config/db.php";

if(!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Only admin & doctor allowed
if($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'doctor') {
    header("Location: ../dashboard.php");
    exit();
}

/* Fetch only Pending appointments */
$appointments = $conn->query("
    SELECT appointment.appointment_id,
           patient.name AS patient_name,
           doctor.name AS doctor_name
    FROM appointment
    JOIN patient ON appointment.patient_id = patient.patient_id
    JOIN doctor ON appointment.doctor_id = doctor.doctor_id
    WHERE appointment.status = 'Pending'
    ORDER BY appointment.appointment_date DESC
");

if(isset($_POST['submit'])) {

    $appointment_id = $_POST['appointment_id'];
    $diagnosis = $_POST['diagnosis'];
    $treatment = $_POST['treatment'];

    $sql = "INSERT INTO consultation (appointment_id, diagnosis, treatment)
            VALUES (?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $appointment_id, $diagnosis, $treatment);

    if($stmt->execute()) {

        // 🔥 Automatically mark appointment as Completed
        $update = $conn->prepare("UPDATE appointment SET status = 'Completed' WHERE appointment_id = ?");
        $update->bind_param("i", $appointment_id);
        $update->execute();

        header("Location: list.php?success=1");
        exit();

    } else {
        $error = "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Consultation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<h3>Add Consultation</h3>

<!-- Navigation -->
<div class="mb-3">
    <a href="../dashboard.php" class="btn btn-secondary">
        ← Back to Dashboard
    </a>

    <a href="list.php" class="btn btn-dark">
        ← Back to Consultations
    </a>
</div>

<?php if(isset($error)): ?>
    <div class="alert alert-danger">
        <?= $error ?>
    </div>
<?php endif; ?>

<form method="POST" class="card p-4" style="max-width:700px;">

    <div class="mb-3">
        <label class="form-label">Appointment</label>
        <select name="appointment_id" class="form-select" required>
            <option value="">Select Appointment</option>
            <?php while($a = $appointments->fetch_assoc()): ?>
                <option value="<?= $a['appointment_id']; ?>">
                    <?= $a['patient_name'] . " - " . $a['doctor_name']; ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Diagnosis</label>
        <textarea name="diagnosis" class="form-control" rows="3" required></textarea>
    </div>

    <div class="mb-3">
        <label class="form-label">Treatment</label>
        <textarea name="treatment" class="form-control" rows="3"></textarea>
    </div>

    <button type="submit" name="submit" class="btn btn-success">
        Save Consultation
    </button>

    <a href="list.php" class="btn btn-danger">
        Cancel
    </a>

</form>

</body>
</html>