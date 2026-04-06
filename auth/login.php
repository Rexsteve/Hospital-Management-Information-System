<?php
session_start();
include "../config/db.php";

$error = '';

if(isset($_POST['login'])) {

    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    // ✅ Only allow approved users
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND status = 'approved'");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows == 1) {

        $user = $result->fetch_assoc();

        if(password_verify($password, $user['password'])) {

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            $_SESSION['doctor_id'] = 0;

            // Get doctor_id if doctor
            if($user['role'] == 'doctor') {
                $doc = $conn->prepare("SELECT doctor_id FROM doctor WHERE user_id = ?");
                $doc->bind_param("i", $user['user_id']);
                $doc->execute();
                $res = $doc->get_result();

                if($res && $res->num_rows > 0) {
                    $d = $res->fetch_assoc();
                    $_SESSION['doctor_id'] = (int)$d['doctor_id'];
                }
            }

            header("Location: ../dashboard.php");
            exit();

        } else {
            $error = "Invalid credentials!";
        }

    } else {
        $error = "Account not approved or does not exist.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login - HMIS</title>

<style>
* {
    box-sizing: border-box;
}

body {
    margin:0;
    font-family: 'Segoe UI', sans-serif;
    background:#eef2f7;
}

.container {
    display:flex;
    min-height:100vh;
}

/* LEFT SIDE */
.left {
    flex:1;
    background:linear-gradient(rgba(20,40,90,0.85), rgba(40,80,160,0.85)),
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

.quote {
    font-style:italic;
    opacity:0.85;
    margin-top:20px;
}

.features {
    margin-top:30px;
}

.feature {
    margin-bottom:12px;
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
    padding:40px;
    border-radius:14px;
    box-shadow:0 20px 50px rgba(0,0,0,0.1);
}

h2 {
    text-align:center;
    margin-bottom:5px;
}

.subtitle {
    text-align:center;
    color:#666;
    font-size:14px;
    margin-bottom:25px;
}

input {
    width:100%;
    padding:13px;
    margin-bottom:15px;
    border-radius:6px;
    border:1px solid #ddd;
    transition:0.2s;
}

input:focus {
    border-color:#5a67d8;
    outline:none;
    box-shadow:0 0 0 2px rgba(90,103,216,0.1);
}

button {
    width:100%;
    padding:14px;
    background:#5a67d8;
    color:white;
    border:none;
    border-radius:6px;
    font-size:16px;
    cursor:pointer;
    transition:0.2s;
}

button:hover {
    background:#434190;
}

.alert {
    padding:12px;
    margin-bottom:15px;
    border-radius:6px;
    font-size:14px;
}

.alert-danger {
    background:#ffe5e5;
    color:#b30000;
}

.footer {
    text-align:center;
    margin-top:15px;
    font-size:14px;
}
</style>

</head>

<body>

<div class="container">

    <!-- LEFT SIDE -->
    <div class="left">
        <div class="brand">🏥 HMIS</div>
        <div class="tagline">
            Welcome back. Continue delivering better healthcare with smarter systems.
        </div>

        <div class="features">
            <div class="feature">✔ Secure patient records</div>
            <div class="feature">✔ Efficient doctor workflows</div>
            <div class="feature">✔ Integrated pharmacy & billing</div>
            <div class="feature">✔ Role-based system control</div>
        </div>

        <div class="quote">
            “Good healthcare begins with good systems.”
        </div>
    </div>

    <!-- RIGHT SIDE -->
    <div class="right">

        <div class="form-box">

            <h2>Welcome Back</h2>
            <div class="subtitle">Login to access your dashboard</div>

            <?php if($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST">

                <input type="text" name="username" placeholder="Username" required>

                <input type="password" name="password" placeholder="Password" required>

                <button type="submit" name="login">Login</button>

            </form>

            <div class="footer">
                Don’t have an account? <a href="register.php">Register</a>
            </div>

        </div>

    </div>

</div>

</body>
</html>