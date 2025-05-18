<?php
include 'auth.php';
include 'functions.php';

checkAuth('Admin'); // To ensure the user is an Admin

echo "<header class='header'>
        <h1 class='title'>Blood Test Management</h1>
        <nav>
            <ul>
                <li><a href='admin_dashboard.php'>Dashboard</a></li>
                <li><a href='logout.php'>Logout</a></li>
            </ul>
        </nav>
      </header>";
?>

<div class="form-container">
    <h2>Add New Blood Test Result</h2>
    <form action="process_blood_test.php" method="POST">
        <label for="donation_id">Donation ID:</label>
        <input type="number" id="donation_id" name="donation_id" required>

        <label for="test_result">Test Result:</label>
        <input type="text" id="test_result" name="test_result" required>

        <label for="test_date">Test Date:</label>
        <input type="date" id="test_date" name="test_date" required>

        <label for="test_type">Test Type:</label>
        <input type="text" id="test_type" name="test_type">

        <button type="submit" class="submit-btn">Add Test Result</button>
    </form>
</div>

<div class="dashboard-container">
    <h2>Blood Test Results</h2>

    <?php
    include 'db_connect.php';
    $bloodTests = getBloodTests();

    if (count($bloodTests) > 0) {
        echo "<ul class='test-list'>";
        foreach ($bloodTests as $test) {
            echo "<li><strong>Donation ID:</strong> " . $test['donation_id'] . 
                 " | <strong>Test Result:</strong> " . $test['test_result'] . 
                 " | <strong>Test Date:</strong> " . $test['test_date'] .
                 " | <strong>Test Type:</strong> " . $test['test_type'] . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No blood test records available.</p>";
    }
    ?>
</div>

<footer>
    <p>&copy; 2024 Blood Donation Management. All rights reserved.</p>
</footer>

<style>
    /* Basic styling */
    .header, footer { background-color: #003366; color: white; padding: 1rem; text-align: center; }
    .form-container, .dashboard-container { padding: 2rem; max-width: 600px; margin: auto; background: #f9f9f9; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); margin-top: 20px; }
    label, input, .submit-btn { display: block; width: 100%; margin-top: 1rem; padding: 0.5rem; border-radius: 4px; }
    .submit-btn { background-color: #003366; color: white; font-weight: bold; cursor: pointer; }
    .submit-btn:hover { background-color: #00509e; }
    .test-list { list-style-type: none; padding: 0; }
    .test-list li { background: #e9ecef; padding: 10px; margin: 5px 0; border-radius: 4px; text-align: left; }
</style>
