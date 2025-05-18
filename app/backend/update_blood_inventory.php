<?php
include 'db_connect.php';
include 'auth.php';

checkAuth();

function updateBloodInventory($donationId, $bloodGroup, $amount) {
    global $conn;
    $stmt = $conn->prepare("CALL update_blood_inventory(?, ?, ?)");
    $stmt->bind_param("isi", $donationId, $bloodGroup, $amount);

    if ($stmt->execute()) {
        echo "Blood inventory updated successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>
