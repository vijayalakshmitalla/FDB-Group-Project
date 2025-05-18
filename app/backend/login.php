<?php
include 'db_connect.php';
include 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = sanitizeInput($_POST['password']);

    // Check credentials in the database
    $stmt = $conn->prepare("SELECT user_id, password FROM user WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            startUserSession($row['user_id'], $username);
            $_SESSION['authenticated'] = true; // Set authenticated to true

            // Retrieve the role from the database and set it in the session
            $stmt = $conn->prepare("
                SELECT r.role_name 
                FROM user_role ur
                JOIN role r ON ur.role_id = r.role_id
                WHERE ur.user_id = ?
            ");
            $stmt->bind_param("i", $row['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $roleRow = $result->fetch_assoc();
                $_SESSION['role'] = $roleRow['role_name']; // Set the role in the session
                redirectTo('dashboard.php');
            } else {
                echo "Error: User role not found.";
            }
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "Invalid username.";
    }
    $stmt->close();
    $conn->close();
}
?>
