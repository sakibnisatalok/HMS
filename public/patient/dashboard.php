<?php
session_start();
require_once '../../app/config/databaseconnection.php';

/** @var \PDO $pdo */

// 1. Access Control Guard
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'patient') {
    http_response_code(403);
    echo "Unauthorized";
    exit;
}

$userId = $_SESSION['user_id'];

// 2. Count Ongoing / Pending Requests (not yet reviewed by doctor)
$ongoingStmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM admission a
    JOIN patient p ON a.patient_id = p.patient_id
    LEFT JOIN consultation c ON a.admission_id = c.admission_id
    WHERE p.user_id = ? AND c.consultation_id IS NULL
");
$ongoingStmt->execute([$userId]);
$ongoingCount = (int)$ongoingStmt->fetchColumn();

// 3. Count History Records (completed / reviewed by doctor)
$historyStmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM consultation c
    JOIN admission a ON c.admission_id = a.admission_id
    JOIN patient p ON a.patient_id = p.patient_id
    WHERE p.user_id = ?
");
$historyStmt->execute([$userId]);
$historyCount = (int)$historyStmt->fetchColumn();
?>

<h2>Patient Dashboard</h2>
<hr style="margin: 15px 0;">

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-top: 25px;">
    <!-- Stat Card: Ongoing Requests -->
    <div style="background: #fefce8; padding: 20px; border-radius: 8px; border-left: 5px solid #eab308; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <h4 style="color: #854d0e; margin-bottom: 8px; font-size: 14px; text-transform: uppercase;">Ongoing Requests</h4>
        <div style="font-size: 32px; font-weight: bold; color: #713f12;"><?= htmlspecialchars($ongoingCount) ?></div>
        <p style="color: #a16207; font-size: 12px; margin-top: 5px;">Pending doctor review</p>
    </div>

    <!-- Stat Card: History Records -->
    <div style="background: #eff6ff; padding: 20px; border-radius: 8px; border-left: 5px solid #2563eb; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <h4 style="color: #1e40af; margin-bottom: 8px; font-size: 14px; text-transform: uppercase;">History Records</h4>
        <div style="font-size: 32px; font-weight: bold; color: #1e3a8a;"><?= htmlspecialchars($historyCount) ?></div>
        <p style="color: #3b82f6; font-size: 12px; margin-top: 5px;">Reviewed consultations & admissions</p>
    </div>
</div>