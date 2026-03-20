<?php
session_start();
include "../config/db.php";

$selected_consultation = $_GET['consultation_id'] ?? '';

// Fetch consultations
$consultations = mysqli_query($conn, "
    SELECT c.consultation_id, p.name AS patient_name, d.name AS doctor_name
    FROM consultation c
    JOIN appointment a ON c.appointment_id = a.appointment_id
    JOIN patient p ON a.patient_id = p.patient_id
    JOIN doctor d ON a.doctor_id = d.doctor_id
");

// Fetch drugs
$drugs = mysqli_query($conn, "SELECT * FROM drug");

if(isset($_POST['save'])) {
    $consultation_id = $_POST['consultation_id'];
    $drug_id = $_POST['drug_id'];
    $quantity = $_POST['quantity'];

    $drug = mysqli_fetch_assoc(mysqli_query($conn, 
        "SELECT quantity FROM drug WHERE drug_id = $drug_id"));

    if(!$drug || $drug['quantity'] < $quantity) {
        $error = "Not enough stock!";
    } else {

        mysqli_query($conn, "
            INSERT INTO prescription (consultation_id, drug_id, quantity)
            VALUES ('$consultation_id', '$drug_id', '$quantity')
        ");

        mysqli_query($conn, "
            UPDATE drug 
            SET quantity = quantity - $quantity 
            WHERE drug_id = $drug_id
        ");

        header("Location: list.php?success=1");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Prescription</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<h3>Add Prescription</h3>

<a href="list.php" class="btn btn-secondary mb-3">← Back</a>

<?php if(isset($error)): ?>
<div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<form method="POST">

    <div class="mb-3">
        <label>Consultation</label>
        <select name="consultation_id" class="form-control" required>
            <option value="">Select Consultation</option>
            <?php while($c = mysqli_fetch_assoc($consultations)): ?>
                <option value="<?= $c['consultation_id'] ?>"
                    <?= $selected_consultation == $c['consultation_id'] ? 'selected' : '' ?>>
                    <?= $c['patient_name'] ?> (Dr. <?= $c['doctor_name'] ?>)
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="mb-3">
        <label>Drug</label>
        <select name="drug_id" class="form-control" required>
            <option value="">Select Drug</option>
            <?php while($dr = mysqli_fetch_assoc($drugs)): ?>
                <option value="<?= $dr['drug_id'] ?>">
                    <?= $dr['name'] ?> (Stock: <?= $dr['quantity'] ?>)
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="mb-3">
        <label>Quantity</label>
        <input type="number" name="quantity" class="form-control" required>
    </div>

    <button type="submit" name="save" class="btn btn-primary">Save Prescription</button>

</form>

</body>
</html>