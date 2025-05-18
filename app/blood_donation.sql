-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Dec 02, 2024 at 01:51 AM
-- Server version: 5.7.44
-- PHP Version: 8.2.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `blood_donation`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `schedule_appointment` (IN `p_donor_id` INT, IN `p_appointment_date` DATE)   BEGIN
    DECLARE donor_eligible BOOLEAN;
    
    SELECT d_eligibility_status INTO donor_eligible
    FROM donor
    WHERE donor_id = p_donor_id;
    
    IF donor_eligible THEN
        INSERT INTO appointment (donor_id, appointment_date, status)
        VALUES (p_donor_id, p_appointment_date, 'Scheduled');
        COMMIT;
    ELSE
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Donor is not eligible for an appointment';
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `update_blood_inventory` (IN `p_donation_id` INT, IN `p_blood_group` VARCHAR(3), IN `p_amount` INT)   BEGIN
    DECLARE test_result ENUM('Passed', 'Failed');
    
    SELECT test_result INTO test_result
    FROM blood_test
    WHERE donation_id = p_donation_id;
    
    IF test_result = 'Passed' THEN
        UPDATE blood_inventory
        SET amount = amount + p_amount
        WHERE blood_group = p_blood_group;
        
        UPDATE donation
        SET status = 'Completed'
        WHERE donation_id = p_donation_id;
        COMMIT;
    ELSE
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Blood test failed, inventory not updated';
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `update_statistics` ()   BEGIN
    -- Update total blood inventory levels
    UPDATE statistics 
    SET blood_inventory_levels = (
        SELECT COALESCE(SUM(amount), 0) FROM blood_inventory
    );
    
    -- Update donor involvement (example: count of active donors)
    UPDATE statistics 
    SET donor_involvement = (
        SELECT CONCAT('Active donors: ', COUNT(*)) FROM donor WHERE d_eligibility_status = 1
    );

    -- Update blood usage (example: total units used in completed donations)
    UPDATE statistics 
    SET blood_usage = (
        SELECT COALESCE(SUM(amount), 0) FROM transfusion_request WHERE status = 'Completed'
    );

    -- Update total transfusion requests
    UPDATE statistics 
    SET transfusion_requests = (
        SELECT COUNT(*) FROM transfusion_request
    );

    -- Update donation campaign performance (e.g., total number of donations from campaigns)
    UPDATE statistics 
    SET donation_campaign_performance = (
        SELECT COUNT(*) FROM donation WHERE status = 'Completed'
    );

    -- Update expired blood count (total units of expired blood)
    UPDATE statistics 
    SET expired_blood = (
        SELECT COUNT(*) FROM blood_inventory WHERE expiration_date < CURDATE()
    );

    -- Update donation trends (example: trends in the last 30 days)
    UPDATE statistics 
    SET donation_trends = (
        SELECT CONCAT('Donations in last 30 days: ', COUNT(*)) FROM donation WHERE donation_date >= CURDATE() - INTERVAL 30 DAY
    );

    -- Update blood shortages (example: count of blood types below a threshold)
    UPDATE statistics 
    SET blood_shortages = (
        SELECT COUNT(*) FROM blood_inventory WHERE amount < 10
    );

    -- Update stock replenishments (example: total replenishments made)
    UPDATE statistics 
    SET stock_replenishment = (
        SELECT COUNT(*) FROM stock_replenishment
    );

END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `appointment`
--

