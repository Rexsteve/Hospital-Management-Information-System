<?php
session_start();
include "../config/db.php";

if($_SESSION['role'] !== 'admin') {
    die("Access denied");
}

/* Delete user (soft safety optional) */
if(isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM users WHERE user_id=$id");
    header("Location: users_all.php");
    exit();
}

/* Change role */
if(isset($_GET['role']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $role = $_GET['role'];

    mysqli_query($conn, "UPDATE users SET role='$role' WHERE user_id=$id");
    header("Location: users_all.php");
    exit();
}

$users = mysqli_query($conn, "SELECT * FROM users ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">

    <h3>System Users Management</h3>

    <a href="../dashboard.php" class="btn btn-secondary mb-3">← Back to Dashboard</a>

    <table class="table table-bordered bg-white">
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Role</th>
            <th>Status</th>
            <th>Created</th>
            <th>Actions</th>
        </tr>

        <?php while($u = mysqli_fetch_assoc($users)): ?>
        <tr>
            <td><?= $u['user_id'] ?></td>
            <td><?= $u['username'] ?></td>
            <td>
                <span class="badge bg-info"><?= $u['role'] ?></span>
            </td>
            <td>
                <?php if($u['status']=='approved'): ?>
                    <span class="badge bg-success">Approved</span>
                <?php elseif($u['status']=='pending'): ?>
                    <span class="badge bg-warning">Pending</span>
                <?php else: ?>
                    <span class="badge bg-danger">Rejected</span>
                <?php endif; ?>
            </td>
            <td><?= $u['created_at'] ?></td>
            <td>

                <!-- Role change -->
                <a href="?id=<?= $u['user_id'] ?>&role=doctor" class="btn btn-sm btn-primary">Doctor</a>
                <a href="?id=<?= $u['user_id'] ?>&role=pharmacist" class="btn btn-sm btn-success">Pharmacist</a>
                <a href="?id=<?= $u['user_id'] ?>&role=receptionist" class="btn btn-sm btn-warning">Reception</a>

                <!-- Delete -->
                <a href="?delete=<?= $u['user_id'] ?>" class="btn btn-sm btn-danger"
                   onclick="return confirm('Delete user?')">
                   Delete
                </a>

            </td>
        </tr>
        <?php endwhile; ?>

    </table>

</div>

</body>
</html>