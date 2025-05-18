<?php
include 'auth.php';
include 'functions.php';

checkAuth('Admin'); // Ensure the user is an Admin

// Fetch Donors from the database
include 'db_connect.php';
$donorStmt = $conn->prepare("SELECT donor_id, donor_name FROM donor WHERE d_eligibility_status = 1");
$donorStmt->execute();
$donors = $donorStmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Record Donation</title>
    <style>
        /* General Styling */
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; min-height: 100vh; display: flex; flex-direction: column; }
        .header, footer { background-color: #003366; color: white; padding: 1rem; text-align: center; }
        .header { display: flex; justify-content: space-between; align-items: center; }
        .header nav ul { list-style-type: none; padding: 0; display: flex; }
        .header nav ul li { margin-left: 1rem; }
        .header nav ul li a { color: white; text-decoration: none; font-weight: bold; }

        .form-container { 
            padding: 2rem; 
            max-width: 500px; 
            margin: 2rem auto; 
            background: #f9f9f9; 
            border-radius: 8px; 
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); 
        }
        label, input, select, .submit-btn { 
            display: block; 
            width: 100%; 
            margin-top: 1rem; 
            padding: 0.5rem; 
            border-radius: 4px; 
        }
        .submit-btn { 
            background-color: #003366; 
            color: white; 
            font-weight: bold; 
            cursor: pointer; 
        }
        .submit-btn:hover { 
            background-color: #00509e; 
        }

        footer {
            margin-top: auto;
            width: 100%;
            text-align: center;
        }
    </style>
</head>
<body>

<header class='header'>
    <h1 class='title'>Record Donation</h1>
    <nav>
        <ul>
            <li><a href='admin_dashboard.php'>Dashboard</a></li>
            <li><a href='logout.php'>Logout</a></li>
        </ul>
    </nav>
</header>

<div class="form-container">
    <h2>Donation and Blood Test Details</h2>
    <form action="process_donation.php" method="POST">
        
        <!-- Donor Selection Dropdown -->
        <label for="donor_id">Select Donor:</label>
        <select id="donor_id" name="donor_id" required>
            <option value="">Select Donor</option>
            <?php while ($donor = $donors->fetch_assoc()): ?>
                <option value="<?php echo $donor['donor_id']; ?>">
                    <?php echo $donor['donor_name']; ?>
                </option>
            <?php endwhile; ?>
        </select>

        <!-- Donation Details -->
        <label for="donation_date">Donation Date:</label>
        <input type="date" id="donation_date" name="donation_date" required>

        <label for="health_condition">Health Condition:</label>
        <input type="text" id="health_condition" name="health_condition" required>

        <!-- Blood Test Details -->
        <h3>Blood Test Information</h3>
        <label for="test_result">Test Result:</label>
        <input type="text" id="test_result" name="test_result" required>

        <label for="test_date">Test Date:</label>
        <input type="date" id="test_date" name="test_date" required>

        <button type="submit" class="submit-btn">Record Donation</button>
    </form>
</div>

<footer>
    <p>&copy; 2024 Blood Donation Management. All rights reserved.</p>
</footer>

<?php
$donorStmt->close();
$conn->close();
?>

</body>
</html>