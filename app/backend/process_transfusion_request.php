<?php
include 'db_connect.php';
include 'functions.php';
include 'auth.php';

checkAuth('Hospital'); // Ensure only hospitals can make requests

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hospitalId = $_SESSION['user_id']; // Assuming the hospital's ID is stored in the user session
    $bloodGroup = sanitizeInput($_POST['blood_group']);
    $amount = (int)$_POST['amount'];

    // Use the addTransfusionRequest function to add the request to the database
    if (addTransfusionRequest($hospitalId, $bloodGroup, $amount)) {
        showModalMessage("Blood request submitted successfully.");
    } else {
        showModalMessage("Error submitting blood request. Please try again.");
    }

    // Redirect back to the hospital dashboard
    redirectTo('hospital_dashboard.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Blood Request</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<header class="header">
    <h1 class="title">Submit Blood Request</h1>
    <nav>
        <ul>
            <li><a href="hospital_dashboard.php">Dashboard</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
</header>

<div class="dashboard-container">
    <h2>Submit a Blood Transfusion Request</h2>
    <p>Fill out the details below to request a specific blood type and amount.</p>
</div>

<footer>
    <p>&copy; 2024 Blood Donation Management. All rights reserved.</p>
</footer>

<style>
    /* General styling */
    body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
    .header, footer { background-color: #003366; color: white; padding: 1rem; text-align: center; }
    .header { display: flex; justify-content: space-between; align-items: center; }
    .header nav ul { list-style-type: none; padding: 0; display: flex; }
    .header nav ul li { margin-left: 1rem; }
    .header nav ul li a { color: white; text-decoration: none; font-weight: bold; }

    .dashboard-container { padding: 2rem; text-align: center; }
</style>

</body>
</html>
