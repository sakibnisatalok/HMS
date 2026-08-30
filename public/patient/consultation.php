
<?php
session_start();
require_once '../../app/config/databaseconnection.php';

/** @var \PDO $pdo */

// Fetch consultation records linked to patient via admission[cite: 2]
$stmt = $pdo->prepare("
    SELECT c.*, u.full_name AS doctor_name
    FROM consultation c
    JOIN admission a ON c.admission_id = a.admission_id
    JOIN patient p ON a.patient_id = p.patient_id
    JOIN doctor d ON c.doctor_id = d.doctor_id
    JOIN user u ON d.user_id = u.user_id
    WHERE p.user_id = ?
    ORDER BY c.consult_datetime DESC
");
$stmt->execute([$_SESSION['user_id']]);
$consultations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Consultation Records</h2>
<hr style="margin: 15px 0;">
<table border="1" cellpadding="10" cellspacing="0" style="width: 100%; border-collapse: collapse; text-align: left;">
    <thead>
        <tr style="background-color: #f3f4f6;">
            <th>Date & Time</th>
            <th>Doctor</th>
            <th>Report</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($consultations)): ?>
            <tr><td colspan="4" style="text-align:center;">No consultation records found.</td></tr>
        <?php else: ?>
            <?php foreach ($consultations as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['consult_datetime']) ?></td>
                    <td><?= htmlspecialchars($row['doctor_name']) ?></td>
                    <td><?= htmlspecialchars($row['report'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($row['status']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>