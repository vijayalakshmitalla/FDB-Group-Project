<?php
include 'auth.php';
include 'functions.php';

checkAuth('Hospital'); // Ensure the user is a Hospital

echo "<header class='header'>
        <h1 class='title'>Hospital Dashboard</h1>
        <nav>
            <ul>
                <li><a href='add_patient.php'>Add New Patient</a></li> <!-- Link to Add Patient Page -->
                <li><a href='logout.php'>Logout</a></li>
            </ul>
        </nav>
      </header>";

echo "<div class='dashboard-container'>";
echo "<h2>Welcome, " . $_SESSION['username'] . "!</h2>";

$hospitalId = $_SESSION['user_id'];
include 'db_connect.php';

// Display Pending Blood Requests
echo "<h3>Pending Blood Requests</h3>";
$stmt = $conn->prepare("SELECT blood_group, amount, request_date, status FROM transfusion_request WHERE hospital_id = ? AND status = 'Pending' ORDER BY request_date DESC");
$stmt->bind_param("i", $hospitalId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<ul class='request-list'>";
    while ($row = $result->fetch_assoc()) {
        echo "<li><strong>Blood Group:</strong> " . $row['blood_group'] . " | <strong>Amount:</strong> " . $row['amount'] . " units | <strong>Date:</strong> " . $row['request_date'] . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No pending blood requests at the moment.</p>";
}
$stmt->close();

// Display Patient Transfusion Status
echo "<h3>Patient Transfusion Status</h3>";
$patientStmt = $conn->prepare("SELECT p.patient_id, p.p_name, t.blood_group, t.amount, t.status FROM patient p JOIN transfusion_request t ON p.patient_id = t.patient_id WHERE t.hospital_id = ?");
$patientStmt->bind_param("i", $hospitalId);
$patientStmt->execute();
$patientResult = $patientStmt->get_result();

if ($patientResult->num_rows > 0) {
    echo "<ul class='patient-list'>";
    while ($patient = $patientResult->fetch_assoc()) {
        echo "<li><strong>Patient:</strong> " . $patient['p_name'] . " | <strong>Blood Group:</strong> " . $patient['blood_group'] . " | <strong>Amount:</strong> " . $patient['amount'] . " units | <strong>Status:</strong> " . $patient['status'] . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No transfusion records available for patients.</p>";
}
$patientStmt->close();

$conn->close();
?>

<div class="form-container">
    <h3>Request Blood</h3>
    <form action="process_transfusion_request.php" method="POST">
        <label for="blood_group">Blood Group:</label>
        <select id="blood_group" name="blood_group" required>
            <option value="">Select Blood Group</option>
            <option value="A+">A+</option>
            <option value="A-">A-</option>
            <option value="B+">B+</option>
            <option value="B-">B-</option>
            <option value="O+">O+</option>
            <option value="O-">O-</option>
            <option value="AB+">AB+</option>
            <option value="AB-">AB-</option>
        </select>

        <label for="amount">Amount (in units):</label>
        <input type="number" id="amount" name="amount" min="1" required>

        <button type="submit" class="submit-btn">Submit Request</button>
    </form>
</div>

<footer>
    <p>&copy; 2024 Blood Donation Management. All rights reserved.</p>
</footer>

<style>
    /* General styling */
    body { font-family: Arial, sans-serif; }
    .header, footer { background-color: #003366; color: white; padding: 1rem; text-align: center; }
    .header { display: flex; justify-content: space-between; align-items: center; }
    .header nav ul { list-style-type: none; padding: 0; display: flex; }
    .header nav ul li { margin-left: 1rem; }
    .header nav ul li a { color: white; text-decoration: none; font-weight: bold; }

    .dashboard-container { padding: 2rem; text-align: center; }
    .form-container { background: #f9f9f9; padding: 2rem; max-width: 400px; margin: auto; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); }
    label, input[type="number"], .submit-btn, select { display: block; width: 100%; margin-top: 1rem; padding: 0.5rem; border-radius: 4px; }
    .submit-btn { background-color: #003366; color: white; font-weight: bold; cursor: pointer; }
    .submit-btn:hover { background-color: #00509e; }

    .request-list, .patient-list { list-style-type: none; padding: 0; }
    .request-list li, .patient-list li { background: #e9ecef; padding: 10px; margin: 5px 0; border-radius: 4px; text-align: left; }
</style>
