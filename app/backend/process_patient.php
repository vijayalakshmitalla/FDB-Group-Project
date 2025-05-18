<?php
include 'auth.php';
include 'functions.php';

// Check if user is an Admin or Hospital
if (!hasRole($_SESSION['user_id'], 'Admin') && !hasRole($_SESSION['user_id'], 'Hospital')) {
    redirectTo('unauthorized.php');
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize user inputs to prevent SQL injection
    $patientId = isset($_POST['patient_id']) ? sanitizeInput($_POST['patient_id']) : null; // Patient ID (for update or processing)
    $name = sanitizeInput($_POST['p_name']);
    $age = sanitizeInput($_POST['age']);
    $medicalProblems = sanitizeInput($_POST['medical_problems']);

    // Sanitize and validate transfusion_need, ensuring only valid values ('Urgent' or 'Non-Urgent')
    $transfusionNeed = isset($_POST['transfusion_need']) && in_array($_POST['transfusion_need'], ['Urgent', 'Non-Urgent'])
        ? sanitizeInput($_POST['transfusion_need'])
        : 'Urgent'; // default value if not provided or invalid

    // Sanitize other fields
    $bloodGroup = isset($_POST['blood_group']) && !empty($_POST['blood_group']) ? sanitizeInput($_POST['blood_group']) : null;
    $contactInfo = isset($_POST['contact_info']) && !empty($_POST['contact_info']) ? sanitizeInput($_POST['contact_info']) : null;
    $hospitalId = sanitizeInput($_POST['hospital_id']); // Hospital ID from the dropdown
    $transfusionDate = isset($_POST['transfusion_date']) && !empty($_POST['transfusion_date']) ? sanitizeInput($_POST['transfusion_date']) : null;

    // Validate inputs (optional but recommended)
    if (empty($name) || empty($age) || empty($transfusionNeed) || empty($hospitalId)) {
        echo "<script type='text/javascript'>alert('Please fill all required fields'); window.location.href = 'add_patient.php';</script>";
    } else {
        // If we have a patient ID, we are updating the patient; otherwise, we're adding a new one.
        if (!empty($patientId)) {
            // Update the patient if patient ID is provided
            if (updatePatient($patientId, $name, $age, $medicalProblems, $transfusionNeed, $hospitalId, $bloodGroup, $contactInfo, $transfusionDate)) {
                echo "<script type='text/javascript'>alert('Patient updated successfully'); window.location.href = 'admin_dashboard.php';</script>";
            } else {
                echo "<script type='text/javascript'>alert('Error updating patient'); window.location.href = 'process_patient.php';</script>";
            }
        } else {
            // Add new patient if no patient ID is provided
            if (addPatient($name, $age, $medicalProblems, $transfusionNeed, $hospitalId, $bloodGroup, $contactInfo, $transfusionDate)) {
                echo "<script type='text/javascript'>alert('Patient added successfully'); window.location.href = 'admin_dashboard.php';</script>";
            } else {
                echo "<script type='text/javascript'>alert('Error adding patient'); window.location.href = 'process_patient.php';</script>";
            }
        }
    }
} else {
    // Redirect if the form was not submitted properly
    redirectTo('admin_dashboard.php');
}

// Function to update patient information
function updatePatient($patientId, $name, $age, $medicalProblems, $transfusionNeed, $hospitalId, $bloodGroup, $contactInfo, $transfusionDate) {
    global $conn;

    $sql = "UPDATE patient SET 
            p_name = ?, 
            age = ?, 
            blood_group = ?, 
            medical_problems = ?, 
            transfusion_need = ?, 
            transfusion_date = ?, 
            contact_info = ?, 
            hospital_id = ? 
            WHERE patient_id = ?";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("MySQL error: " . $conn->error);
        return false;
    }

    $stmt->bind_param('sissssssi', $name, $age, $bloodGroup, $medicalProblems, $transfusionNeed, $transfusionDate, $contactInfo, $hospitalId, $patientId);

    // Execute the statement and return success/failure
    if ($stmt->execute()) {
        return true;
    } else {
        error_log("Execution failed: " . $stmt->error);
        return false;
    }
}

// Function to add new patient
function addPatient($name, $age, $medicalProblems, $transfusionNeed, $hospitalId, $bloodGroup, $contactInfo, $transfusionDate) {
    global $conn;

    // Prepare the SQL statement for inserting new patient data
    $sql = "INSERT INTO patient
            (p_name, age, blood_group, medical_problems, transfusion_need, transfusion_date, contact_info, hospital_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    // Prepare the statement
    $stmt = $conn->prepare($sql);

    // Check if preparation was successful
    if ($stmt === false) {
        error_log("MySQL error: " . $conn->error);
        return false;
    }

    $stmt->bind_param('sissssss', $name, $age, $bloodGroup, $medicalProblems, $transfusionNeed, $transfusionDate, $contactInfo, $hospitalId);

    // Execute the statement and return success/failure
    if ($stmt->execute()) {
        return true;
    } else {
        error_log("Execution failed: " . $stmt->error);
        return false;
    }
}
?>
