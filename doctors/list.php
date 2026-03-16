<?php
session_start();
include "../config/db.php";

if(!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Only admin allowed
if($_SESSION['role'] != 'admin') {
    header("Location: ../dashboard.php");
    exit();
}

/* Handle Delete */
if(isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    $stmt = $conn->prepare("DELETE FROM doctor WHERE doctor_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: list.php?deleted=1");
    exit();
}

$result = $conn->query("SELECT * FROM doctor ORDER BY name ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Doctor List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<h3>Doctors</h3>

<!-- Navigation -->
<div class="mb-3">
    <a href="../dashboard.php" class="btn btn-secondary">
        ← Back to Dashboard
    </a>

    <a href="add.php" class="btn btn-primary">
        + Add New Doctor
    </a>
</div>

<?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success">Doctor added successfully!</div>
<?php endif; ?>

<?php if(isset($_GET['deleted'])): ?>
    <div class="alert alert-success">Doctor deleted successfully!</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Specialization</th>
                    <th>Contact</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>

            <?php if($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['doctor_id']; ?></td>
                    <td><?= $row['name']; ?></td>
                    <td><?= $row['specialization']; ?></td>
                    <td><?= $row['contact']; ?></td>
                    <td>
                        <a href="edit.php?id=<?= $row['doctor_id']; ?>" 
                           class="btn btn-sm btn-warning">Edit</a>

                        <a href="list.php?delete=<?= $row['doctor_id']; ?>" 
                           class="btn btn-sm btn-danger"
                           onclick="return confirm('Are you sure you want to delete this doctor?')">
                           Delete
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center">No doctors found</td>
                </tr>
            <?php endif; ?>

            </tbody>
        </table>

    </div>
</div>

</body>
</html>