<?php
include 'auth.php';
include 'functions.php';

checkAuth('Admin'); // Ensure the user is an Admin

echo "<header class='header'>
        <h1 class='title'>Admin Dashboard</h1>
        <nav>
            <ul>
                <li><a href='manage_campaigns.php'>CAMPAIGNS</a></li>
                <li><a href='add_patient.php'>ADD PATIENT</a></li>
                <li><a href='add_donation.php'>RECORD DONATION</a></li>
                <li><a href='logout.php'>LOGOUT</a></li>
            </ul>
        </nav>
      </header>";

echo "<div class='dashboard-container'>";
echo "<h2>Welcome, " . $_SESSION['username'] . "!</h2>";

// Database Connection
include 'db_connect.php';

// Update Pending Transfusion Requests in Statistics
updatePendingTransfusionRequests();

// Fetch Data from Statistics Table
$statisticsStmt = $conn->prepare("SELECT blood_inventory_levels, blood_usage, transfusion_requests, expired_blood, blood_shortages FROM statistics LIMIT 1");
$statisticsStmt->execute();
$statisticsResult = $statisticsStmt->get_result();
$statisticsRow = $statisticsResult->fetch_assoc();
$statisticsStmt->close();

// Fetch Blood Test Results
$bloodTestStmt = $conn->prepare("
    SELECT d.donor_id, d.donor_name, bt.test_result, bt.test_date, d.d_eligibility_status 
    FROM blood_test bt 
    JOIN donation dn ON bt.donation_id = dn.donation_id 
    JOIN donor d ON dn.donor_id = d.donor_id 
    ORDER BY bt.test_date DESC LIMIT 5
");
$bloodTestStmt->execute();
$bloodTestResults = $bloodTestStmt->get_result();

// Close the database connection
$conn->close();
?>

<div class="dashboard-content">
    <!-- Blood Inventory Section -->
    <div class="card">
        <div class="card-content">
            <div class="card-info">
                <h3>Blood Inventory Levels</h3>
                <p>Monitor the current levels of blood inventory across all blood groups.</p>
            </div>
            <div class="card-count">
                <p><strong><?php echo $statisticsRow['blood_inventory_levels']; ?></strong> units available</p>
            </div>
        </div>
    </div>

    <!-- Blood Usage Section -->
    <div class="card">
        <div class="card-content">
            <div class="card-info">
                <h3>Blood Usage</h3>
                <p>Total units used in completed donations and transfusions.</p>
            </div>
            <div class="card-count">
                <p><strong><?php echo $statisticsRow['blood_usage']; ?></strong> units</p>
            </div>
        </div>
    </div>

    <!-- Transfusion Requests Section -->
    <div class="card">
        <div class="card-content">
            <div class="card-info">
                <h3>Pending Transfusion Requests</h3>
                <p>Number of pending transfusion requests awaiting approval.</p>
            </div>
            <div class="card-count">
                <p><strong><?php echo $statisticsRow['transfusion_requests']; ?></strong> requests</p>
            </div>
        </div>
    </div>

    <!-- Expired Blood Units Section -->
    <div class="card">
        <div class="card-content">
            <div class="card-info">
                <h3>Expired Blood Units</h3>
                <p>Units of blood that have reached expiration and need disposal.</p>
            </div>
            <div class="card-count">
                <p><strong><?php echo $statisticsRow['expired_blood']; ?></strong> units</p>
            </div>
        </div>
    </div>

    <!-- Blood Shortages Section -->
    <div class="card">
        <div class="card-content">
            <div class="card-info">
                <h3>Blood Shortages</h3>
                <p>Blood groups that are below minimum stock levels.</p>
            </div>
            <div class="card-count">
                <p><strong><?php echo $statisticsRow['blood_shortages']; ?></strong> shortages</p>
            </div>
        </div>
    </div>

    <!-- Blood Test Results Section -->
    <div class="card">
        <div class="card-content">
            <div class="card-info">
                <h3>Blood Test Results</h3>
                <p>Recent blood test results and eligibility statuses of donors.</p>
                <?php if ($bloodTestResults->num_rows > 0): ?>
                    <ul class="blood-test-list">
                        <?php while ($row = $bloodTestResults->fetch_assoc()): ?>
                            <li>
                                <strong>Donor:</strong> <?php echo $row['donor_name']; ?> |
                                <strong>Result:</strong> <?php echo $row['test_result']; ?> |
                                <strong>Date:</strong> <?php echo $row['test_date']; ?> |
                                <strong>Eligibility:</strong> <?php echo $row['d_eligibility_status'] ? 'Eligible' : 'Ineligible'; ?>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <p>No recent blood test results available.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<footer>
    <p>&copy; 2024 Blood Donation Management. All rights reserved.</p>
</footer>

<style>
    body { font-family: Arial, sans-serif; margin: 0; padding: 0; min-height: 100vh; display: flex; flex-direction: column; }
    .header, footer { background-color: #003366; color: white; padding: 1rem; text-align: center; }
    .header { display: flex; justify-content: space-between; align-items: center; }
    .header nav ul { list-style-type: none; padding: 0; display: flex; }
    .header nav ul li { margin-left: 1rem; }
    .header nav ul li a { color: white; text-decoration: none; font-weight: bold; }

    .dashboard-container { padding: 2rem; text-align: center; flex: 1; }
    
    /* Dashboard Content Layout */
    .dashboard-content { 
        display: flex; 
        flex-direction: column; 
        gap: 20px; 
        margin-top: 2rem; 
    }

    /* Card Styling */
    .card { 
        background: #f9f9f9; 
        border-radius: 8px; 
        padding: 1.5rem; 
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); 
    }
    .card-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .card-info {
        text-align: left;
    }
    .card-count {
        text-align: right;
        font-weight: bold;
        color: #333;
    }
    .blood-test-list { list-style-type: none; padding: 0; margin: 1rem 0; }
    .blood-test-list li { background: #e9ecef; padding: 10px; margin: 5px 0; border-radius: 4px; text-align: left; }

    .card h3 {
        font-size: 1.2em;
        margin: 0 0 0.5rem 0;
    }
    .card p {
        margin: 0.5rem 0;
        font-size: 0.9em;
        color: #555;
    }

    /* Footer fixed at bottom */
    footer {
        margin-top: auto;
        width: 100%;
        text-align: center;
    }
</style>
