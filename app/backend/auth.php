<?php
include 'functions.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function checkAuth($requiredRole = null) {
    if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
        http_response_code(401);
        showModalMessage("Unauthorized access");
        exit();
    }

    if ($requiredRole && (!isset($_SESSION['role']) || $_SESSION['role'] !== $requiredRole)) {
        http_response_code(403);
        showModalMessage("Forbidden: You do not have permission to access this resource.");
        exit();
    }
}
?>
