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

// Fetch all registered patients joined with their user profile info
$patStmt = $pdo->query("
    SELECT p.patient_id, u.full_name, u.username, u.email, 
           p.gender, p.date_of_birth, p.blood_group, p.address, p.emergency_contact
    FROM patient p
    JOIN user u ON p.user_id = u.user_id
    ORDER BY p.patient_id ASC
");
$patients = $patStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
?>

<h2>Patient Management</h2>
<hr style="margin: 15px 0;">

<!-- Search Section -->
<div style="margin-bottom: 20px; max-width: 400px;">
    <label for="admin-patient-search" style="display: block; margin-bottom: 5px; font-weight: bold; font-size: 14px;">Search Patients</label>
    <input type="text" id="admin-patient-search" placeholder="Search by name, username, email, phone..." style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
</div>

<!-- Patients Table -->
<table border="1" cellpadding="10" cellspacing="0" style="width: 100%; border-collapse: collapse; text-align: left;" id="admin-patients-table">
    <thead>
        <tr style="background-color: #f3f4f6;">
            <th>ID</th>
            <th>Name & Account</th>
            <th>Gender & DOB</th>
            <th>Blood Group</th>
            <th>Address</th>
            <th>Emergency Contact</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($patients)): ?>
            <tr><td colspan="6" style="text-align:center;">No patients registered in the system.</td></tr>
        <?php else: ?>
            <?php foreach ($patients as $row): ?>
                <tr class="admin-pat-row">
                    <td><strong>#<?= htmlspecialchars($row['patient_id']) ?></strong></td>
                    <td>
                        <div style="font-weight: bold;"><?= htmlspecialchars($row['full_name']) ?></div>
                        <div style="font-size: 12px; color: #6b7280;">@<?= htmlspecialchars($row['username']) ?> &bull; <?= htmlspecialchars($row['email'] ?? 'N/A') ?></div>
                    </td>
                    <td>
                        <div><?= htmlspecialchars($row['gender'] ?? 'N/A') ?></div>
                        <div style="font-size: 12px; color: #6b7280;">DOB: <?= htmlspecialchars($row['date_of_birth'] ?? 'N/A') ?></div>
                    </td>
                    <td>
                        <span style="font-weight: bold; color: #b91c1c;"><?= htmlspecialchars($row['blood_group'] ?? 'N/A') ?></span>
                    </td>
                    <td style="max-width: 200px; font-size: 13px;">
                        <?= htmlspecialchars($row['address'] ?? 'N/A') ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($row['emergency_contact'] ?? 'N/A') ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<script>
(function() {
    const searchInput = document.getElementById('admin-patient-search');
    const rows = document.querySelectorAll('.admin-pat-row');

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
