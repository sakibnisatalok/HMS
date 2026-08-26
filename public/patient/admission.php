<?php
session_start();
require_once '../../app/config/databaseconnection.php';

/** @var \PDO $pdo */

// Fetch admission records matching patient schema[cite: 2]
$stmt = $pdo->prepare("
    SELECT a.*, u.full_name AS doctor_name 
    FROM admission a
    JOIN patient p ON a.patient_id = p.patient_id
    JOIN doctor d ON a.doctor_id = d.doctor_id
    JOIN user u ON d.user_id = u.user_id
    WHERE p.user_id = ?
    ORDER BY a.admission_date DESC
");
$stmt->execute([$_SESSION['user_id']]);
$admissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Admission History</h2>
<hr style="margin: 15px 0;">
<table border="1" cellpadding="10" cellspacing="0" style="width: 100%; border-collapse: collapse; text-align: left;">
    <thead>
        <tr style="background-color: #f3f4f6;">
            <th>Admission Date</th>
            <th>Type</th>
            <th>Doctor</th>
            <th>Provisional Diagnosis</th>
            <th>Status</th>
            <th>Discharge Date</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($admissions)): ?>
            <tr><td colspan="6" style="text-align:center;">No admission records found.</td></tr>
        <?php else: ?>
            <?php foreach ($admissions as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['admission_date']) ?></td>
                    <td><?= htmlspecialchars($row['admission_type']) ?></td>
                    <td><?= htmlspecialchars($row['doctor_name']) ?></td>
                    <td><?= htmlspecialchars($row['provisional_diagnosis']) ?></td>
                    <td><b><?= htmlspecialchars($row['status']) ?></b></td>
                    <td><?= htmlspecialchars($row['discharge_date'] ?? 'N/A') ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>