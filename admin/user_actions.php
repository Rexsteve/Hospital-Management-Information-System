<?php
session_start();
include "../config/db.php";

// Only admin allowed
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Access denied");
}

// Validate input
if(!isset($_GET['action']) || !isset($_GET['id'])) {
    die("Invalid request");
}

$id = (int) $_GET['id'];
$action = $_GET['action'];

// Check user exists
$check = $conn->prepare("SELECT user_id, status FROM users WHERE user_id = ?");
$check->bind_param("i", $id);
$check->execute();
$result = $check->get_result();

if($result->num_rows == 0) {
    die("User not found");
}

$user = $result->fetch_assoc();

switch($action) {

    case "approve":
        $stmt = $conn->prepare("UPDATE users SET status = 'approved' WHERE user_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        break;

    case "reject":
        $stmt = $conn->prepare("UPDATE users SET status = 'rejected' WHERE user_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        break;

    case "pending":
        $stmt = $conn->prepare("UPDATE users SET status = 'pending' WHERE user_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        break;

    default:
        die("Invalid action");
}

header("Location: users_management.php");
exit();