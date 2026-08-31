-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Aug 31, 2026 at 07:50 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hms_opd_new`
--

-- --------------------------------------------------------

--
-- Table structure for table `admission`
--

CREATE TABLE `admission` (
  `admission_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `admission_date` datetime DEFAULT NULL,
  `admission_type` enum('Admit','Planned') NOT NULL,
  `problem` varchar(255) DEFAULT NULL,
  `status` enum('Admitted','Discharged','Consult') DEFAULT NULL,
  `discharge_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admission`
--

INSERT INTO `admission` (`admission_id`, `patient_id`, `doctor_id`, `admission_date`, `admission_type`, `problem`, `status`, `discharge_date`) VALUES
(502, 302, 202, '2026-08-04 14:00:00', 'Planned', 'Migraine', 'Discharged', '2026-08-05 12:30:00'),
(503, 303, 203, '2026-08-07 10:15:00', 'Planned', 'High fever and infection', 'Discharged', '2026-08-09 15:00:00'),
(504, 304, 203, '2026-08-10 16:30:00', 'Planned', 'Skin allergy', 'Discharged', '2026-08-11 13:00:00'),
(505, 305, 201, '2026-08-15 09:00:00', 'Planned', 'High cholesterol', 'Admitted', NULL),
(506, 306, 202, '2026-08-30 18:30:00', 'Planned', 'I have severe headache.', 'Consult', NULL),
(507, 306, 204, '2026-09-03 03:40:00', 'Planned', 'i dunno whats my issue', 'Consult', NULL),
(508, 306, 204, '2026-09-04 01:56:00', 'Planned', 'i have skin issue', 'Consult', NULL),
(509, 306, 204, '2026-09-05 01:56:00', 'Planned', 'i have skin issue on my hand', 'Consult', NULL),
(510, 306, 204, '2026-09-01 01:57:00', 'Admit', 'I want to admit under your supervision', 'Admitted', NULL),
(511, 306, 204, '2026-09-05 07:13:00', 'Planned', 'i have fever.', 'Consult', NULL),
(512, 308, 205, '2026-09-05 23:51:00', 'Planned', 'als;dkl;sdk;a', 'Consult', NULL),
(513, 308, 205, '2026-09-05 13:55:00', 'Admit', 'asdasdasdasda', 'Admitted', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `bill`
--

CREATE TABLE `bill` (
  `bill_id` int(11) NOT NULL,
  `admission_id` int(11) NOT NULL,
  `doctor_fee` decimal(10,2) DEFAULT 0.00,
  `medicine_cost` decimal(10,2) DEFAULT 0.00,
  `total_amount` decimal(10,2) DEFAULT 0.00,
  `payment_status` enum('Paid','Unpaid','Partial') DEFAULT 'Unpaid'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bill`
--

INSERT INTO `bill` (`bill_id`, `admission_id`, `doctor_fee`, `medicine_cost`, `total_amount`, `payment_status`) VALUES
(902, 502, 2500.00, 800.00, 3300.00, 'Paid'),
(903, 503, 2000.00, 1500.00, 3500.00, 'Partial'),
(904, 504, 2200.00, 650.00, 2850.00, 'Paid'),
(905, 505, 3000.00, 1800.00, 4800.00, 'Unpaid');

--
-- Triggers `bill`
--
DELIMITER $$
CREATE TRIGGER `calculate_total_amount_insert` BEFORE INSERT ON `bill` FOR EACH ROW BEGIN
    SET NEW.total_amount = NEW.doctor_fee + NEW.medicine_cost;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `calculate_total_amount_update` BEFORE UPDATE ON `bill` FOR EACH ROW BEGIN
    SET NEW.total_amount = NEW.doctor_fee + NEW.medicine_cost;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `consultation`
--

CREATE TABLE `consultation` (
  `consultation_id` int(11) NOT NULL,
  `admission_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `consult_datetime` datetime NOT NULL,
  `report` varchar(255) DEFAULT NULL,
  `status` enum('Approved','Completed','Cancelled') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `consultation`
--

INSERT INTO `consultation` (`consultation_id`, `admission_id`, `doctor_id`, `consult_datetime`, `report`, `status`) VALUES
(603, 502, 202, '2026-08-04 15:00:00', 'Migraine without aura', 'Approved'),
(604, 503, 203, '2026-08-07 11:00:00', 'Viral fever', 'Approved'),
(605, 503, 203, '2026-08-08 10:00:00', 'Fever reduced', 'Approved'),
(606, 504, 203, '2026-08-10 17:00:00', 'Allergic dermatitis', 'Approved'),
(607, 505, 201, '2026-08-15 10:00:00', 'High cholesterol', 'Approved'),
(608, 507, 204, '2026-09-03 03:40:00', 'You have no issue.', 'Completed'),
(609, 509, 204, '2026-08-31 02:02:00', 'i cant help you', 'Cancelled'),
(610, 510, 204, '2026-08-30 22:06:20', 'I will supervise you', 'Approved'),
(611, 508, 204, '2026-09-04 01:56:00', 'Just stop using some fancy cosmetics', 'Completed'),
(612, 511, 204, '2026-08-31 03:11:00', 'it\'s normal in this weather. take a chill pill and sleep', 'Completed');

-- --------------------------------------------------------

--
-- Table structure for table `doctor`
--

CREATE TABLE `doctor` (
  `doctor_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `specialization_id` int(11) DEFAULT NULL,
  `designation` varchar(60) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `experience` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `doctor`
--

INSERT INTO `doctor` (`doctor_id`, `user_id`, `specialization_id`, `designation`, `phone`, `status`, `experience`) VALUES
(201, 201, 1, 'Consultant Cardiologist', '01711000001', 'Active', NULL),
(202, 202, 2, 'Senior Neurologist', '01711000002', 'Active', NULL),
(203, 203, 4, 'General Physician', '01711000003', 'Active', NULL),
(204, 307, 3, 'Consultant Dermatology', '1112223333', 'Active', '40 yrs'),
(205, 311, NULL, NULL, NULL, 'Active', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `patient`
--

CREATE TABLE `patient` (
  `patient_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `blood_group` varchar(5) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `emergency_contact` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `patient`
--

INSERT INTO `patient` (`patient_id`, `user_id`, `gender`, `date_of_birth`, `blood_group`, `address`, `emergency_contact`) VALUES
(302, 302, 'Female', '1999-08-22', 'A+', 'Uttara, Dhaka', '01811000002'),
(303, 303, 'Male', '2003-02-10', 'O+', 'Mohammadpur, Dhaka', '01811000003'),
(304, 304, 'Female', '1998-11-05', 'AB+', 'Dhanmondi, Dhaka', '01811000004'),
(305, 305, 'Male', '1995-06-18', 'B-', 'Badda, Dhaka', '01811000005'),
(306, 306, 'Female', '2026-08-26', 'B+', '108, east rampura, 1219,Dhaka,1219,Bangladesh', '12345678901'),
(308, 310, 'Male', '2023-06-06', 'B+', 'vagabond', '123456789011111');

--
-- Triggers `patient`
--
DELIMITER $$
CREATE TRIGGER `chk_patient_dob_insert` BEFORE INSERT ON `patient` FOR EACH ROW BEGIN
    IF NEW.date_of_birth > CURRENT_DATE() THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Date of birth cannot be in the future.';
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `chk_patient_update_validation` BEFORE UPDATE ON `patient` FOR EACH ROW BEGIN
    -- 1. Check Future Date of Birth
    IF NEW.date_of_birth IS NOT NULL AND NEW.date_of_birth > CURRENT_DATE() THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Trigger Error: Date of birth cannot be in the future.';
    END IF;

    -- 2. Check Valid Blood Group
    IF NEW.blood_group IS NOT NULL AND NEW.blood_group != '' 
       AND NEW.blood_group NOT IN ('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Trigger Error: Invalid blood group. Must be A+, A-, B+, B-, AB+, AB-, O+, or O-.';
    END IF;

    -- 3. Check Emergency Contact Length (At least 11 digits)
    IF NEW.emergency_contact IS NOT NULL AND NEW.emergency_contact != '' 
       AND CHAR_LENGTH(NEW.emergency_contact) < 11 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Trigger Error: Emergency contact must be at least 11 digits.';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `specialization`
--

CREATE TABLE `specialization` (
  `specialization_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `specialization`
--

INSERT INTO `specialization` (`specialization_id`, `name`, `description`) VALUES
(1, 'Cardiology', 'Diagnosis and treatment of heart-related conditions'),
(2, 'Neurology', 'Diagnosis and treatment of nervous system disorders'),
(3, 'Dermatology', 'Treatment of skin, hair and nail conditions'),
(4, 'Pediatrics', 'Medical care for infants, children and adolescents'),
(5, 'General Medicine', 'General diagnosis and treatment of common illnesses');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('Admin','Doctor','Patient') NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(120) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `username`, `password_hash`, `role`, `full_name`, `email`) VALUES
(101, 'admin', '$2y$10$K3v0uW8Z1bX2cY3dZ4e5f.1g2h3i4j5k6l7m8n9o0p1q2r3s4t5u6', 'Admin', 'System Administrator', 'admin@hms.com'),
(201, 'drrahman', '$2y$10$a1b2c3d4e5f6g7h8i9j0k.L1m2n3o4p5q6r7s8t9u0v1w2x3y4z5', 'Doctor', 'Dr. Ahmed Rahman', 'ahmed.rahman@hms.com'),
(202, 'drsadia', '$2y$10$b2c3d4e5f6g7h8i9j0k1l.M2n3o4p5q6r7s8t9u0v1w2x3y4z5a6', 'Doctor', 'Dr. Sadia Karim', 'sadia.karim@hms.com'),
(203, 'drhasan', '$2y$10$c3d4e5f6g7h8i9j0k1l2m.N3o4p5q6r7s8t9u0v1w2x3y4z5a6b7', 'Doctor', 'Dr. Mahmud Hasan', 'mahmud.hasan@hms.com'),
(302, 'patient02', '$2y$10$e5f6g7h8i9j0k1l2m3n4o.P5q6r7s8t9u0v1w2x3y4z5a6b7c8d9', 'Patient', 'Nusrat Jahan', 'nusrat@gmail.com'),
(303, 'patient03', '$2y$10$f6g7h8i9j0k1l2m3n4o5p.Q6r7s8t9u0v1w2x3y4z5a6b7c8d9e0', 'Patient', 'Sakib Hossain', 'sakib@gmail.com'),
(304, 'patient04', '$2y$10$g7h8i9j0k1l2m3n4o5p6q.R7s8t9u0v1w2x3y4z5a6b7c8d9e0f1', 'Patient', 'Maliha Rahman', 'maliha@gmail.com'),
(305, 'patient05', '$2y$10$h8i9j0k1l2m3n4o5p6q7r.S8t9u0v1w2x3y4z5a6b7c8d9e0f1g2', 'Patient', 'Farhan Kabir', 'farhan@gmail.com'),
(306, 'abc123', '$2y$10$e8SZ9bVaS6DCWzIgYd0LZerYBum0J6//gnZs2L9Z0OWVEquw4gpKS', 'Patient', 'abc123', 'abc123@gmail.com'),
(307, 'drhasanpiker', '$2y$10$exCwmP1N4tCSRXX9qKKbR.WzrNQSOQdQWXmp65Mt/mam8xvKSMno.', 'Doctor', 'drhasanpiker', 'drhasanpiker@gmail.com'),
(308, 'adminguy', '$2y$10$aD8kiwa9euULcCmJR5vMke3CSgXBtn.27NVywelZI2hSYKYTVD/0a', 'Admin', 'adminguy', 'adminguy@gamil.com'),
(309, 'newadmin', '$2y$10$ywYqcsCAf3WkEU2fRqko7uSNGD3nOrwNKzxrKzdzRTuWKS/4ugsWy', 'Admin', 'newadmin', 'newadmin@gmail.com'),
(310, 'harryporter', '$2y$10$IMLzZIo5DHd/HJRRdI.aJ.lkdu4EyYv0kP31Lq/UJdj2wzpWe9P22', 'Patient', 'harryporter', 'harryporter@gmail.com'),
(311, 'drharryporter', '$2y$10$WaJsyVbqMIZaLDpcAHPRbe7yyDlacA0X7hMN.k.14cSfFM1lKXeBO', 'Doctor', 'drharryporter', 'drharryporter@gmail.com');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admission`
--
ALTER TABLE `admission`
  ADD PRIMARY KEY (`admission_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `bill`
--
ALTER TABLE `bill`
  ADD PRIMARY KEY (`bill_id`),
  ADD UNIQUE KEY `admission_id` (`admission_id`);

--
-- Indexes for table `consultation`
--
ALTER TABLE `consultation`
  ADD PRIMARY KEY (`consultation_id`),
  ADD KEY `admission_id` (`admission_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `doctor`
--
ALTER TABLE `doctor`
  ADD PRIMARY KEY (`doctor_id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `specialization_id` (`specialization_id`);

--
-- Indexes for table `patient`
--
ALTER TABLE `patient`
  ADD PRIMARY KEY (`patient_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `specialization`
--
ALTER TABLE `specialization`
  ADD PRIMARY KEY (`specialization_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admission`
--
ALTER TABLE `admission`
  MODIFY `admission_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=514;

--
-- AUTO_INCREMENT for table `bill`
--
ALTER TABLE `bill`
  MODIFY `bill_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=906;

--
-- AUTO_INCREMENT for table `consultation`
--
ALTER TABLE `consultation`
  MODIFY `consultation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=613;

--
-- AUTO_INCREMENT for table `doctor`
--
ALTER TABLE `doctor`
  MODIFY `doctor_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=206;

--
-- AUTO_INCREMENT for table `patient`
--
ALTER TABLE `patient`
  MODIFY `patient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=309;

--
-- AUTO_INCREMENT for table `specialization`
--
ALTER TABLE `specialization`
  MODIFY `specialization_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=312;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admission`
--
ALTER TABLE `admission`
  ADD CONSTRAINT `admission_doctor_fk` FOREIGN KEY (`doctor_id`) REFERENCES `doctor` (`doctor_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `admission_patient_fk` FOREIGN KEY (`patient_id`) REFERENCES `patient` (`patient_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `bill`
--
ALTER TABLE `bill`
  ADD CONSTRAINT `bill_admission_fk` FOREIGN KEY (`admission_id`) REFERENCES `admission` (`admission_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `consultation`
--
ALTER TABLE `consultation`
  ADD CONSTRAINT `consultation_admission_fk` FOREIGN KEY (`admission_id`) REFERENCES `admission` (`admission_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `consultation_doctor_fk` FOREIGN KEY (`doctor_id`) REFERENCES `doctor` (`doctor_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `doctor`
--
ALTER TABLE `doctor`
  ADD CONSTRAINT `doctor_specialization_fk` FOREIGN KEY (`specialization_id`) REFERENCES `specialization` (`specialization_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `doctor_user_fk` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `patient`
--
ALTER TABLE `patient`
  ADD CONSTRAINT `patient_user_fk` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
