<?php
include 'db_connect.php'; // Ensure the database connection is included

// Role and Authentication Functions
if (!function_exists('hasRole')) {
    function hasRole($userId, $requiredRole) {
        global $conn;
        $stmt = $conn->prepare("
            SELECT r.role_name
            FROM user_role ur
            JOIN role r ON ur.role_id = r.role_id
            WHERE ur.user_id = ? AND r.role_name = ?
        ");
        $stmt->bind_param("is", $userId, $requiredRole);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result->num_rows > 0;
    }
}

if (!function_exists('startUserSession')) {
    function startUserSession($userId, $username) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $username;
    }
}

if (!function_exists('isLoggedIn')) {
    function isLoggedIn() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['user_id']);
    }
}

if (!function_exists('logoutUser')) {
    function logoutUser() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = array();
        session_destroy();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }
    }
}

if (!function_exists('redirectTo')) {
    function redirectTo($url) {
        header("Location: $url");
        exit();
    }
}

// Utility Functions
if (!function_exists('sanitizeInput')) {
    function sanitizeInput($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('debugSession')) {
    function debugSession() {
        echo '<pre>';
        print_r($_SESSION);
        echo '</pre>';
    }
}

if (!function_exists('showModalMessage')) {
    function showModalMessage($message) {
        echo "<script type='text/javascript'>alert('$message');</script>";
    }
}

// Donation Functions
if (!function_exists('addDonation')) {
    function addDonation($donorId, $donationDate, $healthCondition, $status) {
        global $conn;
        $stmt = $conn->prepare("INSERT INTO donation (donor_id, donation_date, health_condition, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $donorId, $donationDate, $healthCondition, $status);
        if ($stmt->execute()) {
            return $conn->insert_id; // Return the donation_id for use in blood test
        }
        return false;
    }
}


if (!function_exists('getDonations')) {
    function getDonations($donorId = null) {
        global $conn;
        $query = "SELECT * FROM donation";
        if ($donorId) {
            $query .= " WHERE donor_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $donorId);
        } else {
            $stmt = $conn->prepare($query);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}

// Patient Functions
if (!function_exists('addPatient')) {
    function addPatient($name, $age, $bloodGroup, $medicalProblems, $transfusionNeed, $transfusionDate, $contactInfo, $hospitalId) {
        global $conn;
    
        // Prepare the SQL query to insert data into the 'patients' table
        $sql = "INSERT INTO patient (p_name, age, blood_group, medical_problems, transfusion_need, transfusion_date, contact_info, hospital_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
        // Prepare the statement
        if ($stmt = $conn->prepare($sql)) {
            // Bind parameters to the query
            $stmt->bind_param("sisssssi", $name, $age, $bloodGroup, $medicalProblems, $transfusionNeed, $transfusionDate, $contactInfo, $hospitalId);
    
            // Execute the query
            if ($stmt->execute()) {
                return true; // Successfully added patient
            } else {
                return false; // Error inserting patient
            }
        } else {
            return false; // Error preparing the query
        }
    }
      
}



if (!function_exists('getHospitals')) {
    function getHospitals() {
        global $conn;
        // Update the query to use the correct table name 'hospital'
        $sql = "SELECT hospital_id, hosp_name FROM hospital";
        $result = $conn->query($sql);
        $hospitals = [];
    
        while ($row = $result->fetch_assoc()) {
            $hospitals[] = $row;
        }
        return $hospitals;
    }
}


if (!function_exists('getPatients')) {
    function getPatients() {
        global $conn;
        $stmt = $conn->prepare("SELECT * FROM patient");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}

if (!function_exists('addBloodTest')) {
    function addBloodTest($donationId, $testResult, $testDate = null, $testType = null) {
        global $conn;
        
        // Check that $testResult is within the allowed length if needed
        $stmt = $conn->prepare("INSERT INTO blood_test (donation_id, test_result, test_date, test_type) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $donationId, $testResult, $testDate, $testType);
        
        return $stmt->execute();
    }
}

if (!function_exists('getBloodTests')) {
    function getBloodTests($donationId = null) {
        global $conn;
        $query = "SELECT * FROM blood_test";
        
        if ($donationId) {
            $query .= " WHERE donation_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $donationId);
        } else {
            $stmt = $conn->prepare($query);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $tests = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $tests;
    }
}

// Transfusion Request Functions
if (!function_exists('addTransfusionRequest')) {
    function addTransfusionRequest($hospitalId, $bloodGroup, $amount) {
        global $conn;
        $requestDate = date('Y-m-d');
        $status = 'Pending';
        $stmt = $conn->prepare("INSERT INTO transfusion_request (hospital_id, blood_group, amount, request_date, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isiss", $hospitalId, $bloodGroup, $amount, $requestDate, $status);
        return $stmt->execute();
    }
}

if (!function_exists('updatePendingTransfusionRequests')) {
    function updatePendingTransfusionRequests() {
        global $conn;
        
        $pendingCountStmt = $conn->prepare("
            UPDATE statistics 
            SET transfusion_requests = (SELECT COUNT(*) FROM transfusion_request WHERE status = 'Pending')
            WHERE stat_id = 1
        ");
        $pendingCountStmt->execute();
        $pendingCountStmt->close();
    }
}

if (!function_exists('completeTransfusionRequest')) {
    function completeTransfusionRequest($requestId) {
        global $conn;
        
        // Mark request as completed
        $stmt = $conn->prepare("UPDATE transfusion_request SET status = 'Completed' WHERE request_id = ?");
        $stmt->bind_param("i", $requestId);
        $stmt->execute();
        
        // Update blood usage in statistics
        $usageStmt = $conn->prepare("
            UPDATE statistics 
            SET blood_usage = blood_usage + (SELECT amount FROM transfusion_request WHERE request_id = ?)
            WHERE stat_id = 1
        ");
        $usageStmt->bind_param("i", $requestId);
        $usageStmt->execute();
        
        $stmt->close();
        $usageStmt->close();
    }
}

// Campaign Management Functions
if (!function_exists('createCampaign')) {
    function createCampaign($name, $location, $startDate, $endDate, $donorInvolvement) {
        global $conn;
        $stmt = $conn->prepare("INSERT INTO campaign (camp_name, camp_location, start_date, end_date, donor_involvement) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $location, $startDate, $endDate, $donorInvolvement);
        return $stmt->execute();
    }
}

if (!function_exists('updateCampaign')) {
    function updateCampaign($campaignId, $name, $location, $startDate, $endDate, $donorInvolvement) {
        global $conn;
        $stmt = $conn->prepare("UPDATE campaign SET camp_name = ?, camp_location = ?, start_date = ?, end_date = ?, donor_involvement = ? WHERE campaign_id = ?");
        $stmt->bind_param("sssssi", $name, $location, $startDate, $endDate, $donorInvolvement, $campaignId);
        return $stmt->execute();
    }
}

if (!function_exists('getCampaigns')) {
    function getCampaigns() {
        global $conn;
        $stmt = $conn->prepare("SELECT * FROM campaign");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}

if (!function_exists('deleteCampaign')) {
    function deleteCampaign($campaignId) {
        global $conn;
        $stmt = $conn->prepare("DELETE FROM campaign WHERE campaign_id = ?");
        $stmt->bind_param("i", $campaignId);
        return $stmt->execute();
    }
}

if (!function_exists('addDonorToCampaign')) {
    function addDonorToCampaign($campaignId, $donorId) {
        global $conn;
        $stmt = $conn->prepare("INSERT INTO campaign_donor (campaign_id, donor_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $campaignId, $donorId);
        return $stmt->execute();
    }
}

if (!function_exists('getDonorsByCampaign')) {
    function getDonorsByCampaign($campaignId) {
        global $conn;
        $stmt = $conn->prepare("SELECT d.donor_id, d.donor_name FROM campaign_donor cd JOIN donor d ON cd.donor_id = d.donor_id WHERE cd.campaign_id = ?");
        $stmt->bind_param("i", $campaignId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>
