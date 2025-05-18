<?php
include 'auth.php';
include 'functions.php';

checkAuth('Staff'); // Ensure the user is a Staff

echo "<header class='header'>
        <h1 class='title'>Staff Dashboard</h1>
        <nav>
            <ul>
                <li><a href='logout.php'>Logout</a></li>
            </ul>
        </nav>
      </header>";

echo "<div class='dashboard-container'>";
echo "<h2>Welcome, " . $_SESSION['username'] . "!</h2>";

// Display Blood Inventory
echo "<h3>Current Blood Inventory</h3>";

// Fetch blood inventory from the database
include 'db_connect.php';

$stmt = $conn->prepare("SELECT blood_id, blood_group, amount, storage_date, expiration_date FROM blood_inventory ORDER BY expiration_date ASC");
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<ul class='inventory-list'>";
    while ($row = $result->fetch_assoc()) {
        $status = (strtotime($row['expiration_date']) < strtotime('+7 days')) ? 'Expiring Soon' : 'Available';
        echo "<li><strong>Blood Group:</strong> " . $row['blood_group'] . 
             " | <strong>Amount:</strong> " . $row['amount'] . " units | <strong>Storage Date:</strong> " . 
             $row['storage_date'] . " | <strong>Expiration Date:</strong> " . $row['expiration_date'] . 
             " | <strong>Status:</strong> " . $status . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No blood units available in inventory.</p>";
}

$stmt->close();
$conn->close();
?>

<!-- Form to Add New Blood Inventory Units -->
<div class="form-container">
    <h3>Add Blood to Inventory</h3>
    <form action="process_inventory.php" method="POST">
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

        <label for="storage_date">Storage Date:</label>
        <input type="date" id="storage_date" name="storage_date" required>

        <label for="expiration_date">Expiration Date:</label>
        <input type="date" id="expiration_date" name="expiration_date" required>

        <button type="submit" class="submit-btn">Add to Inventory</button>
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
    label, input[type="number"], input[type="date"], .submit-btn, select { display: block; width: 100%; margin-top: 1rem; padding: 0.5rem; border-radius: 4px; }
    .submit-btn { background-color: #003366; color: white; font-weight: bold; cursor: pointer; }
    .submit-btn:hover { background-color: #00509e; }

    .inventory-list { list-style-type: none; padding: 0; }
    .inventory-list li { background: #e9ecef; padding: 10px; margin: 5px 0; border-radius: 4px; text-align: left; }
</style>
