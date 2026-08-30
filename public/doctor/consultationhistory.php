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
$doctor = $docStmt->fetch(PDO::FETCH_ASSOC);

if (!$doctor) {
    echo "<p style='color: red;'>Doctor record not found.</p>";
    exit;
}

$doctorId = (int)$doctor['doctor_id'];

// 3. Fetch All Recorded Consultations for this doctor
$histStmt = $pdo->prepare("
    SELECT c.consultation_id, c.consult_datetime, c.report, c.status AS consult_status,
           u.full_name AS patient_name, u.email AS patient_email, a.problem, a.admission_id
    FROM consultation c
    JOIN admission a ON c.admission_id = a.admission_id
    JOIN patient p ON a.patient_id = p.patient_id
    JOIN user u ON p.user_id = u.user_id
    WHERE c.doctor_id = ?
    ORDER BY c.consult_datetime DESC
");
$histStmt->execute([$doctorId]);
$history = $histStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
?>

<h2>Consultation History</h2>
<hr style="margin: 15px 0;">

<!-- Search Filter -->
<div style="margin-bottom: 20px; max-width: 400px;">
    <label for="consult-search" style="display: block; margin-bottom: 5px; font-weight: bold; font-size: 14px;">Search History</label>
    <input type="text" id="consult-search" placeholder="Search by patient name, email, report..." style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
</div>

<table border="1" cellpadding="10" cellspacing="0" style="width: 100%; border-collapse: collapse; text-align: left;" id="consult-history-table">
    <thead>
        <tr style="background-color: #f0fdfa; color: #115e59;">
            <th>ID</th>
            <th>Date & Time</th>
            <th>Patient Details</th>
            <th>Initial Problem</th>
            <th>Doctor Report / Diagnosis</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($history)): ?>
            <tr><td colspan="6" style="text-align: center; color: #6b7280;">No recorded consultation history found.</td></tr>
        <?php else: ?>
            <?php foreach ($history as $row): ?>
                <tr class="consult-row">
                    <td><strong>#<?= htmlspecialchars($row['consultation_id']) ?></strong></td>
                    <td><?= htmlspecialchars($row['consult_datetime']) ?></td>
                    <td>
                        <div style="font-weight: bold;"><?= htmlspecialchars($row['patient_name']) ?></div>
                        <div style="font-size: 12px; color: #6b7280;"><?= htmlspecialchars($row['patient_email'] ?? '') ?></div>
                    </td>
                    <td style="max-width: 200px; font-size: 13px;"><?= htmlspecialchars($row['problem'] ?? 'N/A') ?></td>
                    <td style="max-width: 280px;"><?= htmlspecialchars($row['report'] ?? 'N/A') ?></td>
                    <td>
                        <span style="display: inline-block; padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: bold; color: <?= $row['consult_status'] === 'Completed' ? '#15803d; background: #dcfce7;' : ($row['consult_status'] === 'Cancelled' ? '#b91c1c; background: #fee2e2;' : '#0369a1; background: #e0f2fe;') ?>">
                            <?= htmlspecialchars($row['consult_status']) ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<script>
(function() {
    const searchInput = document.getElementById('consult-search');
    const rows = document.querySelectorAll('.consult-row');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            rows.forEach(row => {
                const textContent = row.textContent.toLowerCase();
                if (textContent.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
})();
</script>
