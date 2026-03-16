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

if(isset($_POST['submit'])) {

    $name = $_POST['name'];
    $specialization = $_POST['specialization'];
    $contact = $_POST['contact'];

    $sql = "INSERT INTO doctor (name, specialization, contact)
            VALUES (?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $name, $specialization, $contact);

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
    <title>Add Doctor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<h3>Add Doctor</h3>

<!-- Navigation -->
<div class="mb-3">
    <a href="../dashboard.php" class="btn btn-secondary">
        ← Back to Dashboard
    </a>

    <a href="list.php" class="btn btn-dark">
        ← Back to Doctors
    </a>
</div>

<?php if(isset($error)): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<div class="card" style="max-width:600px;">
    <div class="card-body">
        <form method="POST">

            <div class="mb-3">
                <label class="form-label">Doctor Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Specialization</label>
                <input type="text" name="specialization" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Contact</label>
                <input type="text" name="contact" class="form-control">
            </div>

            <button type="submit" name="submit" class="btn btn-success">
                Save Doctor
            </button>

            <a href="list.php" class="btn btn-danger">
                Cancel
            </a>

        </form>
    </div>
</div>

</body>
</html>