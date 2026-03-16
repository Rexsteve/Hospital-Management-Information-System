<?php
session_start();
include "../config/db.php";

if(!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Only admin & receptionist can book
if($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'receptionist') {
    header("Location: ../dashboard.php");
    exit();
}

/* Fetch Patients */
$patients = $conn->query("SELECT * FROM patient ORDER BY name ASC");

/* Fetch Doctors */
$doctors = $conn->query("SELECT * FROM doctor ORDER BY name ASC");

if(isset($_POST['submit'])) {

    $patient_id = $_POST['patient_id'];
    $doctor_id = $_POST['doctor_id'];
    $date = $_POST['appointment_date'];
    $time = $_POST['appointment_time'];
    $status = "Pending";

    $sql = "INSERT INTO appointment 
            (patient_id, doctor_id, appointment_date, appointment_time, status)
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisss", $patient_id, $doctor_id, $date, $time, $status);

    if($stmt->execute()) {
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
    <title>Book Appointment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<h3>Book Appointment</h3>

<!-- Navigation -->
<div class="mb-3">
    <a href="../dashboard.php" class="btn btn-secondary">
        ← Back to Dashboard
    </a>

    <a href="list.php" class="btn btn-dark">
        ← Back to Appointments
    </a>
</div>

<?php if(isset($error)): ?>
    <div class="alert alert-danger">
        <?= $error ?>
    </div>
<?php endif; ?>

<form method="POST" class="card p-4" style="max-width:600px;">

    <div class="mb-3">
        <label class="form-label">Patient</label>
        <select name="patient_id" class="form-select" required>
            <option value="">Select Patient</option>
            <?php while($p = $patients->fetch_assoc()): ?>
                <option value="<?= $p['patient_id']; ?>">
                    <?= $p['name']; ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Doctor</label>
        <select name="doctor_id" class="form-select" required>
            <option value="">Select Doctor</option>
            <?php while($d = $doctors->fetch_assoc()): ?>
                <option value="<?= $d['doctor_id']; ?>">
                    <?= $d['name']; ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Date</label>
        <input type="date" name="appointment_date" class="form-control" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Time</label>
        <input type="time" name="appointment_time" class="form-control" required>
    </div>

    <button type="submit" name="submit" class="btn btn-success">
        Save Appointment
    </button>

    <a href="list.php" class="btn btn-danger">
        Cancel
    </a>

</form>

</body>
</html>