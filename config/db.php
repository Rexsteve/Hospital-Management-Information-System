<?php
$host = "localhost";
$user = "root";
$password = "rexsteve";   // put your real password here
$database = "hmis";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>