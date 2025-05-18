<?php
include 'auth.php';
include 'functions.php';

checkAuth('Donor'); // Ensure the user is a Donor

echo "<header class='header'>
        <h1 class='title'>Campaigns</h1>
        <nav>
            <ul>
                <li><a href='donor_dashboard.php'>Dashboard</a></li>
                <li><a href='donor_campaigns.php'>Campaigns</a></li>
                <li><a href='logout.php'>Logout</a></li>
            </ul>
        </nav>
      </header>";

echo "<div class='dashboard-container'>";
echo "<h2>Available Campaigns</h2>";

// Database Connection
include 'db_connect.php';

$donorId = $_SESSION['user_id'];

// Fetch available campaigns
$stmt = $conn->prepare("SELECT campaign_id, camp_name, camp_location, start_date, end_date FROM campaign WHERE end_date >= CURDATE()");
$stmt->execute();
$campaigns = $stmt->get_result();

// Fetch donor's registered campaigns
$registeredStmt = $conn->prepare("SELECT campaign_id FROM campaign_donor WHERE donor_id = ?");
$registeredStmt->bind_param("i", $donorId);
$registeredStmt->execute();
$registeredResult = $registeredStmt->get_result();
$registeredCampaigns = [];
while ($row = $registeredResult->fetch_assoc()) {
    $registeredCampaigns[] = $row['campaign_id'];
}
$registeredStmt->close();

if ($campaigns->num_rows > 0) {
    echo "<ul class='campaign-list'>";
    while ($row = $campaigns->fetch_assoc()) {
        $isRegistered = in_array($row['campaign_id'], $registeredCampaigns);
        echo "<li><strong>Campaign:</strong> " . htmlspecialchars($row['camp_name']) . 
             "<br><strong>Location:</strong> " . htmlspecialchars($row['camp_location']) . 
             "<br><strong>Dates:</strong> " . $row['start_date'] . " to " . $row['end_date'];

        if ($isRegistered) {
            echo "<p class='registered'>Already Registered</p>";
        } else {
            echo "<form action='register_for_campaign.php' method='POST' style='display:inline;'>
                    <input type='hidden' name='campaign_id' value='" . $row['campaign_id'] . "'>
                    <button type='submit' class='register-btn'>Register</button>
                  </form>";
        }
        echo "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No available campaigns at this time.</p>";
}

$stmt->close();
$conn->close();
echo "</div>";
?>

<footer>
    <p>&copy; 2024 Blood Donation Management. All rights reserved.</p>
</footer>

<style>
    body { font-family: Arial, sans-serif; }
    .header, footer { background-color: #003366; color: white; padding: 1rem; text-align: center; }
    .header { display: flex; justify-content: space-between; align-items: center; }
    .header nav ul { list-style-type: none; padding: 0; display: flex; }
    .header nav ul li { margin-left: 1rem; }
    .header nav ul li a { color: white; text-decoration: none; font-weight: bold; }

    .dashboard-container { padding: 2rem; text-align: center; }
    .campaign-list { list-style-type: none; padding: 0; }
    .campaign-list li { background: #f9f9f9; padding: 1.5rem; margin: 10px 0; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); text-align: left; }
    .registered { color: green; font-weight: bold; margin-top: 10px; }
    .register-btn { background-color: #003366; color: white; font-weight: bold; padding: 0.5rem; border-radius: 4px; cursor: pointer; border: none; }
    .register-btn:hover { background-color: #00509e; }

    footer { margin-top: auto; width: 100%; text-align: center; }
</style>
