<?php
session_start();

if(isset($_SESSION['user_id'])) {
    header("Location: ../dashboard.php");
    exit();
}

include "../config/db.php";

$error = '';
$success = '';

if(isset($_POST['register'])) {

    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];

    // Doctor fields (optional unless role=doctor)
    $specialization = isset($_POST['specialization']) ? mysqli_real_escape_string($conn, $_POST['specialization']) : '';
    $contact = isset($_POST['contact']) ? mysqli_real_escape_string($conn, $_POST['contact']) : '';

    if($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {

        // Check username exists
        $check = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        $result = $check->get_result();

        if($result->num_rows > 0) {
            $error = "Username already exists!";
        } else {

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert user
            $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $hashed_password, $role);

            if($stmt->execute()) {

                $user_id = $stmt->insert_id;

                // If doctor → insert into doctor table with specialization + contact
                if($role === 'doctor') {

                    // Basic validation
                    if(empty($specialization) || empty($contact)) {
                        $error = "Specialization and Contact are required for doctors!";
                    } else {

                        $name = $username;

                        $doc_stmt = $conn->prepare("
                            INSERT INTO doctor (name, specialization, contact, user_id)
                            VALUES (?, ?, ?, ?)
                        ");
                        $doc_stmt->bind_param("sssi", $name, $specialization, $contact, $user_id);
                        $doc_stmt->execute();

                        $success = "Doctor registered successfully!";
                    }

                } else {
                    $success = "Registration successful! Please login.";
                }

            } else {
                $error = "Registration failed: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - HMIS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <script>
        function toggleDoctorFields() {
            var role = document.getElementById("role").value;
            var doctorFields = document.getElementById("doctorFields");

            if(role === "doctor") {
                doctorFields.style.display = "block";
            } else {
                doctorFields.style.display = "none";
            }
        }
    </script>
</head>

<body class="d-flex align-items-center justify-content-center" style="min-height:100vh; background:#667eea;">

<div class="bg-white p-4 rounded shadow" style="width:100%; max-width:450px;">

    <h3 class="text-center mb-3">🏥 HMIS Register</h3>

    <?php if($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <?php if($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST">

        <div class="mb-3">
            <label>Username</label>
            <input type="text" name="username" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Confirm Password</label>
            <input type="password" name="confirm_password" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Register as</label>
            <select name="role" id="role" class="form-select" onchange="toggleDoctorFields()" required>
                <option value="">Select role</option>
                <option value="admin">Admin</option>
                <option value="doctor">Doctor</option>
                <option value="pharmacist">Pharmacist</option>
                <option value="receptionist">Receptionist</option>
                <option value="cashier">Cashier</option>
            </select>
        </div>

        <!-- Doctor-only fields -->
        <div id="doctorFields" style="display:none; border:1px solid #ddd; padding:10px; border-radius:5px; margin-bottom:15px;">
            <h6>Doctor Details</h6>

            <div class="mb-2">
                <label>Specialization</label>
                <input type="text" name="specialization" class="form-control">
            </div>

            <div class="mb-2">
                <label>Contact</label>
                <input type="text" name="contact" class="form-control">
            </div>
        </div>

        <button type="submit" name="register" class="btn btn-primary w-100">
            Register
        </button>

    </form>

    <div class="text-center mt-3">
        <a href="login.php">Already have an account? Login</a>
    </div>

</div>

</body>
</html>