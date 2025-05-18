<?php
include 'auth.php';
include 'functions.php';

checkAuth('Admin'); // Ensure the user is an Admin

echo "<header class='header'>
        <h1 class='title'>Manage Campaigns</h1>
        <nav>
            <ul>
                <li><a href='admin_dashboard.php'>DASHBOARD</a></li>
                <li><a href='logout.php'>LOGOUT</a></li>
            </ul>
        </nav>
      </header>";

// Database connection
include 'db_connect.php';

// Handle form submission for creating a new campaign
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $campaignName = sanitizeInput($_POST['camp_name']);
    $campaignLocation = sanitizeInput($_POST['camp_location']);
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];
    
    // Insert new campaign
    $stmt = $conn->prepare("INSERT INTO campaign (camp_name, camp_location, start_date, end_date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $campaignName, $campaignLocation, $startDate, $endDate);
    $stmt->execute();
    $stmt->close();
}

// Handle delete request
if (isset($_GET['delete_id'])) {
    $campaignId = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM campaign WHERE campaign_id = ?");
    $stmt->bind_param("i", $campaignId);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_campaigns.php");
    exit();
}

// Fetch all campaigns
$result = $conn->query("SELECT * FROM campaign ORDER BY start_date DESC");
?>

<div class="content">
    <h2>Create a New Campaign</h2>
    <form action="manage_campaigns.php" method="POST" class="campaign-form">
        <label for="camp_name">Campaign Name:</label>
        <input type="text" id="camp_name" name="camp_name" required>
        
        <label for="camp_location">Location:</label>
        <input type="text" id="camp_location" name="camp_location" required>
        
        <label for="start_date">Start Date:</label>
        <input type="date" id="start_date" name="start_date" required>
        
        <label for="end_date">End Date:</label>
        <input type="date" id="end_date" name="end_date" required>
        
        <button type="submit">Create Campaign</button>
    </form>
    
    <h2>Current Campaigns</h2>
    <div class="campaign-list">
        <?php while ($campaign = $result->fetch_assoc()): ?>
            <div class="campaign-card">
                <h3><?php echo htmlspecialchars($campaign['camp_name']); ?></h3>
                <p><strong>Location:</strong> <?php echo htmlspecialchars($campaign['camp_location']); ?></p>
                <p><strong>Start Date:</strong> <?php echo htmlspecialchars($campaign['start_date']); ?></p>
                <p><strong>End Date:</strong> <?php echo htmlspecialchars($campaign['end_date']); ?></p>
                <a href="?delete_id=<?php echo $campaign['campaign_id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this campaign?');">Delete</a>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<footer>
    <p>&copy; 2024 Blood Donation Management. All rights reserved.</p>
</footer>

<style>
    /* General styling */
    body { font-family: Arial, sans-serif; margin: 0; padding: 0; min-height: 100vh; display: flex; flex-direction: column; }
    .header, footer { background-color: #003366; color: white; padding: 1rem; text-align: center; }
    .header { display: flex; justify-content: space-between; align-items: center; }
    .header nav ul { list-style-type: none; padding: 0; display: flex; }
    .header nav ul li { margin-left: 1rem; }
    .header nav ul li a { color: white; text-decoration: none; font-weight: bold; }

    .content { padding: 2rem; text-align: center; flex: 1; }

    /* Form styling */
    .campaign-form { background: #f9f9f9; padding: 2rem; border-radius: 8px; max-width: 400px; margin: auto; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); }
    label, input, button { display: block; width: 100%; margin-top: 1rem; }
    input, button { padding: 0.5rem; border-radius: 4px; }
    button { background-color: #003366; color: white; font-weight: bold; cursor: pointer; }
    button:hover { background-color: #00509e; }

    /* Campaign list styling */
    .campaign-list { display: grid; gap: 20px; margin-top: 2rem; }
    .campaign-card { background: #f9f9f9; padding: 1rem; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); text-align: left; }
    .campaign-card h3 { margin: 0; }
    .delete-btn { color: red; font-weight: bold; text-decoration: none; }
    .delete-btn:hover { text-decoration: underline; }

    /* Footer styling */
    footer {
        margin-top: auto;
        width: 100%;
        text-align: center;
    }
</style>
