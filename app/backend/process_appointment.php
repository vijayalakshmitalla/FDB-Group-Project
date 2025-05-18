<?php
include 'auth.php';
include 'functions.php';
include 'db_connect.php';

checkAuth('Donor'); // Ensure the user is a Donor

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointmentDate = sanitizeInput($_POST['appointment_date']);
    $donorId = $_SESSION['user_id'];

    // Check if the selected date is in the future
    if (strtotime($appointmentDate) < strtotime(date('Y-m-d'))) {
        showModalMessage("Error: Please choose a future date.");
        redirectTo('donor_dashboard.php');
        exit();
    }

    // Insert the appointment into the database
    $stmt = $conn->prepare("INSERT INTO appointment (donor_id, appointment_date, status) VALUES (?, ?, 'Scheduled')");
    $stmt->bind_param("is", $donorId, $appointmentDate);

    if ($stmt->execute()) {
        showModalMessage("Appointment scheduled successfully.");
    } else {
        showModalMessage("Error scheduling appointment: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();

    // Redirect back to the donor dashboard
    redirectTo('donor_dashboard.php');
}
?>
