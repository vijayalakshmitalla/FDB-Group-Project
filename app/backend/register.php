<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "Register script reached.<br>"; // Debug line

    $username = htmlspecialchars(trim($_POST['username']));
    $email = htmlspecialchars(trim($_POST['email']));
    $password = htmlspecialchars(trim($_POST['password']));
    $role = htmlspecialchars(trim($_POST['role']));

    // Validate input fields
    if (empty($username) || empty($email) || empty($password) || empty($role)) {
        echo "All required fields must be filled out.";
        exit();
    }

    // Check if username or email already exists
    $stmt = $conn->prepare("SELECT * FROM user WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "Username or email already taken.";
    } else {
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Insert into the user table with common fields
        $stmt = $conn->prepare("INSERT INTO user (username, password, email, role, status) VALUES (?, ?, ?, ?, 'Active')");
        $stmt->bind_param("ssss", $username, $hashedPassword, $email, $role);

        if ($stmt->execute()) {
            $userId = $conn->insert_id;

            // Get the role_id from the role table
            $stmt = $conn->prepare("SELECT role_id FROM role WHERE role_name = ?");
            $stmt->bind_param("s", $role);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $roleId = $result->fetch_assoc()['role_id'];

                // Insert the user-role association into the user_role table
                $stmt = $conn->prepare("INSERT INTO user_role (user_id, role_id) VALUES (?, ?)");
                $stmt->bind_param("ii", $userId, $roleId);
                if ($stmt->execute()) {
                    echo "User role association created successfully.<br>";
                } else {
                    echo "Error inserting into user_role table: " . $stmt->error . "<br>";
                }
            } else {
                echo "Error: Role not found.<br>";
            }

            // Insert into role-specific tables
            switch ($role) {
                case 'Donor':
                    echo "Processing Donor registration.<br>"; // Debug line
                    $d_address = htmlspecialchars(trim($_POST['d_address']));
                    $d_blood_group = htmlspecialchars(trim($_POST['d_blood_group']));
                    $d_health_history = htmlspecialchars(trim($_POST['d_health_history']));
                    $stmt = $conn->prepare("INSERT INTO donor (donor_id, donor_name, d_address, d_blood_group, d_health_history, d_eligibility_status) VALUES (?, ?, ?, ?, ?, 1)");
                    $stmt->bind_param("issss", $userId, $username, $d_address, $d_blood_group, $d_health_history);
                    $stmt->execute();
                    echo "Donor table entry created successfully.<br>"; // Debug line
                    break;

                case 'Hospital':
                    echo "Processing Hospital registration.<br>"; // Debug line
                    $hosp_name = htmlspecialchars(trim($_POST['hosp_name']));
                    $hosp_location = htmlspecialchars(trim($_POST['hosp_location']));
                    echo "Hospital Name: $hosp_name, Location: $hosp_location<br>"; // Debug line
                    $stmt = $conn->prepare("INSERT INTO hospital (hospital_id, hosp_name, hosp_location) VALUES (?, ?, ?)");
                    $stmt->bind_param("iss", $userId, $hosp_name, $hosp_location);
                    if ($stmt->execute()) {
                        echo "Hospital table entry created successfully.<br>"; // Debug line
                    } else {
                        echo "Error inserting into hospital table: " . $stmt->error . "<br>";
                    }
                    break;

                case 'Staff':
                    echo "Processing Staff registration.<br>"; // Debug line
                    $s_contact_info = htmlspecialchars(trim($_POST['s_contact_info']));
                    echo "Staff Contact Info: $s_contact_info<br>"; // Debug line
                    $stmt = $conn->prepare("INSERT INTO staff (staff_id, s_name, s_contact_info) VALUES (?, ?, ?)");
                    $stmt->bind_param("iss", $userId, $username, $s_contact_info);
                    if ($stmt->execute()) {
                        echo "Staff table entry created successfully.<br>"; // Debug line
                    } else {
                        echo "Error inserting into staff table: " . $stmt->error . "<br>";
                    }
                    break;

                default:
                    echo "Invalid role selected.";
                    exit();
            }

            echo "Registration successful!";
        } else {
            echo "Error inserting into user table: " . $stmt->error;
        }
    }

    $stmt->close();
    $conn->close();
}
?>
