<?php
session_start();
include "../config/db.php";

if($_SESSION['role'] !== 'admin') {
    die("Access denied");
}

/* Approve user */
if(isset($_GET['approve'])) {
    $id = intval($_GET['approve']);
    mysqli_query($conn, "UPDATE users SET status='approved' WHERE user_id=$id");
    header("Location: users_pending.php");
    exit();
}

/* Reject user */
if(isset($_GET['reject'])) {
    $id = intval($_GET['reject']);
    mysqli_query($conn, "UPDATE users SET status='rejected' WHERE user_id=$id");
    header("Location: users_pending.php");
    exit();
}

$users = mysqli_query($conn, "
    SELECT * FROM users 
    WHERE status='pending'
    ORDER BY created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pending Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">

    <h3>Pending User Approvals</h3>
    <a href="../dashboard.php" class="btn btn-secondary mb-3">
    ← Back to Dashboard
    </a>

    <table class="table table-bordered bg-white mt-3">
        <tr>
            <th>Name</th>
            <th>Role</th>
            <th>Date</th>
            <th>Action</th>
        </tr>

        <?php while($u = mysqli_fetch_assoc($users)): ?>
        <tr>
            <td><?= $u['username'] ?></td>
            <td><?= $u['role'] ?></td>
            <td><?= $u['created_at'] ?></td>
            <td>
                <a href="?approve=<?= $u['user_id'] ?>" class="btn btn-success btn-sm">Approve</a>
                <a href="?reject=<?= $u['user_id'] ?>" class="btn btn-danger btn-sm">Reject</a>
            </td>
        </tr>
        <?php endwhile; ?>

    </table>

</div>

</body>
</html>