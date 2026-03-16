<?php
// Detect current folder for active state
$current_folder = basename(dirname($_SERVER['PHP_SELF']));
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-2 p-0 sidebar">
            <div class="p-3">
                <h4 class="text-center mb-4">
                    <i class="bi bi-hospital"></i> HMIS
                </h4>
                <hr class="bg-white">

                <a href="../dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>

                <a href="../patients/list.php" class="<?= $current_folder == 'patients' ? 'active' : '' ?>">
                    <i class="bi bi-people"></i> Patients
                </a>

                <a href="../doctors/list.php" class="<?= $current_folder == 'doctors' ? 'active' : '' ?>">
                    <i class="bi bi-person-badge"></i> Doctors
                </a>

                <a href="../appointments/list.php" class="<?= $current_folder == 'appointments' ? 'active' : '' ?>">
                    <i class="bi bi-calendar-check"></i> Appointments
                </a>

                <a href="../consultations/list.php" class="<?= $current_folder == 'consultations' ? 'active' : '' ?>">
                    <i class="bi bi-chat-dots"></i> Consultations
                </a>

                <a href="../pharmacy/list.php" class="<?= $current_folder == 'pharmacy' ? 'active' : '' ?>">
                    <i class="bi bi-capsule"></i> Pharmacy
                </a>

                <a href="../invoice/list.php" class="<?= $current_folder == 'invoice' ? 'active' : '' ?>">
                    <i class="bi bi-receipt"></i> Invoices
                </a>

                <a href="../payment/list.php" class="<?= $current_folder == 'payment' ? 'active' : '' ?>">
                    <i class="bi bi-credit-card"></i> Payments
                </a>

                <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                    <hr class="bg-white">

                    <a href="../reports/index.php" class="<?= $current_folder == 'reports' ? 'active' : '' ?>">
                        <i class="bi bi-bar-chart"></i> Reports
                    </a>
                <?php endif; ?>

                <hr class="bg-white">

                <a href="../logout.php" class="text-danger">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-10 main-content">