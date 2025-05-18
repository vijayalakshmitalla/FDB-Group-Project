<?php
include 'auth.php';
include 'db_connect.php';
include 'functions.php';

checkAuth('Donor'); // Ensure the user is a Donor

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['campaign_id'])) {
    $donorId = $_SESSION['user_id'];
    $campaignId = intval($_POST['campaign_id']);

    // Register donor for campaign
    $stmt = $conn->prepare("INSERT INTO campaign_donor (campaign_id, donor_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $campaignId, $donorId);

    if ($stmt->execute()) {
        showModalMessage("Successfully registered for the campaign!");
    } else {
        showModalMessage("Error: Unable to register for campaign. You may already be registered.");
    }

    $stmt->close();
    $conn->close();

    header("Location: donor_dashboard.php"); // Redirect back to dashboard
    exit();
} else {
    echo "Invalid request.";
}
?>
