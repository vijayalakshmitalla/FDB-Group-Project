<?php
include 'auth.php';
include 'functions.php';

checkAuth('Donor'); // Ensure the user is a Donor

echo "<header class='header'>
        <h1 class='title'>Donor Dashboard</h1>
        <nav>
            <ul>
                <li><a href='donor_campaigns.php'>CAMPAIGNS</a></li> 
                <li><a href='logout.php'>LOGOUT</a></li>
            </ul>
        </nav>
      </header>";

echo "<div class='dashboard-container'>";
echo "<h2>Welcome, " . $_SESSION['username'] . "!</h2>";

$donorId = $_SESSION['user_id'];
include 'db_connect.php';

// Display Appointment History and Upcoming Appointments
echo "<h3>Your Appointment History and Upcoming Appointments</h3>";
$stmt = $conn->prepare("SELECT appointment_id, appointment_date, status FROM appointment WHERE donor_id = ? ORDER BY appointment_date DESC");
$stmt->bind_param("i", $donorId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<ul class='appointment-list'>";
    while ($row = $result->fetch_assoc()) {
        $isUpcoming = (strtotime($row['appointment_date']) >= strtotime(date('Y-m-d'))) && $row['status'] == 'Scheduled';
        echo "<li><strong>Date:</strong> " . $row['appointment_date'] . 
             " | <strong>Status:</strong> " . $row['status'];
        
        if ($isUpcoming) {
            echo " | <a href='cancel_appointment.php?id=" . $row['appointment_id'] . "' class='cancel-link'>Cancel</a>";
        }
        echo "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>You have no appointment history. Schedule one now!</p>";
}

$stmt->close();

// Display Donation History
echo "<h3>Your Donation History</h3>";
$donations = getDonations($donorId);

if (count($donations) > 0) {
    echo "<ul class='donation-list'>";
    foreach ($donations as $donation) {
        echo "<li><strong>Date:</strong> " . $donation['donation_date'] . 
             " | <strong>Health Condition:</strong> " . $donation['health_condition'] . 
             " | <strong>Status:</strong> " . $donation['status'] . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>You have no donation history recorded.</p>";
}

$conn->close();
?>

<!-- Schedule Appointment Form -->
<div class="form-container">
    <h3>Schedule an Appointment</h3>
    <form action="process_appointment.php" method="POST">
        <label for="appointment_date">Choose a Date:</label>
        <input type="date" id="appointment_date" name="appointment_date" required>
        
        <button type="submit" class="submit-btn">Schedule Appointment</button>
    </form>
</div>

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
    .form-container { background: #f9f9f9; padding: 2rem; max-width: 400px; margin: auto; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); }
    label, input[type="date"], .submit-btn { display: block; width: 100%; margin-top: 1rem; padding: 0.5rem; border-radius: 4px; }
    .submit-btn { background-color: #003366; color: white; font-weight: bold; cursor: pointer; }
    .submit-btn:hover { background-color: #00509e; }

    .appointment-list, .donation-list { list-style-type: none; padding: 0; }
    .appointment-list li, .donation-list li { background: #e9ecef; padding: 10px; margin: 5px 0; border-radius: 4px; text-align: left; }
    .cancel-link { color: red; text-decoration: none; font-weight: bold; margin-left: 10px; }
    .cancel-link:hover { text-decoration: underline; }
</style>
