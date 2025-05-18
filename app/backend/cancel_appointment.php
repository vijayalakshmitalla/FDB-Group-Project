<?php
include 'auth.php';
include 'functions.php';
include 'db_connect.php';

checkAuth('Donor'); // Ensure the user is a Donor

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $appointmentId = (int)$_GET['id'];
    $donorId = $_SESSION['user_id'];

    // Update the appointment status to "Cancelled" if it belongs to the current donor
    $stmt = $conn->prepare("UPDATE appointment SET status = 'Cancelled' WHERE appointment_id = ? AND donor_id = ? AND status = 'Scheduled'");
    $stmt->bind_param("ii", $appointmentId, $donorId);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        showModalMessage("Appointment cancelled successfully.");
    } else {
        showModalMessage("Error: Unable to cancel appointment or appointment is not eligible for cancellation.");
    }

    $stmt->close();
    $conn->close();
    redirectTo('donor_dashboard.php'); // Redirect back to the donor dashboard
} else {
    showModalMessage("Invalid appointment ID.");
    redirectTo('donor_dashboard.php');
}
?>
