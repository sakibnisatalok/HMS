
-- Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS = 0;

-- Wipe existing data and reset user_id auto-increment to 1
TRUNCATE TABLE user;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Insert seed accounts
INSERT INTO `user` (`username`, `password_hash`, `role`, `full_name`, `email`) VALUES
-- Admin (Password: Admin@123)
('admin', '$2y$10$4.aGZJ1l4R4S1K.V1I/0.O7A/U1e.S2k1M/9O9/0z1A2B3C4D5E6F', 'Admin', 'System Administrator', 'admin@hospital.com'),

-- Doctors (Password: Doctor@123)
('dr_smith', '$2y$10$W7.k8d5/mG.L3P0O1N.u/e9J0K1L2M3N4O5P6Q7R8S9T0U1V2W3X4', 'Doctor', 'Dr. John Smith', 'doctor.smith@hospital.com'),
('dr_sarah', '$2y$10$W7.k8d5/mG.L3P0O1N.u/e9J0K1L2M3N4O5P6Q7R8S9T0U1V2W3X4', 'Doctor', 'Dr. Sarah Connor', 'doctor.sarah@hospital.com'),

-- Patients (Password: Patient@123)
('patient_john', '$2y$10$3X2Y1Z0a9b8c7d6e5f4g3.h2i1j0k9l8m7n6o5p4q3r2s1t0u1v2w', 'Patient', 'John Doe', 'patient.john@gmail.com'),
('patient_emily', '$2y$10$3X2Y1Z0a9b8c7d6e5f4g3.h2i1j0k9l8m7n6o5p4q3r2s1t0u1v2w', 'Patient', 'Emily Watson', 'patient.emily@gmail.com');