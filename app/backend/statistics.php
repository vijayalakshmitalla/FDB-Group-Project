<?php
include 'db_connect.php';

// Blood Inventory Levels
$inventoryStmt = $conn->prepare("SELECT SUM(amount) AS total_units FROM blood_inventory");
$inventoryStmt->execute();
$inventoryResult = $inventoryStmt->get_result()->fetch_assoc();
$blood_inventory_levels = $inventoryResult['total_units'];
$inventoryStmt->close();

// Donor Involvement (e.g., total registered donors)
$donorStmt = $conn->prepare("SELECT COUNT(*) AS total_donors FROM donor WHERE d_eligibility_status = 1");
$donorStmt->execute();
$donorResult = $donorStmt->get_result()->fetch_assoc();
$donor_involvement = $donorResult['total_donors'];
$donorStmt->close();

// Blood Usage (e.g., total units used in transfusions)
$usageStmt = $conn->prepare("SELECT SUM(amount) AS total_used FROM transfusion_request WHERE status = 'Completed'");
$usageStmt->execute();
$usageResult = $usageStmt->get_result()->fetch_assoc();
$blood_usage = $usageResult['total_used'];
$usageStmt->close();

// Transfusion Requests (e.g., pending requests)
$requestStmt = $conn->prepare("SELECT COUNT(*) AS pending_requests FROM transfusion_request WHERE status = 'Pending'");
$requestStmt->execute();
$requestResult = $requestStmt->get_result()->fetch_assoc();
$transfusion_requests = $requestResult['pending_requests'];
$requestStmt->close();

// Total Donations
$totalDonationsStmt = $conn->prepare("SELECT COUNT(*) AS total_donations FROM donation");
$totalDonationsStmt->execute();
$totalDonationsResult = $totalDonationsStmt->get_result()->fetch_assoc();
$total_donations = $totalDonationsResult['total_donations'];
$totalDonationsStmt->close();

// Completed Transfusions
$completedTransfusionsStmt = $conn->prepare("SELECT COUNT(*) AS completed_transfusions FROM transfusion_request WHERE status = 'Completed'");
$completedTransfusionsStmt->execute();
$completedTransfusionsResult = $completedTransfusionsStmt->get_result()->fetch_assoc();
$completed_transfusions = $completedTransfusionsResult['completed_transfusions'];
$completedTransfusionsStmt->close();

// Failed Blood Tests
$failedTestsStmt = $conn->prepare("SELECT COUNT(*) AS failed_tests FROM blood_test WHERE test_result = 'Failed'");
$failedTestsStmt->execute();
$failedTestsResult = $failedTestsStmt->get_result()->fetch_assoc();
$failed_blood_tests = $failedTestsResult['failed_tests'];
$failedTestsStmt->close();

// Expired Blood Units
$expiredStmt = $conn->prepare("SELECT COUNT(*) AS expired_units FROM blood_inventory WHERE expiration_date < CURDATE()");
$expiredStmt->execute();
$expiredResult = $expiredStmt->get_result()->fetch_assoc();
$expired_blood = $expiredResult['expired_units'];
$expiredStmt->close();

// Donation Trends (e.g., recent donation count)
$trendsStmt = $conn->prepare("SELECT COUNT(*) AS recent_donations FROM donation WHERE donation_date >= CURDATE() - INTERVAL 30 DAY");
$trendsStmt->execute();
$trendsResult = $trendsStmt->get_result()->fetch_assoc();
$donation_trends = $trendsResult['recent_donations'];
$trendsStmt->close();

// Blood Shortages (e.g., count of low-stock blood groups)
$lowStockThreshold = 5;
$shortageStmt = $conn->prepare("SELECT COUNT(*) AS low_stock_groups FROM blood_inventory WHERE amount < ?");
$shortageStmt->bind_param("i", $lowStockThreshold);
$shortageStmt->execute();
$shortageResult = $shortageStmt->get_result()->fetch_assoc();
$blood_shortages = $shortageResult['low_stock_groups'];
$shortageStmt->close();

// Update the Statistics Table
$updateStmt = $conn->prepare("
    UPDATE statistics 
    SET blood_inventory_levels = ?, donor_involvement = ?, blood_usage = ?, transfusion_requests = ?, 
        total_donations = ?, completed_transfusions = ?, failed_blood_tests = ?,
        expired_blood = ?, donation_trends = ?, blood_shortages = ?
    WHERE stat_id = 1
");
$updateStmt->bind_param(
    "iiiiiiiiii",
    $blood_inventory_levels,
    $donor_involvement,
    $blood_usage,
    $transfusion_requests,
    $total_donations,
    $completed_transfusions,
    $failed_blood_tests,
    $expired_blood,
    $donation_trends,
    $blood_shortages
);

if ($updateStmt->execute()) {
    echo "<script type='text/javascript'>alert('Statistics updated successfully');</script>";
} else {
    echo "<script type='text/javascript'>alert('Error updating statistics');</script>";
}

$updateStmt->close();
$conn->close();
?>
