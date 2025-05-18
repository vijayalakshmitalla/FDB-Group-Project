<?php
include 'db_connect.php';
include 'auth.php';

checkAuth();

function scheduleAppointment($donorId, $appointmentDate) {
    global $conn;
    $stmt = $conn->prepare("CALL schedule_appointment(?, ?)");
    $stmt->bind_param("is", $donorId, $appointmentDate);

    if ($stmt->execute()) {
        echo "Appointment scheduled successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

?>
