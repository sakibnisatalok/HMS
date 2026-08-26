
<?php
session_start();
require_once '../../app/config/databaseconnection.php';

// Fetch profile data joined from user and patient tables[cite: 2]
$stmt = $pdo->prepare("
    SELECT u.full_name, u.username, u.email, p.gender, p.date_of_birth, p.blood_group, p.address, p.emergency_contact
    FROM user u
    LEFT JOIN patient p ON u.user_id = p.user_id
    WHERE u.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<h2>Patient Profile</h2>
<hr style="margin: 15px 0;">
<div style="line-height: 2;">
    <p><strong>Full Name:</strong> <?= htmlspecialchars($profile['full_name'] ?? 'N/A') ?></p>
    <p><strong>Username:</strong> <?= htmlspecialchars($profile['username'] ?? 'N/A') ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($profile['email'] ?? 'N/A') ?></p>
    <p><strong>Gender:</strong> <?= htmlspecialchars($profile['gender'] ?? 'N/A') ?></p>
    <p><strong>Date of Birth:</strong> <?= htmlspecialchars($profile['date_of_birth'] ?? 'N/A') ?></p>
    <p><strong>Blood Group:</strong> <?= htmlspecialchars($profile['blood_group'] ?? 'N/A') ?></p>
    <p><strong>Address:</strong> <?= htmlspecialchars($profile['address'] ?? 'N/A') ?></p>
    <p><strong>Emergency Contact:</strong> <?= htmlspecialchars($profile['emergency_contact'] ?? 'N/A') ?></p>
</div>