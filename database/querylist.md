# Hospital Management System - Complete Query & SQL Reference

This document catalogs all database queries, triggers, and stored procedures used across the Hospital Management System, categorized by module and operational flow.

---

## Table of Contents
1. [Authentication & Registration Queries](#1-authentication--registration-queries)
2. [Admin Module Queries](#2-admin-module-queries)
3. [Doctor Module Queries](#3-doctor-module-queries)
4. [Patient Module Queries](#4-patient-module-queries)
5. [Database Triggers (Automatic Integrity Rules)](#5-database-triggers-automatic-integrity-rules)
6. [Stored Procedures](#6-stored-procedures)

---

## 1. Authentication & Registration Queries
**Location**: `public/login.php`

### 1.1 User Login Authentication
Verifies credentials by querying the user table using the submitted email address.
```sql
SELECT user_id, full_name, password_hash, role 
FROM user 
WHERE email = ?;
```

### 1.2 User Registration (Multi-Table Insertion)
Wrapped inside a database transaction (`BEGIN TRANSACTION` / `COMMIT`).
- **Step 1: Insert into Base `user` Table**
  ```sql
  INSERT INTO user (username, full_name, email, password_hash, role) 
  VALUES (?, ?, ?, ?, ?);
  ```
- **Step 2: Insert into Role-Specific Table (`doctor` or `patient`)**
  ```sql
  -- If Role is Doctor:
  INSERT INTO doctor (user_id) VALUES (?);

  -- If Role is Patient:
  INSERT INTO patient (user_id) VALUES (?);
  ```

---

## 2. Admin Module Queries
**Location**: `public/admin/`

### 2.1 Live System Statistics Overview (`dashboard.php`)
Calculates real-time counts across the entire hospital system.
```sql
SELECT COUNT(*) FROM doctor;
SELECT COUNT(*) FROM patient;
SELECT COUNT(*) FROM admission;
SELECT COUNT(*) FROM consultation;
```

### 2.2 Searchable Client Deletion Directory (`deleteclient.php`)
Fetches all doctors and patients with their associated contacts for management.
```sql
SELECT u.user_id, u.username, u.full_name, u.email, u.role,
       COALESCE(d.phone, p.emergency_contact, 'N/A') AS phone,
       COALESCE(s.name, 'N/A') AS extra_info
FROM user u
LEFT JOIN doctor d ON u.user_id = d.user_id
LEFT JOIN specialization s ON d.specialization_id = s.specialization_id
LEFT JOIN patient p ON u.user_id = p.user_id
WHERE u.role IN ('Doctor', 'Patient')
ORDER BY u.user_id DESC;
```

### 2.3 Cascading Account Deletion (`deleteclient.php`)
Safely deletes a client account; MySQL foreign keys with `ON DELETE CASCADE` automatically clean up all associated admissions and consultation history.
```sql
-- Step 1: Pre-check user role
SELECT user_id, full_name, username, role 
FROM user 
WHERE user_id = ?;

-- Step 2: Delete from user table (cascades to doctor/patient/admission/consultation)
DELETE FROM user 
WHERE user_id = ? AND role IN ('Doctor', 'Patient');
```

### 2.4 Doctor Directory with Specialization (`doctors.php`)
Retrieves all doctors joined with their specialization category.
```sql
SELECT d.doctor_id, u.full_name, u.username, u.email, 
       s.name AS specialization_name, d.designation, d.phone, d.status, d.experience
FROM doctor d
JOIN user u ON d.user_id = u.user_id
LEFT JOIN specialization s ON d.specialization_id = s.specialization_id
ORDER BY d.doctor_id ASC;
```

### 2.5 Patient Directory (`patients.php`)
Fetches all patient records joined with user profile data.
```sql
SELECT p.patient_id, u.full_name, u.username, u.email, 
       p.gender, p.date_of_birth, p.blood_group, p.address, p.emergency_contact
FROM patient p
JOIN user u ON p.user_id = u.user_id
ORDER BY p.patient_id ASC;
```

---

## 3. Doctor Module Queries
**Location**: `public/doctor/`

### 3.1 Doctor ID Resolution & Dashboard Stats (`dashboard.php`)
Resolves the logged-in user's doctor ID and counts pending work queues.
```sql
-- Find doctor_id
SELECT doctor_id FROM doctor WHERE user_id = ?;

-- Count Pending OPD Consultations
SELECT COUNT(*) 
FROM admission a
LEFT JOIN consultation c ON a.admission_id = c.admission_id
WHERE a.doctor_id = ? 
  AND a.admission_type = 'Planned'
  AND (c.consultation_id IS NULL OR c.status = 'Approved');

-- Count Pending In-Patient Admissions
SELECT COUNT(*) 
FROM admission a
LEFT JOIN consultation c ON a.admission_id = c.admission_id
WHERE a.doctor_id = ? 
  AND a.admission_type = 'Admit' 
  AND a.status = 'Admitted'
  AND (c.consultation_id IS NULL OR c.status != 'Approved');

-- Count Total Consultation History
SELECT COUNT(*) FROM consultation WHERE doctor_id = ?;
```

### 3.2 In-Patient Admission Request Management (`admissionreq.php`)
- **Fetch In-Patient Queue**:
  ```sql
  SELECT a.admission_id, u.full_name AS patient_name, u.email AS patient_email,
         p.gender, p.blood_group, p.emergency_contact,
         a.admission_date, a.problem, a.status AS admission_status,
         c.consultation_id, c.report, c.status AS consultation_status
  FROM admission a
  JOIN patient p ON a.patient_id = p.patient_id
  JOIN user u ON p.user_id = u.user_id
  LEFT JOIN consultation c ON a.admission_id = c.admission_id
  WHERE a.doctor_id = ? AND a.admission_type = 'Admit'
  ORDER BY a.admission_date DESC;
  ```
- **Approve or Discharge In-Patient**:
  ```sql
  UPDATE admission 
  SET status = ? 
  WHERE admission_id = ? AND doctor_id = ?;
  ```
- **Create or Update Admission Treatment Record**:
  ```sql
  -- If consultation record exists:
  UPDATE consultation 
  SET consult_datetime = NOW(), report = ?, status = ? 
  WHERE admission_id = ?;

  -- If consultation record is new:
  INSERT INTO consultation (admission_id, doctor_id, consult_datetime, report, status) 
  VALUES (?, ?, NOW(), ?, ?);
  ```

### 3.3 OPD Consultation Requests & Clinical Diagnosis (`consultationreq.php`)
- **Fetch OPD Queue**:
  ```sql
  SELECT a.admission_id, u.full_name AS patient_name, u.email AS patient_email, 
         p.gender, p.date_of_birth, p.blood_group,
         a.admission_date, a.problem, a.status AS admission_status,
         c.consultation_id, c.report, c.status AS consult_status, c.consult_datetime
  FROM admission a
  JOIN patient p ON a.patient_id = p.patient_id
  JOIN user u ON p.user_id = u.user_id
  LEFT JOIN consultation c ON a.admission_id = c.admission_id
  WHERE a.doctor_id = ? AND a.admission_type = 'Planned'
  ORDER BY a.admission_date DESC;
  ```
- **Save / Update Consultation Diagnosis & Status**:
  ```sql
  -- Update existing report:
  UPDATE consultation 
  SET report = ?, status = ?, consult_datetime = ? 
  WHERE consultation_id = ?;

  -- Create initial report:
  INSERT INTO consultation (admission_id, doctor_id, consult_datetime, report, status) 
  VALUES (?, ?, ?, ?, ?);
  ```

### 3.4 Doctor History Log (`history.php`)
Retrieves unified treatment and consultation history for the doctor.
```sql
SELECT c.consultation_id, c.consult_datetime, c.report, c.status AS consult_status,
       a.admission_id, a.admission_date, a.admission_type, a.problem, a.status AS admission_status, a.discharge_date,
       u.full_name AS patient_name, u.email AS patient_email,
       p.gender, p.blood_group, p.emergency_contact
FROM consultation c
JOIN admission a ON c.admission_id = a.admission_id
JOIN patient p ON a.patient_id = p.patient_id
JOIN user u ON p.user_id = u.user_id
WHERE c.doctor_id = ?
ORDER BY c.consult_datetime DESC;
```

### 3.5 Doctor Profile & Demographics Update (`profile.php`, `edit.php`)
```sql
-- View Doctor Profile
SELECT u.full_name, u.username, u.email, d.designation, d.phone, d.status, d.experience, s.name AS specialization_name
FROM doctor d
JOIN user u ON d.user_id = u.user_id
LEFT JOIN specialization s ON d.specialization_id = s.specialization_id
WHERE d.user_id = ?;

-- Update Doctor Profile
UPDATE doctor 
SET phone = ?, designation = ?, specialization_id = ?, experience = ? 
WHERE user_id = ?;
```

---

## 4. Patient Module Queries
**Location**: `public/patient/`

### 4.1 Patient Dashboard Stats (`dashboard.php`)
```sql
-- Count Pending Appointment Requests (Not Yet Consulted)
SELECT COUNT(*) 
FROM admission a
LEFT JOIN consultation c ON a.admission_id = c.admission_id
WHERE a.patient_id = ? AND c.consultation_id IS NULL;

-- Count Completed Consultation History
SELECT COUNT(*) 
FROM consultation c
JOIN admission a ON c.admission_id = a.admission_id
WHERE a.patient_id = ?;
```

### 4.2 Submit Appointment / Admission Request (`admission.php`)
```sql
-- Step 1: Verify Doctor Status
SELECT doctor_id FROM doctor WHERE doctor_id = ? AND status = 'Active';

-- Step 2: Insert Admission/Appointment Record
INSERT INTO admission (patient_id, doctor_id, admission_date, admission_type, problem, status) 
VALUES (?, ?, ?, ?, ?, ?);
```

### 4.3 Doctor Search & Directory (`doctorlist.php`)
```sql
SELECT d.doctor_id, u.full_name, s.name AS specialization_name, d.designation, d.phone, u.email
FROM doctor d
JOIN user u ON d.user_id = u.user_id
LEFT JOIN specialization s ON d.specialization_id = s.specialization_id
WHERE d.status = 'Active'
ORDER BY u.full_name ASC;
```

### 4.4 Patient Demographic Profile & Updates (`profile.php`, `edit.php`)
```sql
-- View Patient Profile
SELECT u.full_name, u.username, u.email, p.gender, p.date_of_birth, p.blood_group, p.address, p.emergency_contact
FROM patient p
JOIN user u ON p.user_id = u.user_id
WHERE p.user_id = ?;

-- Update Patient Demographics (Triggers fire validation on this query)
UPDATE patient 
SET gender = ?, date_of_birth = ?, blood_group = ?, address = ?, emergency_contact = ? 
WHERE user_id = ?;
```

### 4.5 Patient Medical History View (`history.php`)
Allows patients to view their doctor diagnoses, clinical notes, and treatment dates.
```sql
SELECT c.*, u.full_name AS doctor_name
FROM consultation c
JOIN doctor d ON c.doctor_id = d.doctor_id
JOIN user u ON d.user_id = u.user_id
JOIN admission a ON c.admission_id = a.admission_id
WHERE a.patient_id = ?
ORDER BY c.consult_datetime DESC;
```

---

## 5. Database Triggers (Automatic Integrity Rules)

### 5.1 Prevent Future Date of Birth (`BEFORE INSERT ON patient`)
Prevents inserting an invalid date of birth that is ahead of today's date.
```sql
DELIMITER $$
CREATE TRIGGER `chk_patient_dob_insert` 
BEFORE INSERT ON `patient` 
FOR EACH ROW 
BEGIN
    IF NEW.date_of_birth > CURRENT_DATE() THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Date of birth cannot be in the future.';
    END IF;
END$$
DELIMITER ;
```

### 5.2 Patient Update Multi-Rule Validation (`BEFORE UPDATE ON patient`)
Validates date of birth, medical blood group whitelist, and phone number length upon profile updates.
```sql
DELIMITER $$
CREATE TRIGGER `chk_patient_update_validation` 
BEFORE UPDATE ON `patient` 
FOR EACH ROW 
BEGIN
    -- Rule 1: Check Future Date of Birth
    IF NEW.date_of_birth IS NOT NULL AND NEW.date_of_birth > CURRENT_DATE() THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Trigger Error: Date of birth cannot be in the future.';
    END IF;

    -- Rule 2: Check Valid Medical Blood Group Whitelist
    IF NEW.blood_group IS NOT NULL AND NEW.blood_group != '' 
       AND NEW.blood_group NOT IN ('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Trigger Error: Invalid blood group. Must be A+, A-, B+, B-, AB+, AB-, O+, or O-.';
    END IF;

    -- Rule 3: Check Emergency Contact Minimum Length (At least 11 digits)
    IF NEW.emergency_contact IS NOT NULL AND NEW.emergency_contact != '' 
       AND CHAR_LENGTH(NEW.emergency_contact) < 11 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Trigger Error: Emergency contact must be at least 11 digits.';
    END IF;
END$$
DELIMITER ;
```

---

## 6. Stored Procedures

### 6.1 `GetAllPatientDetails`
Encapsulates patient and user table join logic into a single reusable database routine.
```sql
DROP PROCEDURE IF EXISTS `GetAllPatientDetails`;

DELIMITER $$

CREATE PROCEDURE `GetAllPatientDetails`()
BEGIN
    SELECT p.patient_id,
           u.full_name,
           u.username,
           u.email,
           p.gender,
           p.date_of_birth,
           p.blood_group,
           p.address,
           p.emergency_contact
    FROM patient p
    JOIN `user` u ON p.user_id = u.user_id
    ORDER BY p.patient_id ASC;
END$$

DELIMITER ;
```
**PHP Execution**:
```php
$patStmt = $pdo->query("CALL GetAllPatientDetails()");
$patients = $patStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
$patStmt->closeCursor();
```
