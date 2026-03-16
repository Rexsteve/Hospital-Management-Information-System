<?php
session_start();
include "../config/db.php";

if(!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

/* Fetch consultations */
$consultations = $conn->query("
    SELECT consultation.consultation_id,
           patient.name AS patient_name
    FROM consultation
    JOIN appointment ON consultation.appointment_id = appointment.appointment_id
    JOIN patient ON appointment.patient_id = patient.patient_id
");

if(isset($_POST['submit'])) {

    $consultation_id = $_POST['consultation_id'];
    $amount = $_POST['amount'];
    $method = $_POST['payment_method'];

    $sql = "INSERT INTO billing (consultation_id, amount, payment_method)
            VALUES (?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ids", $consultation_id, $amount, $method);

    if($stmt->execute()) {
        echo "Bill Recorded Successfully!";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Bill</title>
</head>
<body>

<h2>Add Bill</h2>

<form method="POST">

    Consultation:
    <select name="consultation_id" required>
        <option value="">Select Consultation</option>
        <?php while($c = $consultations->fetch_assoc()): ?>
            <option value="<?php echo $c['consultation_id']; ?>">
                <?php echo $c['patient_name']; ?>
            </option>
        <?php endwhile; ?>
    </select>
    <br><br>

    Amount: <input type="number" name="amount" step="0.01" required><br><br>

    Payment Method:
    <select name="payment_method" required>
        <option value="Cash">Cash</option>
        <option value="Mpesa">Mpesa</option>
        <option value="Card">Card</option>
    </select>
    <br><br>

    <button type="submit" name="submit">Save Bill</button>

</form>

<br>
<a href="list.php">View Bills</a>

</body>
</html>
