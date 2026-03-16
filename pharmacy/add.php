<?php
session_start();
include "../config/db.php";

if(!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Only admin & pharmacist allowed
if($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'pharmacist') {
    header("Location: ../dashboard.php");
    exit();
}

if(isset($_POST['submit'])) {

    $name = $_POST['name'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];
    $expiry = $_POST['expiry'];

    // Use prepared statement (secure)
    $stmt = $conn->prepare("INSERT INTO drug (name, quantity, price, expiry_date)
                            VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sids", $name, $quantity, $price, $expiry);

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
    <title>Add Drug</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<h3>Add New Drug</h3>

<!-- Navigation -->
<div class="mb-3">
    <a href="../dashboard.php" class="btn btn-secondary">
        ← Back to Dashboard
    </a>

    <a href="list.php" class="btn btn-dark">
        ← Back to Drug List
    </a>
</div>

<?php if(isset($error)): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<div class="card" style="max-width:600px;">
    <div class="card-body">
        <form method="POST">

            <div class="mb-3">
                <label class="form-label">Drug Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Quantity</label>
                <input type="number" name="quantity" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Price (Ksh)</label>
                <input type="number" step="0.01" name="price" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Expiry Date</label>
                <input type="date" name="expiry" class="form-control" required>
            </div>

            <button type="submit" name="submit" class="btn btn-success">
                Save Drug
            </button>

            <a href="list.php" class="btn btn-danger">
                Cancel
            </a>

        </form>
    </div>
</div>

</body>
</html>