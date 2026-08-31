-- This stored procedure retrieves and returns details of all registered patients, including their full name, username, email, gender, birth date, blood group, address, and emergency contact from the database.

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
