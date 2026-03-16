<?php
session_start();
include "../config/db.php";

if(!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Only admin & receptionist should access
if($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'receptionist') {
    header("Location: ../dashboard.php");
    exit();
}

$result = $conn->query("SELECT * FROM patient ORDER BY patient_id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Patient List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<h3>Patient List</h3>

<!-- Navigation Buttons -->
<div class="mb-3">
    <a href="../dashboard.php" class="btn btn-secondary">
        ← Back to Dashboard
    </a>

    <a href="add.php" class="btn btn-primary">
        + Add New Patient
    </a>
</div>

<!-- Success Message -->
<?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success">
        Patient saved successfully!
    </div>
<?php endif; ?>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Gender</th>
            <th>DOB</th>
            <th>Contact</th>
            <th>Address</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>

    <?php if($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['patient_id']; ?></td>
            <td><?= $row['name']; ?></td>
            <td><?= $row['gender']; ?></td>
            <td><?= $row['dob']; ?></td>
            <td><?= $row['contact']; ?></td>
            <td><?= $row['address']; ?></td>
            <td>
                <a href="view.php?id=<?= $row['patient_id']; ?>" 
                   class="btn btn-sm btn-info">View</a>

                <a href="edit.php?id=<?= $row['patient_id']; ?>" 
                   class="btn btn-sm btn-warning">Edit</a>
            </td>
        </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="7" class="text-center">No patients found</td>
        </tr>
    <?php endif; ?>

    </tbody>
</table>

</body>
</html>