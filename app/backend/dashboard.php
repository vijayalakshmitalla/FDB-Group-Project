<?php
include 'auth.php';
include 'db_connect.php';
include 'functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is authenticated
if (!isLoggedIn()) {
    redirectTo('login.html');
}

// Redirect based on user role stored in the session
if (isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'Admin':
            redirectTo('admin_dashboard.php');
            break;
        case 'Donor':
            redirectTo('donor_dashboard.php');
            break;
        case 'Staff':
            redirectTo('staff_dashboard.php');
            break;
        case 'Hospital':
            redirectTo('hospital_dashboard.php');
            break;
        default:
            echo "Access Denied: Invalid or unauthorized user role.";
            break;
    }
} else {
    echo "Access Denied: Role not found in the session.";
}
exit();
?>
