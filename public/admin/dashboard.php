<?php
session_start();
require_once '../../app/config/databaseconnection.php';

/** @var \PDO $pdo */

// Access Control Guard
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'admin') {
    http_response_code(403);
    echo "Unauthorized";
    exit;
}

// Fetch general system statistics
$totalDoctors = $pdo->query("SELECT COUNT(*) FROM doctor")->fetchColumn() ?? 0;
$totalPatients = $pdo->query("SELECT COUNT(*) FROM patient")->fetchColumn() ?? 0;
$totalAdmissions = $pdo->query("SELECT COUNT(*) FROM admission")->fetchColumn() ?? 0;
$totalConsultations = $pdo->query("SELECT COUNT(*) FROM consultation")->fetchColumn() ?? 0;
?>

<h2>Admin Dashboard</h2>
<hr style="margin: 15px 0;">
<p>Welcome to the Hospital Management System Administration Portal.</p>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 25px;">
    <!-- Stat Card: Doctors -->
    <div style="background: #eef2ff; padding: 20px; border-radius: 8px; border-left: 5px solid #4f46e5;">
        <h4 style="color: #4338ca; margin-bottom: 8px; font-size: 14px; text-transform: uppercase;">Total Doctors</h4>
        <div style="font-size: 28px; font-weight: bold; color: #1e1b4b;"><?= htmlspecialchars($totalDoctors) ?></div>
    </div>

    <!-- Stat Card: Patients -->
    <div style="background: #eff6ff; padding: 20px; border-radius: 8px; border-left: 5px solid #2563eb;">
        <h4 style="color: #1d4ed8; margin-bottom: 8px; font-size: 14px; text-transform: uppercase;">Total Patients</h4>
        <div style="font-size: 28px; font-weight: bold; color: #1e3a8a;"><?= htmlspecialchars($totalPatients) ?></div>
    </div>

    <!-- Stat Card: Admissions -->
    <div style="background: #f0fdf4; padding: 20px; border-radius: 8px; border-left: 5px solid #16a34a;">
        <h4 style="color: #15803d; margin-bottom: 8px; font-size: 14px; text-transform: uppercase;">Total Admissions</h4>
        <div style="font-size: 28px; font-weight: bold; color: #14532d;"><?= htmlspecialchars($totalAdmissions) ?></div>
    </div>

    <!-- Stat Card: Consultations -->
    <div style="background: #fefce8; padding: 20px; border-radius: 8px; border-left: 5px solid #ca8a04;">
        <h4 style="color: #a16207; margin-bottom: 8px; font-size: 14px; text-transform: uppercase;">Consultations</h4>
        <div style="font-size: 28px; font-weight: bold; color: #713f12;"><?= htmlspecialchars($totalConsultations) ?></div>
    </div>
</div>

<div style="margin-top: 30px; background: #fafafa; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px;">
    <h3 style="color: #111827; margin-bottom: 10px;">Quick Management</h3>
    <p style="color: #4b5563; font-size: 14px; line-height: 1.6; margin-bottom: 15px;">
        From this portal, you can monitor hospital operations, view complete directory records for registered doctors, review patient account details, or register new clients.
    </p>
    <button type="button" onclick="document.querySelector('.nav-link[data-page=\'addclient\']')?.click();" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; background-color: #4f46e5; color: #ffffff; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 14px; transition: background-color 0.2s;">
        + Add New Client (Doctor / Patient)
    </button>
</div>
