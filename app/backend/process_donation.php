<?php
include 'db_connect.php';
include 'functions.php';

$donor_id = $_POST['donor_id'];
$donation_date = $_POST['donation_date'];
$health_condition = $_POST['health_condition'];
$status = 'Completed';  // Assume status is completed

$test_result = $_POST['test_result'];
$test_date = $_POST['test_date'];

// Insert into Donation Table
if (addDonation($donor_id, $donation_date, $health_condition, $status)) {
    $donation_id = $conn->insert_id; // Get the inserted donation ID

    // Insert into Blood Test Table using the donation ID
    if (addBloodTest($donation_id, $test_result, $test_date)) {
        echo "<script>alert('Donation and blood test recorded successfully'); window.location.href = 'admin_dashboard.php';</script>";
    } else {
        echo "<script>alert('Error recording blood test'); window.history.back();</script>";
    }
} else {
    echo "<script>alert('Error recording donation'); window.history.back();</script>";
}
?>
