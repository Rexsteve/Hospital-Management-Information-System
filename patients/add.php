<?php
session_start();
include "../config/db.php";

if(!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Only admin & receptionist allowed
if($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'receptionist') {
    header("Location: ../dashboard.php");
    exit();
}

if(isset($_POST['submit'])) {

    $name = $_POST['name'];
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];
    $contact = $_POST['contact'];
    $address = $_POST['address'];

    $sql = "INSERT INTO patient (name, gender, dob, contact, address)
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $name, $gender, $dob, $contact, $address);

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
    <title>Add Patient</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<h3>Add Patient</h3>

<!-- Navigation Buttons -->
<div class="mb-3">
    <a href="../dashboard.php" class="btn btn-secondary">
        ← Back to Dashboard
    </a>

    <a href="list.php" class="btn btn-dark">
        ← Back to Patient List
    </a>
</div>

<?php if(isset($error)): ?>
    <div class="alert alert-danger">
        <?= $error ?>
    </div>
<?php endif; ?>

<form method="POST" class="card p-4" style="max-width:600px;">

    <div class="mb-3">
        <label class="form-label">Full Name</label>
        <input type="text" name="name" class="form-control" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Gender</label>
        <select name="gender" class="form-select">
            <option value="Male">Male</option>
            <option value="Female">Female</option>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Date of Birth</label>
        <input type="date" name="dob" class="form-control">
    </div>

    <div class="mb-3">
        <label class="form-label">Contact</label>
        <input type="text" name="contact" class="form-control">
    </div>

    <div class="mb-3">
        <label class="form-label">Address</label>
        <input type="text" name="address" class="form-control">
    </div>

    <button type="submit" name="submit" class="btn btn-success">
        Save Patient
    </button>

    <a href="list.php" class="btn btn-danger">
        Cancel
    </a>

</form>

</body>
</html>