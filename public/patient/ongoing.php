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

// 2. Fetch all ongoing / pending requests that have NOT been dealt with by a doctor yet
$stmt = $pdo->prepare("
    SELECT a.admission_id, a.admission_date, a.admission_type, a.problem, a.status AS admission_status,
           u.full_name AS doctor_name, s.name AS specialization_name, d.designation
    FROM admission a
    JOIN patient p ON a.patient_id = p.patient_id
    JOIN doctor d ON a.doctor_id = d.doctor_id
    JOIN user u ON d.user_id = u.user_id
    LEFT JOIN specialization s ON d.specialization_id = s.specialization_id
    LEFT JOIN consultation c ON a.admission_id = c.admission_id
    WHERE p.user_id = ? AND c.consultation_id IS NULL
    ORDER BY a.admission_date DESC
");
$stmt->execute([$_SESSION['user_id']]);
$ongoingRequests = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
?>

<h2>Ongoing Requests</h2>
<hr style="margin: 15px 0;">

<p style="color: #4b5563; margin-bottom: 20px;">
    These are your active consultation and admission requests awaiting doctor review.
</p>

<table border="1" cellpadding="10" cellspacing="0" style="width: 100%; border-collapse: collapse; text-align: left;">
    <thead>
        <tr style="background-color: #f3f4f6;">
            <th>ID</th>
            <th>Type</th>
            <th>Appointment / Admission Date</th>
            <th>Doctor Assigned</th>
            <th>Reported Problem</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($ongoingRequests)): ?>
            <tr><td colspan="6" style="text-align: center; color: #6b7280;">No pending or ongoing requests.</td></tr>
        <?php else: ?>
            <?php foreach ($ongoingRequests as $row): ?>
                <tr>
                    <td><strong>#<?= htmlspecialchars($row['admission_id']) ?></strong></td>
                    <td>
                        <span style="display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; background: <?= $row['admission_type'] === 'Planned' ? '#e0e7ff; color: #3730a3;' : '#fef3c7; color: #92400e;' ?>">
                            <?= $row['admission_type'] === 'Planned' ? 'Planned (OPD)' : 'Admit (In-Patient)' ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($row['admission_date']) ?></td>
                    <td>
                        <div style="font-weight: bold;">Dr. <?= htmlspecialchars($row['doctor_name']) ?></div>
                        <div style="font-size: 12px; color: #6b7280;"><?= htmlspecialchars($row['specialization_name'] ?? 'General') ?><?= !empty($row['designation']) ? ' - ' . htmlspecialchars($row['designation']) : '' ?></div>
                    </td>
                    <td style="max-width: 250px; font-size: 13px;"><?= htmlspecialchars($row['problem'] ?? 'None specified') ?></td>
                    <td>
                        <span style="display: inline-block; padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: bold; background: #fef3c7; color: #92400e;">
                            Pending Doctor Action
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
