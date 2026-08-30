<?php
session_start();
require_once '../../app/config/databaseconnection.php';

/** @var \PDO $pdo */

// 1. Access Control Guard
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'doctor') {
    http_response_code(403);
    echo "Unauthorized";
    exit;
}

// 2. Fetch doctor_id for the logged-in doctor
$docStmt = $pdo->prepare("SELECT doctor_id FROM doctor WHERE user_id = ?");
$docStmt->execute([$_SESSION['user_id']]);
$doctorId = (int)$docStmt->fetchColumn();

// 3. Count Pending Consultation Requests (not yet interacted/recorded by doctor)
$cReqStmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM admission a
    LEFT JOIN consultation c ON a.admission_id = c.admission_id
    WHERE a.doctor_id = ? AND a.status = 'Consult' AND c.consultation_id IS NULL
");
$cReqStmt->execute([$doctorId]);
$consultationCount = (int)$cReqStmt->fetchColumn();

// 4. Count Pending Admission Requests (not yet interacted/approved/cancelled by doctor)
$admReqStmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM admission a
    LEFT JOIN consultation c ON a.admission_id = c.admission_id
    WHERE a.doctor_id = ? AND a.admission_type = 'Admit' AND c.consultation_id IS NULL
");
$admReqStmt->execute([$doctorId]);
$admissionCount = (int)$admReqStmt->fetchColumn();

// 5. Count History Records (all requests doctor has interacted with & recorded)
$histStmt = $pdo->prepare("SELECT COUNT(*) FROM consultation WHERE doctor_id = ?");
$histStmt->execute([$doctorId]);
$historyCount = (int)$histStmt->fetchColumn();
?>

<h2>Doctor Dashboard</h2>
<hr style="margin: 15px 0;">

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-top: 25px;">
    <!-- Stat Card: Pending Consultation Requests -->
    <div style="background: #f0fdfa; padding: 20px; border-radius: 8px; border-left: 5px solid #0d9488; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <h4 style="color: #0f766e; margin-bottom: 8px; font-size: 14px; text-transform: uppercase;">Consultation Requests</h4>
        <div style="font-size: 32px; font-weight: bold; color: #115e59;"><?= htmlspecialchars($consultationCount) ?></div>
        <p style="color: #14b8a6; font-size: 12px; margin-top: 5px;">Pending doctor review</p>
    </div>

    <!-- Stat Card: Pending Admission Requests -->
    <div style="background: #fefce8; padding: 20px; border-radius: 8px; border-left: 5px solid #eab308; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <h4 style="color: #854d0e; margin-bottom: 8px; font-size: 14px; text-transform: uppercase;">Admission Requests</h4>
        <div style="font-size: 32px; font-weight: bold; color: #713f12;"><?= htmlspecialchars($admissionCount) ?></div>
        <p style="color: #a16207; font-size: 12px; margin-top: 5px;">Pending doctor action</p>
    </div>

    <!-- Stat Card: History Records -->
    <div style="background: #eff6ff; padding: 20px; border-radius: 8px; border-left: 5px solid #2563eb; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <h4 style="color: #1e40af; margin-bottom: 8px; font-size: 14px; text-transform: uppercase;">History Records</h4>
        <div style="font-size: 32px; font-weight: bold; color: #1e3a8a;"><?= htmlspecialchars($historyCount) ?></div>
        <p style="color: #3b82f6; font-size: 12px; margin-top: 5px;">Interacted & recorded</p>
    </div>
</div>
