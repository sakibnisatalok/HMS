<?php
session_start();
require_once '../../app/config/databaseconnection.php';

/** @var \PDO $pdo */

// Access Control Guard
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'doctor') {
    http_response_code(403);
    echo "Unauthorized";
    exit;
}

// Fetch doctor profile details joined with specialization
$stmt = $pdo->prepare("
    SELECT u.full_name, u.username, u.email, d.designation, d.phone, d.status, s.name AS specialization_name
    FROM user u
    LEFT JOIN doctor d ON u.user_id = d.user_id
    LEFT JOIN specialization s ON d.specialization_id = s.specialization_id
    WHERE u.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
?>

<h2>Doctor Profile</h2>
<hr style="margin: 15px 0;">
<div style="line-height: 2;">
    <p><strong>Full Name:</strong> <?= htmlspecialchars($profile['full_name'] ?? 'N/A') ?></p>
    <p><strong>Username:</strong> <?= htmlspecialchars($profile['username'] ?? 'N/A') ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($profile['email'] ?? 'N/A') ?></p>
    <p><strong>Designation:</strong> <?= htmlspecialchars($profile['designation'] ?? 'N/A') ?></p>
    <p><strong>Specialization:</strong> <?= htmlspecialchars($profile['specialization_name'] ?? 'None') ?></p>
    <p><strong>Phone Number:</strong> <?= htmlspecialchars($profile['phone'] ?? 'N/A') ?></p>
    <p><strong>Status:</strong> <span style="color: <?= ($profile['status'] ?? 'Active') === 'Active' ? 'green' : 'red' ?>; font-weight: bold;"><?= htmlspecialchars($profile['status'] ?? 'N/A') ?></span></p>
</div>
