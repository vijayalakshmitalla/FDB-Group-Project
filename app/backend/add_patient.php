<?php
include 'auth.php';
include 'functions.php';

// Check if user is an Admin or Hospital
if (!hasRole($_SESSION['user_id'], 'Admin') && !hasRole($_SESSION['user_id'], 'Hospital')) {
    redirectTo('unauthorized.php');
}

// Fetch hospitals if the user is a hospital
$hospitals = getHospitals();

echo "<header class='header'>
        <h1 class='title'>Add New Patient</h1>
        <nav>
            <ul>";

// Display different navigation links based on role
if (hasRole($_SESSION['user_id'], 'Admin')) {
    echo "<li><a href='admin_dashboard.php'>Dashboard</a></li>";
} elseif (hasRole($_SESSION['user_id'], 'Hospital')) {
    echo "<li><a href='hospital_dashboard.php'>Dashboard</a></li>";
}

echo "      <li><a href='logout.php'>Logout</a></li>
            </ul>
        </nav>
      </header>";
?>

<div class="form-container">
    <h2>Patient Information</h2>
    <form action="process_patient.php" method="POST">
        <!-- Patient Name -->
        <label for="p_name">Patient Name:</label>
        <input type="text" id="p_name" name="p_name" required>

        <!-- Age -->
        <label for="age">Age:</label>
        <input type="number" id="age" name="age" min="1" required>

        <!-- Medical Problems -->
        <label for="medical_problems">Medical Problems:</label>
        <input type="text" id="medical_problems" name="medical_problems" required>

        <!-- Transfusion Need -->
        <label for="transfusion_need">Transfusion Need:</label>
        <select id="transfusion_need" name="transfusion_need" required>
            <option value="">Select Need Level</option>
            <option value="Urgent">Urgent</option>
            <option value="Non-Urgent">Non-Urgent</option>
        </select>

        <!-- Blood Group -->
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

        <!-- Hospital Selection -->
        <label for="hospital_id">Hospital:</label>
        <select id="hospital_id" name="hospital_id" required>
        <option value="">Select Hospital</option>
        <?php
        // Populate the hospitals dropdown
        if ($hospitals) {
        foreach ($hospitals as $hospital) {
            // Correct column names for 'hospital_id' and 'hosp_name'
            echo "<option value='{$hospital['hospital_id']}'>{$hospital['hosp_name']}</option>";
        }
        } else {
        echo "<option value=''>No hospitals available</option>";
    }
    ?>
</select>


        <!-- Contact Info -->
        <label for="contact_info">Contact Info:</label>
        <input type="text" id="contact_info" name="contact_info">

        <!-- Submit Button -->
        <button type="submit" class="submit-btn">Add Patient</button>
    </form>
</div>

<footer>
    <p>&copy; 2024 Blood Donation Management. All rights reserved.</p>
</footer>

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
        box-sizing: border-box;
    }

    .submit-btn { 
        background-color: #003366; 
        color: white; 
        font-weight: bold; 
        cursor: pointer; 
        border: none;
        padding: 10px 15px;
        margin-top: 20px;
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
