<?php
include 'auth.php';
include 'functions.php';
checkAuth('Admin');

$action = $_POST['action'] ?? '';

if ($action === 'create') {
    // Create a new campaign
    $name = sanitizeInput($_POST['camp_name']);
    $location = sanitizeInput($_POST['camp_location']);
    $startDate = sanitizeInput($_POST['start_date']);
    $endDate = sanitizeInput($_POST['end_date']);
    $donorInvolvement = sanitizeInput($_POST['donor_involvement']);

    if (createCampaign($name, $location, $startDate, $endDate, $donorInvolvement)) {
        showModalMessage("Campaign created successfully!");
    } else {
        showModalMessage("Failed to create campaign.");
    }

} elseif ($action === 'update') {
    // Update an existing campaign
    $campaignId = (int)$_POST['campaign_id'];
    // Fetch and prefill campaign data
    // Here you can create an additional form to handle updates

} elseif ($action === 'delete') {
    // Delete a campaign
    $campaignId = (int)$_POST['campaign_id'];
    if (deleteCampaign($campaignId)) {
        showModalMessage("Campaign deleted successfully!");
    } else {
        showModalMessage("Failed to delete campaign.");
    }
}

// Redirect back to the admin dashboard
redirectTo('admin_dashboard.php');
?>
