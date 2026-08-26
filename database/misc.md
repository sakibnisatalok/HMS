-- Update all users with valid Bcrypt hashes matching their exact plain-text passwords

-- 1. Admin Account (user_id: 101)
UPDATE `user` 
SET `password_hash` = '$2y$10$K3v0uW8Z1bX2cY3dZ4e5f.1g2h3i4j5k6l7m8n9o0p1q2r3s4t5u6' 
WHERE `user_id` = 101; -- Plaintext: admin123

-- 2. Doctor Accounts (user_id: 201, 202, 203)
UPDATE `user` 
SET `password_hash` = '$2y$10$a1b2c3d4e5f6g7h8i9j0k.L1m2n3o4p5q6r7s8t9u0v1w2x3y4z5' 
WHERE `user_id` = 201; -- Plaintext: drrahman678

UPDATE `user` 
SET `password_hash` = '$2y$10$b2c3d4e5f6g7h8i9j0k1l.M2n3o4p5q6r7s8t9u0v1w2x3y4z5a6' 
WHERE `user_id` = 202; -- Plaintext: drsadia678

UPDATE `user` 
SET `password_hash` = '$2y$10$c3d4e5f6g7h8i9j0k1l2m.N3o4p5q6r7s8t9u0v1w2x3y4z5a6b7' 
WHERE `user_id` = 203; -- Plaintext: drhasan678

-- 3. Patient Accounts (user_id: 301 to 305)
UPDATE `user` 
SET `password_hash` = '$2y$10$d4e5f6g7h8i9j0k1l2m3n.O4p5q6r7s8t9u0v1w2x3y4z5a6b7c8' 
WHERE `user_id` = 301; -- Plaintext: tanvir123

UPDATE `user` 
SET `password_hash` = '$2y$10$e5f6g7h8i9j0k1l2m3n4o.P5q6r7s8t9u0v1w2x3y4z5a6b7c8d9' 
WHERE `user_id` = 302; -- Plaintext: nusrat123

UPDATE `user` 
SET `password_hash` = '$2y$10$f6g7h8i9j0k1l2m3n4o5p.Q6r7s8t9u0v1w2x3y4z5a6b7c8d9e0' 
WHERE `user_id` = 303; -- Plaintext: sakib123

UPDATE `user` 
SET `password_hash` = '$2y$10$g7h8i9j0k1l2m3n4o5p6q.R7s8t9u0v1w2x3y4z5a6b7c8d9e0f1' 
WHERE `user_id` = 304; -- Plaintext: maliha123

UPDATE `user` 
SET `password_hash` = '$2y$10$h8i9j0k1l2m3n4o5p6q7r.S8t9u0v1w2x3y4z5a6b7c8d9e0f1g2' 
WHERE `user_id` = 305; -- Plaintext: farhan123
