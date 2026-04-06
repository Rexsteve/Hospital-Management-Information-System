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

    $specialization = $_POST['specialization'] ?? '';
    $contact = $_POST['contact'] ?? '';

    if($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {

        $check = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        $result = $check->get_result();

        if($result->num_rows > 0) {
            $error = "Username already exists!";
        } else {

            if($role === 'doctor' && (empty($specialization) || empty($contact))) {
                $error = "Specialization and Contact are required for doctors!";
            } else {

                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $conn->prepare("
                    INSERT INTO users (username, password, role, status) 
                    VALUES (?, ?, ?, 'pending')
                ");
                $stmt->bind_param("sss", $username, $hashed_password, $role);

                if($stmt->execute()) {

                    $user_id = $stmt->insert_id;

                    if($role === 'doctor') {
                        $name = $username;

                        $doc_stmt = $conn->prepare("
                            INSERT INTO doctor (name, specialization, contact, user_id)
                            VALUES (?, ?, ?, ?)
                        ");
                        $doc_stmt->bind_param("sssi", $name, $specialization, $contact, $user_id);
                        $doc_stmt->execute();
                    }

                    $success = "Account created. Await admin approval.";

                } else {
                    $error = "Registration failed.";
                }
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

<style>
* {
    box-sizing: border-box;
}

body {
    margin:0;
    font-family: 'Segoe UI', sans-serif;
    background:#f4f7fb;
}

.container {
    display:flex;
    min-height:100vh;
}

/* LEFT SIDE */
.left {
    flex:1;
    background:linear-gradient(rgba(30,60,114,0.85), rgba(42,82,152,0.85)),
    url('../assets/images/hospital.jpg') center/cover;
    color:white;
    padding:60px;
    display:flex;
    flex-direction:column;
    justify-content:center;
}

.brand {
    font-size:42px;
    font-weight:bold;
    margin-bottom:10px;
}

.tagline {
    font-size:18px;
    margin-bottom:30px;
    opacity:0.9;
}

.features {
    margin-top:20px;
}

.feature {
    margin-bottom:15px;
    font-size:16px;
}

.stats {
    margin-top:40px;
    display:flex;
    gap:30px;
}

.stat {
    font-size:20px;
    font-weight:bold;
}

/* RIGHT SIDE */
.right {
    flex:1;
    display:flex;
    justify-content:center;
    align-items:center;
}

.form-box {
    width:100%;
    max-width:420px;
    background:white;
    padding:35px;
    border-radius:12px;
    box-shadow:0 15px 40px rgba(0,0,0,0.08);
}

h2 {
    text-align:center;
    margin-bottom:5px;
}

.subtitle {
    text-align:center;
    font-size:14px;
    color:#777;
    margin-bottom:20px;
}

input, select {
    width:100%;
    padding:12px;
    margin-bottom:12px;
    border-radius:6px;
    border:1px solid #ddd;
    transition:0.2s;
}

input:focus, select:focus {
    border-color:#667eea;
    outline:none;
}

button {
    width:100%;
    padding:14px;
    background:#667eea;
    color:white;
    border:none;
    border-radius:6px;
    font-size:16px;
    cursor:pointer;
    transition:0.2s;
}

button:hover {
    background:#5a67d8;
}

.alert {
    padding:10px;
    margin-bottom:10px;
    border-radius:5px;
    font-size:14px;
}

.alert-danger {
    background:#ffe5e5;
    color:#b30000;
}

.alert-success {
    background:#e6ffed;
    color:#006b2d;
}

.footer-text {
    text-align:center;
    font-size:13px;
    margin-top:10px;
}
</style>

<script>
function toggleDoctorFields() {
    var role = document.getElementById("role").value;
    var doctorFields = document.getElementById("doctorFields");

    doctorFields.style.display = (role === "doctor") ? "block" : "none";
}
</script>

</head>

<body>

<div class="container">

    <!-- LEFT -->
    <div class="left">
        <div class="brand">🏥 HMIS</div>
        <div class="tagline">
            Empowering hospitals with smarter workflows, faster decisions,
            and better patient care.
        </div>

        <div class="features">
            <div class="feature">✔ Real-time patient tracking</div>
            <div class="feature">✔ Smart prescriptions & pharmacy integration</div>
            <div class="feature">✔ Secure role-based access</div>
            <div class="feature">✔ Centralized medical records</div>
        </div>

        <div class="stats">
            <div class="stat">24/7<br><span style="font-size:12px;">System Access</span></div>
            <div class="stat">100%<br><span style="font-size:12px;">Data Security</span></div>
            <div class="stat">Fast<br><span style="font-size:12px;">Workflows</span></div>
        </div>
    </div>

    <!-- RIGHT -->
    <div class="right">

        <div class="form-box">

            <h2>Create Account</h2>
            <div class="subtitle">Join the system and get started</div>

            <?php if($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <?php if($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>

            <form method="POST">

                <input type="text" name="username" placeholder="Username" required>

                <input type="password" name="password" placeholder="Password" required>

                <input type="password" name="confirm_password" placeholder="Confirm Password" required>

                <select name="role" id="role" onchange="toggleDoctorFields()" required>
                    <option value="">Select Role</option>
                    <option value="doctor">Doctor</option>
                    <option value="pharmacist">Pharmacist</option>
                    <option value="receptionist">Receptionist</option>
                    <option value="cashier">Cashier</option>
                </select>

                <div id="doctorFields" style="display:none;">
                    <input type="text" name="specialization" placeholder="Specialization">
                    <input type="text" name="contact" placeholder="Contact">
                </div>

                <button type="submit" name="register">Create Account</button>

            </form>

            <div class="footer-text">
                Already have an account? <a href="login.php">Login</a>
            </div>

        </div>

    </div>

</div>

</body>
</html>