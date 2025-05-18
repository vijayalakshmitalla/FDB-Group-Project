<?php
include 'auth.php';
include 'functions.php';

checkAuth('Admin'); // Ensure the user is an Admin

// Fetch form data
$donationId = $_POST['donation_id'];
$testResult = $_POST['test_result'];
$testDate = $_POST['test_date'];
$testType = $_POST['test_type'];

// Add the blood test result
if (addBloodTest($donationId, $testResult, $testDate, $testType)) {
    echo "<script type='text/javascript'>alert('Blood test result added successfully.'); window.location.href = 'blood_test_management.php';</script>";
} else {
    echo "<script type='text/javascript'>alert('Failed to add blood test result. Please try again.'); window.location.href = 'blood_test_management.php';</script>";
}
?>
