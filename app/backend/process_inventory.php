<?php
include 'db_connect.php';
include 'auth.php';
include 'functions.php';

checkAuth('Staff'); // Ensure only staff can add inventory

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bloodGroup = sanitizeInput($_POST['blood_group']);
    $amount = (int)$_POST['amount'];
    $storageDate = sanitizeInput($_POST['storage_date']);
    $expirationDate = sanitizeInput($_POST['expiration_date']);

    $stmt = $conn->prepare("INSERT INTO blood_inventory (blood_group, amount, storage_date, expiration_date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("siss", $bloodGroup, $amount, $storageDate, $expirationDate);

    if ($stmt->execute()) {
        showModalMessage("Blood unit added to inventory successfully.");
    } else {
        showModalMessage("Error adding blood unit to inventory: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();

    // Redirect back to the staff dashboard
    redirectTo('staff_dashboard.php');
}
?>
