<?php
include 'db_connect.php';
include 'auth.php';

checkAuth('Admin');

function addDonor($name, $address, $bloodGroup, $healthHistory, $eligibility) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO donor (donor_name, d_address, d_blood_group, d_health_history, d_eligibility_status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $name, $address, $bloodGroup, $healthHistory, $eligibility);

    if ($stmt->execute()) {
        echo "New donor added successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>
