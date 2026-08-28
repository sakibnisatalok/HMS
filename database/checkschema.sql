-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Aug 28, 2026 at 07:25 AM
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
  `provisional_diagnosis` varchar(255) DEFAULT NULL,
  `status` enum('Admitted','Discharged','Consult') DEFAULT NULL,
  `discharge_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admission`
--

INSERT INTO `admission` (`admission_id`, `patient_id`, `doctor_id`, `admission_date`, `admission_type`, `provisional_diagnosis`, `status`, `discharge_date`) VALUES
(501, 301, 201, '2026-08-01 09:30:00', 'Planned', 'Hypertension', 'Discharged', '2026-08-03 11:00:00'),
(502, 302, 202, '2026-08-04 14:00:00', 'Planned', 'Migraine', 'Discharged', '2026-08-05 12:30:00'),
(503, 303, 203, '2026-08-07 10:15:00', 'Planned', 'High fever and infection', 'Discharged', '2026-08-09 15:00:00'),
(504, 304, 203, '2026-08-10 16:30:00', 'Planned', 'Skin allergy', 'Discharged', '2026-08-11 13:00:00'),
(505, 305, 201, '2026-08-15 09:00:00', 'Planned', 'High cholesterol', 'Admitted', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `bill`
--

CREATE TABLE `bill` (
  `bill_id` int(11) NOT NULL,
  `admission_id` int(11) NOT NULL,
  `doctor_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `medicine_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_status` enum('Paid','Unpaid','Partial') NOT NULL DEFAULT 'Unpaid'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bill`
--

INSERT INTO `bill` (`bill_id`, `admission_id`, `doctor_fee`, `medicine_cost`, `total_amount`, `payment_status`) VALUES
(901, 501, 3000.00, 1200.00, 4200.00, 'Paid'),
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
(601, 501, 201, '2026-08-01 10:00:00', 'Mild hypertension', 'Approved'),
(602, 501, 201, '2026-08-02 10:30:00', 'Blood pressure improving', 'Approved'),
(603, 502, 202, '2026-08-04 15:00:00', 'Migraine without aura', 'Approved'),
(604, 503, 203, '2026-08-07 11:00:00', 'Viral fever', 'Approved'),
(605, 503, 203, '2026-08-08 10:00:00', 'Fever reduced', 'Approved'),
(606, 504, 203, '2026-08-10 17:00:00', 'Allergic dermatitis', 'Approved'),
(607, 505, 201, '2026-08-15 10:00:00', 'High cholesterol', 'Approved');

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
  `status` enum('Active','Inactive') DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `doctor`
--

INSERT INTO `doctor` (`doctor_id`, `user_id`, `specialization_id`, `designation`, `phone`, `status`) VALUES
(201, 201, 1, 'Consultant Cardiologist', '01711000001', 'Active'),
(202, 202, 2, 'Senior Neurologist', '01711000002', 'Active'),
(203, 203, 4, 'General Physician', '01711000003', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `medicine`
--

CREATE TABLE `medicine` (
  `medicine_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `form` varchar(40) DEFAULT NULL,
  `strength` varchar(40) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `medicine`
--

INSERT INTO `medicine` (`medicine_id`, `name`, `form`, `strength`) VALUES
(401, 'Paracetamol', 'Tablet', '500 mg'),
(402, 'Omeprazole', 'Capsule', '20 mg'),
(403, 'Amlodipine', 'Tablet', '5 mg'),
(404, 'Atorvastatin', 'Tablet', '20 mg'),
(405, 'Cetirizine', 'Tablet', '10 mg'),
(406, 'Azithromycin', 'Tablet', '500 mg'),
(407, 'Metformin', 'Tablet', '500 mg'),
(408, 'Clobetasol', 'Cream', '0.05%'),
(409, 'Pregabalin', 'Capsule', '75 mg'),
(410, 'Vitamin D3', 'Tablet', '1000 IU');

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
(301, 301, 'Male', '2001-05-14', 'B+', 'Mirpur, Dhaka', '01811000001'),
(302, 302, 'Female', '1999-08-22', 'A+', 'Uttara, Dhaka', '01811000002'),
(303, 303, 'Male', '2003-02-10', 'O+', 'Mohammadpur, Dhaka', '01811000003'),
(304, 304, 'Female', '1998-11-05', 'AB+', 'Dhanmondi, Dhaka', '01811000004'),
(305, 305, 'Male', '1995-06-18', 'B-', 'Badda, Dhaka', '01811000005'),
(306, 306, 'Female', '2026-08-26', 'B+', '108, east rampura, 1219,Dhaka,1219,Bangladesh', '12345678901');

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
CREATE TRIGGER `chk_patient_dob_update` BEFORE UPDATE ON `patient` FOR EACH ROW BEGIN
    IF NEW.date_of_birth > CURRENT_DATE() THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Date of birth cannot be in the future.';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `prescription`
--

CREATE TABLE `prescription` (
  `prescription_id` int(11) NOT NULL,
  `consultation_id` int(11) NOT NULL,
  `prescribed_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `prescription`
--

INSERT INTO `prescription` (`prescription_id`, `consultation_id`, `prescribed_at`) VALUES
(701, 601, '2026-08-01 10:15:00'),
(702, 602, '2026-08-02 10:45:00'),
(703, 603, '2026-08-04 15:15:00'),
(704, 604, '2026-08-07 11:15:00'),
(705, 605, '2026-08-08 10:15:00'),
(706, 606, '2026-08-10 17:15:00'),
(707, 607, '2026-08-15 10:15:00');

-- --------------------------------------------------------

--
-- Table structure for table `prescription_item`
--

CREATE TABLE `prescription_item` (
  `item_id` int(11) NOT NULL,
  `prescription_id` int(11) NOT NULL,
  `medicine_id` int(11) NOT NULL,
  `dosage` varchar(40) DEFAULT NULL,
  `frequency` varchar(40) DEFAULT NULL,
  `duration` varchar(40) DEFAULT NULL,
  `instruction` varchar(120) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `prescription_item`
--

INSERT INTO `prescription_item` (`item_id`, `prescription_id`, `medicine_id`, `dosage`, `frequency`, `duration`, `instruction`) VALUES
(801, 701, 403, '1 tablet', 'Once daily', '30 days', 'Take after breakfast'),
(802, 702, 403, '1 tablet', 'Once daily', '30 days', 'Take after breakfast'),
(803, 702, 404, '1 tablet', 'Once daily', '30 days', 'Take at night'),
(804, 703, 401, '1 tablet', 'When needed', '5 days', 'Take after food'),
(805, 703, 409, '1 capsule', 'Once daily', '7 days', 'Take at night'),
(806, 704, 401, '1 tablet', 'Three times daily', '5 days', 'Take after meals'),
(807, 704, 406, '1 tablet', 'Once daily', '3 days', 'Complete the course'),
(808, 705, 401, '1 tablet', 'Twice daily', '3 days', 'Take after meals'),
(809, 706, 408, 'Apply thin layer', 'Twice daily', '7 days', 'Apply to affected area'),
(810, 707, 404, '1 tablet', 'Once daily', '30 days', 'Take at night'),
(811, 707, 410, '1 tablet', 'Once daily', '30 days', 'Take after breakfast'),
(812, 707, 402, '1 capsule', 'Once daily', '14 days', 'Take before breakfast');

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
(301, 'patient01', '$2y$10$d4e5f6g7h8i9j0k1l2m3n.O4p5q6r7s8t9u0v1w2x3y4z5a6b7c8', 'Patient', 'Tanvir Ahmed', 'tanvir@gmail.com'),
(302, 'patient02', '$2y$10$e5f6g7h8i9j0k1l2m3n4o.P5q6r7s8t9u0v1w2x3y4z5a6b7c8d9', 'Patient', 'Nusrat Jahan', 'nusrat@gmail.com'),
(303, 'patient03', '$2y$10$f6g7h8i9j0k1l2m3n4o5p.Q6r7s8t9u0v1w2x3y4z5a6b7c8d9e0', 'Patient', 'Sakib Hossain', 'sakib@gmail.com'),
(304, 'patient04', '$2y$10$g7h8i9j0k1l2m3n4o5p6q.R7s8t9u0v1w2x3y4z5a6b7c8d9e0f1', 'Patient', 'Maliha Rahman', 'maliha@gmail.com'),
(305, 'patient05', '$2y$10$h8i9j0k1l2m3n4o5p6q7r.S8t9u0v1w2x3y4z5a6b7c8d9e0f1g2', 'Patient', 'Farhan Kabir', 'farhan@gmail.com'),
(306, 'abc123', '$2y$10$e8SZ9bVaS6DCWzIgYd0LZerYBum0J6//gnZs2L9Z0OWVEquw4gpKS', 'Patient', 'abc123', 'abc123@gmail.com');

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
-- Indexes for table `medicine`
--
ALTER TABLE `medicine`
  ADD PRIMARY KEY (`medicine_id`);

--
-- Indexes for table `patient`
--
ALTER TABLE `patient`
  ADD PRIMARY KEY (`patient_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `prescription`
--
ALTER TABLE `prescription`
  ADD PRIMARY KEY (`prescription_id`),
  ADD KEY `consultation_id` (`consultation_id`);

--
-- Indexes for table `prescription_item`
--
ALTER TABLE `prescription_item`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `prescription_id` (`prescription_id`),
  ADD KEY `medicine_id` (`medicine_id`);

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
  MODIFY `admission_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=506;

--
-- AUTO_INCREMENT for table `bill`
--
ALTER TABLE `bill`
  MODIFY `bill_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=906;

--
-- AUTO_INCREMENT for table `consultation`
--
ALTER TABLE `consultation`
  MODIFY `consultation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=608;

--
-- AUTO_INCREMENT for table `doctor`
--
ALTER TABLE `doctor`
  MODIFY `doctor_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=204;

--
-- AUTO_INCREMENT for table `medicine`
--
ALTER TABLE `medicine`
  MODIFY `medicine_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=411;

--
-- AUTO_INCREMENT for table `patient`
--
ALTER TABLE `patient`
  MODIFY `patient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=307;

--
-- AUTO_INCREMENT for table `prescription`
--
ALTER TABLE `prescription`
  MODIFY `prescription_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=708;

--
-- AUTO_INCREMENT for table `prescription_item`
--
ALTER TABLE `prescription_item`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=813;

--
-- AUTO_INCREMENT for table `specialization`
--
ALTER TABLE `specialization`
  MODIFY `specialization_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=307;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admission`
--
ALTER TABLE `admission`
  ADD CONSTRAINT `admission_doctor_fk` FOREIGN KEY (`doctor_id`) REFERENCES `doctor` (`doctor_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `admission_patient_fk` FOREIGN KEY (`patient_id`) REFERENCES `patient` (`patient_id`) ON UPDATE CASCADE;

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
  ADD CONSTRAINT `consultation_doctor_fk` FOREIGN KEY (`doctor_id`) REFERENCES `doctor` (`doctor_id`) ON UPDATE CASCADE;

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

--
-- Constraints for table `prescription`
--
ALTER TABLE `prescription`
  ADD CONSTRAINT `prescription_consultation_fk` FOREIGN KEY (`consultation_id`) REFERENCES `consultation` (`consultation_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `prescription_item`
--
ALTER TABLE `prescription_item`
  ADD CONSTRAINT `item_medicine_fk` FOREIGN KEY (`medicine_id`) REFERENCES `medicine` (`medicine_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `item_prescription_fk` FOREIGN KEY (`prescription_id`) REFERENCES `prescription` (`prescription_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