CREATE TABLE `appointment` (
  `appointment_id` int(11) NOT NULL,
  `donor_id` int(11) DEFAULT NULL,
  `appointment_date` date NOT NULL,
  `status` enum('Scheduled','Completed','Cancelled') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `appointment`
--

INSERT INTO `appointment` (`appointment_id`, `donor_id`, `appointment_date`, `status`) VALUES
(1, 9, '2024-11-13', 'Cancelled'),
(2, 9, '2024-11-15', 'Cancelled'),
(3, 9, '2024-11-20', 'Cancelled'),
(4, 20, '2024-11-13', 'Cancelled'),
(5, 9, '2024-11-13', 'Cancelled'),
(6, 9, '2024-11-13', 'Cancelled'),
(7, 9, '2024-12-13', 'Scheduled');

-- --------------------------------------------------------

--
-- Table structure for table `blood_inventory`
--

CREATE TABLE `blood_inventory` (
  `blood_id` int(11) NOT NULL,
  `blood_group` varchar(3) NOT NULL,
  `amount` int(11) DEFAULT NULL,
  `storage_date` date NOT NULL,
  `expiration_date` date NOT NULL,
  `storage_conditions` varchar(255) DEFAULT NULL,
  `minimum_required` int(11) DEFAULT '5'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `blood_inventory`
--

INSERT INTO `blood_inventory` (`blood_id`, `blood_group`, `amount`, `storage_date`, `expiration_date`, `storage_conditions`, `minimum_required`) VALUES
(1, 'A+', 20, '2024-11-11', '2024-11-14', NULL, 5),
(2, 'O+', 10, '2024-12-02', '2024-12-28', NULL, 5);

--
-- Triggers `blood_inventory`
--
DELIMITER $$
CREATE TRIGGER `before_blood_expiration` BEFORE UPDATE ON `blood_inventory` FOR EACH ROW BEGIN
    IF DATEDIFF(NEW.expiration_date, CURDATE()) <= 7 THEN
        INSERT INTO notifications (notification_message, created_at)
        VALUES (CONCAT('Blood unit with ID ', NEW.blood_id, ' is expiring soon'), NOW());
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `blood_test`
--

CREATE TABLE `blood_test` (
  `test_id` int(11) NOT NULL,
  `test_result` varchar(50) DEFAULT NULL,
  `test_date` date DEFAULT NULL,
  `test_type` varchar(50) DEFAULT NULL,
  `donation_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `blood_test`
--

INSERT INTO `blood_test` (`test_id`, `test_result`, `test_date`, `test_type`, `donation_id`) VALUES
(1, 'Pass', '2024-11-12', NULL, 2),
(2, 'Pass', '2024-12-01', NULL, 3);

--
-- Triggers `blood_test`
--
DELIMITER $$
CREATE TRIGGER `after_blood_test_insert` AFTER INSERT ON `blood_test` FOR EACH ROW BEGIN
    IF NEW.test_result = 'Passed' THEN
        UPDATE donation
        SET status = 'Completed'
        WHERE donation_id = NEW.donation_id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `campaign`
--

CREATE TABLE `campaign` (
  `campaign_id` int(11) NOT NULL,
  `camp_name` varchar(100) NOT NULL,
  `camp_location` varchar(255) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `donor_involvement` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `campaign`
--

INSERT INTO `campaign` (`campaign_id`, `camp_name`, `camp_location`, `start_date`, `end_date`, `donor_involvement`) VALUES
(1, 'Christmas Blood Donation', 'Denton', '2024-11-12', '2024-11-15', NULL),
(2, 'New Year Campaign', 'Dallas', '2024-12-02', '2024-12-28', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `campaign_donor`
--

CREATE TABLE `campaign_donor` (
  `campaign_id` int(11) NOT NULL,
  `donor_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `campaign_donor`
--

INSERT INTO `campaign_donor` (`campaign_id`, `donor_id`) VALUES
(1, 9);

-- --------------------------------------------------------

--
-- Table structure for table `donation`
--

CREATE TABLE `donation` (
  `donation_id` int(11) NOT NULL,
  `donor_id` int(11) DEFAULT NULL,
  `donation_date` date NOT NULL,
  `amount` int(11) DEFAULT NULL,
  `health_condition` text,
  `status` enum('Pending','Completed','Failed') DEFAULT 'Pending',
  `blood_test_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `donation`
--

INSERT INTO `donation` (`donation_id`, `donor_id`, `donation_date`, `amount`, `health_condition`, `status`, `blood_test_id`) VALUES
(1, 9, '2024-11-12', NULL, 'NA', 'Completed', NULL),
(2, 9, '2024-11-12', NULL, 'NA', 'Completed', NULL),
(3, 20, '2024-12-01', NULL, 'NA', 'Completed', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `donor`
--

CREATE TABLE `donor` (
  `donor_id` int(11) NOT NULL,
  `donor_name` varchar(100) NOT NULL,
  `d_address` varchar(255) DEFAULT NULL,
  `d_blood_group` varchar(3) NOT NULL,
  `d_health_history` text,
  `d_eligibility_status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `donor`
--

INSERT INTO `donor` (`donor_id`, `donor_name`, `d_address`, `d_blood_group`, `d_health_history`, `d_eligibility_status`) VALUES
(9, 'tst_donor', '1101 avenue C', 'O+', 'Good Standing.', 1),
(15, 'tst_donor1', '1101 Ave C', 'A+', 'NA', 1),
(18, 'tst2_donor', 'Union Circle', 'O+', 'NA', 1),
(20, 'Harshith', '1101 Ave C', 'O+', 'NA', 1);

-- --------------------------------------------------------

--
-- Table structure for table `hospital`
--

CREATE TABLE `hospital` (
  `hospital_id` int(11) NOT NULL,
  `hosp_name` varchar(100) NOT NULL,
  `hosp_location` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `hospital`
--

INSERT INTO `hospital` (`hospital_id`, `hosp_name`, `hosp_location`) VALUES
(11, 'Texas Health', 'Denton'),
(16, 'United health', 'Texas');

-- --------------------------------------------------------

--
-- Table structure for table `patient`
--

CREATE TABLE `patient` (
  `patient_id` int(11) NOT NULL,
  `p_name` varchar(100) NOT NULL,
  `age` int(11) DEFAULT NULL,
  `blood_group` varchar(5) DEFAULT NULL,
  `medical_problems` text,
  `transfusion_need` enum('Urgent','Non-Urgent') NOT NULL,
  `transfusion_date` date DEFAULT NULL,
  `contact_info` varchar(100) DEFAULT NULL,
  `hospital_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `patient`
--

INSERT INTO `patient` (`patient_id`, `p_name`, `age`, `blood_group`, `medical_problems`, `transfusion_need`, `transfusion_date`, `contact_info`, `hospital_id`) VALUES
(1, 'Krishna', NULL, 'O+ve', 'NA', 'Urgent', NULL, '9803311133', NULL),
(2, 'Harshith', NULL, 'A+ve', 'Hepatits', 'Urgent', NULL, '9803311133', NULL),
(3, 'rohith', 28, 'O+', 'NA', 'Non-Urgent', NULL, '1234567890', 16),
(4, 'Vamshi', 27, 'B+', 'na', 'Urgent', NULL, '2345678910', 11),
(5, 'test', 43, 'B-', 'na', 'Urgent', NULL, '8766655559', 11);

-- --------------------------------------------------------

--
-- Table structure for table `role`
--

CREATE TABLE `role` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `permissions` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `role`
--

INSERT INTO `role` (`role_id`, `role_name`, `permissions`) VALUES
(1, 'Donor', 'Can register as a donor and donate blood'),
(2, 'Hospital', 'Can request blood and manage hospital data'),
(3, 'Staff', 'Can manage appointments and inventory'),
(4, 'Admin', 'Full access to manage users and system settings');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `staff_id` int(11) NOT NULL,
  `s_name` varchar(100) NOT NULL,
  `s_contact_info` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`staff_id`, `s_name`, `s_contact_info`) VALUES
(10, 'tst_staff', '9989279124'),
(14, 'tst_staff1', '21321313322'),
(17, 'tst_staff2', 'test');

-- --------------------------------------------------------

--
-- Table structure for table `statistics`
--

CREATE TABLE `statistics` (
  `stat_id` int(11) NOT NULL,
  `blood_inventory_levels` int(11) DEFAULT NULL,
  `donor_involvement` text,
  `blood_usage` int(11) DEFAULT NULL,
  `transfusion_requests` int(11) DEFAULT NULL,
  `donation_campaign_performance` int(11) DEFAULT NULL,
  `expired_blood` int(11) DEFAULT NULL,
  `donation_trends` text,
  `blood_shortages` int(11) DEFAULT NULL,
  `stock_replenishment` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `statistics`
--

INSERT INTO `statistics` (`stat_id`, `blood_inventory_levels`, `donor_involvement`, `blood_usage`, `transfusion_requests`, `donation_campaign_performance`, `expired_blood`, `donation_trends`, `blood_shortages`, `stock_replenishment`) VALUES
(1, 20, '3', NULL, 6, 1, 0, '0', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `transfusion_request`
--

CREATE TABLE `transfusion_request` (
  `request_id` int(11) NOT NULL,
  `hospital_id` int(11) DEFAULT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `blood_group` varchar(3) NOT NULL,
  `amount` int(11) DEFAULT NULL,
  `request_date` date NOT NULL,
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `transfusion_request`
--

INSERT INTO `transfusion_request` (`request_id`, `hospital_id`, `patient_id`, `blood_group`, `amount`, `request_date`, `status`) VALUES
(1, 11, NULL, 'A+', 1, '2024-11-11', 'Pending'),
(2, 11, NULL, 'B+', 2, '2024-11-11', 'Pending'),
(3, 11, NULL, 'O+', 22, '2024-11-11', 'Pending'),
(4, 11, NULL, 'O+', 20, '2024-11-11', 'Pending'),
(5, 11, NULL, 'O+', 20, '2024-11-11', 'Pending'),
(6, 11, NULL, 'B+', 11, '2024-11-11', 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('Donor','Staff','Hospital','Admin') NOT NULL,
  `status` enum('Active','Inactive') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `username`, `password`, `email`, `role`, `status`) VALUES
(9, 'tst_donor', '$2y$10$S5t3EXPcYZn79jaOHXQHGuC9XRi6.cWWmub1hiETJoPLuBqbpHQ6a', 'donor@test.com', 'Donor', 'Active'),
(10, 'tst_staff', '$2y$10$vt0G5lgoVmVrzrz7Kgj8.uYDKp8i4yggUW.TQ4ZfbKpaNRqMvEFRO', 'staff@test.com', 'Staff', 'Active'),
(11, 'tst_hosp', '$2y$10$f2eG/FADGhKX7O8v6kHKt.K3DjXnANw/Beld9ykAql1zX.bBYaa7a', 'th@hospital.com', 'Hospital', 'Active'),
(14, 'tst_staff1', '$2y$10$jmZladiljbUaXwmS/pTlyuUlm00PMK4p7CVtVte.12200k2zoT8g2', 'satff1@test.com', 'Staff', 'Active'),
(15, 'tst_donor1', '$2y$10$u1FDdfHXBYFNMRLTPpzeNeecd.kCKpYS7WglsyCSx3hSBEyEvqcRa', 'donor1@test.com', 'Donor', 'Active'),
(16, 'tst_hosp1', '$2y$10$G4q6zvAcq8Zw/USrRU8z7.xMVX4pPSckpqzPqPA2WeeG5YzTlYc3.', 'hosp1@test.com', 'Hospital', 'Active'),
(17, 'tst_staff2', '$2y$10$jj1/bBM9ug0WCskqIQyCB.n7ksibDiAdiW4hpWuDuzeVupz0AEASC', 'staff2@test.com', 'Staff', 'Active'),
(18, 'tst2_donor', '$2y$10$MKpMjvirXMO3Ex6DD5SfVudtsrduZlrg3gpBoD5axDQ3PhZa/gsoe', 'donor2@test.com', 'Donor', 'Active'),
(19, 'admin', '$2y$10$ufgBn.pmoGD5nyQReXTMuODLTfHWb3dKD5Oi1JblMDr4exDmhYtym', 'admin@test.com', 'Admin', 'Active'),
(20, 'Harshith', '$2y$10$fE2YUqBCFvOEygPD0JaOVuWk6NSV1/w1e9ct22mB6wLmmHlK9hHK.', 'harshith@gmail.com', 'Donor', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `user_role`
--

CREATE TABLE `user_role` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `user_role`
--

INSERT INTO `user_role` (`user_id`, `role_id`) VALUES
(9, 1),
(15, 1),
(18, 1),
(20, 1),
(11, 2),
(16, 2),
(10, 3),
(14, 3),
(17, 3),
(19, 4);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointment`
--
ALTER TABLE `appointment`
  ADD PRIMARY KEY (`appointment_id`),
  ADD KEY `donor_id` (`donor_id`),
  ADD KEY `appointment_date` (`appointment_date`);

--
-- Indexes for table `blood_inventory`
--
ALTER TABLE `blood_inventory`
  ADD PRIMARY KEY (`blood_id`),
  ADD KEY `expiration_date` (`expiration_date`);

--
-- Indexes for table `blood_test`
--
ALTER TABLE `blood_test`
  ADD PRIMARY KEY (`test_id`),
  ADD KEY `test_result` (`test_result`),
  ADD KEY `fk_donation` (`donation_id`);

--
-- Indexes for table `campaign`
--
ALTER TABLE `campaign`
  ADD PRIMARY KEY (`campaign_id`);

--
-- Indexes for table `campaign_donor`
--
ALTER TABLE `campaign_donor`
  ADD PRIMARY KEY (`campaign_id`,`donor_id`),
  ADD KEY `donor_id` (`donor_id`);

--
-- Indexes for table `donation`
--
ALTER TABLE `donation`
  ADD PRIMARY KEY (`donation_id`),
  ADD KEY `donor_id` (`donor_id`),
  ADD KEY `fk_donation_blood_test` (`blood_test_id`);

--
-- Indexes for table `donor`
--
ALTER TABLE `donor`
  ADD PRIMARY KEY (`donor_id`),
  ADD KEY `d_blood_group` (`d_blood_group`);

--
-- Indexes for table `hospital`
--
ALTER TABLE `hospital`
  ADD PRIMARY KEY (`hospital_id`),
  ADD KEY `hosp_name` (`hosp_name`);

--
-- Indexes for table `patient`
--
ALTER TABLE `patient`
  ADD PRIMARY KEY (`patient_id`),
  ADD KEY `transfusion_need` (`transfusion_need`),
  ADD KEY `fk_patient_hospital` (`hospital_id`);

--
-- Indexes for table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`staff_id`);

--
-- Indexes for table `statistics`
--
ALTER TABLE `statistics`
  ADD PRIMARY KEY (`stat_id`);

--
-- Indexes for table `transfusion_request`
--
ALTER TABLE `transfusion_request`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `hospital_id` (`hospital_id`),
  ADD KEY `request_date` (`request_date`,`status`),
  ADD KEY `fk_patient_id` (`patient_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_role`
--
ALTER TABLE `user_role`
  ADD PRIMARY KEY (`user_id`,`role_id`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointment`
--
ALTER TABLE `appointment`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `blood_inventory`
--
ALTER TABLE `blood_inventory`
  MODIFY `blood_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `blood_test`
--
ALTER TABLE `blood_test`
  MODIFY `test_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `campaign`
--
ALTER TABLE `campaign`
  MODIFY `campaign_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `donation`
--
ALTER TABLE `donation`
  MODIFY `donation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `donor`
--
ALTER TABLE `donor`
  MODIFY `donor_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `hospital`
--
ALTER TABLE `hospital`
  MODIFY `hospital_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `patient`
--
ALTER TABLE `patient`
  MODIFY `patient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `role`
--
ALTER TABLE `role`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `statistics`
--
ALTER TABLE `statistics`
  MODIFY `stat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `transfusion_request`
--
ALTER TABLE `transfusion_request`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointment`
--
ALTER TABLE `appointment`
  ADD CONSTRAINT `appointment_ibfk_1` FOREIGN KEY (`donor_id`) REFERENCES `donor` (`donor_id`) ON DELETE CASCADE;

--
-- Constraints for table `blood_test`
--
ALTER TABLE `blood_test`
  ADD CONSTRAINT `fk_donation` FOREIGN KEY (`donation_id`) REFERENCES `donation` (`donation_id`);

--
-- Constraints for table `campaign_donor`
--
ALTER TABLE `campaign_donor`
  ADD CONSTRAINT `campaign_donor_ibfk_1` FOREIGN KEY (`campaign_id`) REFERENCES `campaign` (`campaign_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `campaign_donor_ibfk_2` FOREIGN KEY (`donor_id`) REFERENCES `donor` (`donor_id`) ON DELETE CASCADE;

--
-- Constraints for table `donation`
--
ALTER TABLE `donation`
  ADD CONSTRAINT `donation_ibfk_1` FOREIGN KEY (`donor_id`) REFERENCES `donor` (`donor_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_donation_blood_test` FOREIGN KEY (`blood_test_id`) REFERENCES `blood_test` (`test_id`);

--
-- Constraints for table `patient`
--
ALTER TABLE `patient`
  ADD CONSTRAINT `fk_patient_hospital` FOREIGN KEY (`hospital_id`) REFERENCES `hospital` (`hospital_id`);

--
-- Constraints for table `transfusion_request`
--
ALTER TABLE `transfusion_request`
  ADD CONSTRAINT `fk_patient_id` FOREIGN KEY (`patient_id`) REFERENCES `patient` (`patient_id`),
  ADD CONSTRAINT `transfusion_request_ibfk_1` FOREIGN KEY (`hospital_id`) REFERENCES `hospital` (`hospital_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_role`
--
ALTER TABLE `user_role`
  ADD CONSTRAINT `user_role_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_role_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `role` (`role_id`) ON DELETE CASCADE;

DELIMITER $$
--
-- Events
--
CREATE DEFINER=`root`@`localhost` EVENT `daily_statistics_update` ON SCHEDULE EVERY 1 DAY STARTS '2024-11-11 12:42:42' ON COMPLETION NOT PRESERVE ENABLE DO CALL update_statistics()$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
