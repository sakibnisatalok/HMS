

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

// Fetch all specializations for filtering
$specStmt = $pdo->query("SELECT * FROM specialization ORDER BY name ASC");
$specializations = $specStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

// Fetch all doctors with their user and specialization details
$docStmt = $pdo->query("
    SELECT d.doctor_id, u.full_name, u.username, u.email, s.name AS specialization_name, 
           d.designation, d.phone, d.status, d.experience
    FROM doctor d
    JOIN user u ON d.user_id = u.user_id
    LEFT JOIN specialization s ON d.specialization_id = s.specialization_id
    ORDER BY d.doctor_id ASC
");
$doctors = $docStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
?>

<h2>Doctor Management</h2>
<hr style="margin: 15px 0;">

<!-- Filters Section -->
<div style="display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap;">
    <div style="flex: 1; min-width: 250px;">
        <label for="admin-doctor-search" style="display: block; margin-bottom: 5px; font-weight: bold; font-size: 14px;">Search Doctors</label>
        <input type="text" id="admin-doctor-search" placeholder="Search by name, username, email..." style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
    </div>
    <div style="width: 200px;">
        <label for="admin-spec-filter" style="display: block; margin-bottom: 5px; font-weight: bold; font-size: 14px;">Specialization</label>
        <select id="admin-spec-filter" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            <option value="">All Specializations</option>
            <?php foreach ($specializations as $spec): ?>
                <option value="<?= htmlspecialchars($spec['name']) ?>"><?= htmlspecialchars($spec['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<!-- Doctors Table -->
<table border="1" cellpadding="10" cellspacing="0" style="width: 100%; border-collapse: collapse; text-align: left;" id="admin-doctors-table">
    <thead>
        <tr style="background-color: #f3f4f6;">
            <th>ID</th>
            <th>Name & Username</th>
            <th>Specialization</th>
            <th>Designation & Experience</th>
            <th>Contact Details</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($doctors)): ?>
            <tr><td colspan="6" style="text-align:center;">No doctors registered in the system.</td></tr>
        <?php else: ?>
            <?php foreach ($doctors as $row): ?>
                <tr class="admin-doc-row" data-specialization="<?= htmlspecialchars($row['specialization_name'] ?? '') ?>">
                    <td><strong>#<?= htmlspecialchars($row['doctor_id']) ?></strong></td>
                    <td class="admin-doc-name">
                        <div style="font-weight: bold;"><?= htmlspecialchars($row['full_name']) ?></div>
                        <div style="font-size: 12px; color: #6b7280;">@<?= htmlspecialchars($row['username']) ?></div>
                    </td>
                    <td class="admin-doc-spec"><?= htmlspecialchars($row['specialization_name'] ?? 'N/A') ?></td>
                    <td>
                        <div><?= htmlspecialchars($row['designation'] ?? 'N/A') ?></div>
                        <div style="font-size: 12px; color: #6b7280;">Exp: <?= htmlspecialchars($row['experience'] ?? 'N/A') ?></div>
                    </td>
                    <td>
                        <div>Phone: <?= htmlspecialchars($row['phone'] ?? 'N/A') ?></div>
                        <div style="font-size: 12px; color: #6b7280;">Email: <?= htmlspecialchars($row['email'] ?? 'N/A') ?></div>
                    </td>
                    <td>
                        <span style="display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; color: <?= ($row['status'] ?? 'Active') === 'Active' ? '#15803d; background: #dcfce7;' : '#b91c1c; background: #fee2e2;' ?>">
                            <?= htmlspecialchars($row['status'] ?? 'Active') ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<script>
(function() {
    const searchInput = document.getElementById('admin-doctor-search');
    const specFilter = document.getElementById('admin-spec-filter');
    const rows = document.querySelectorAll('.admin-doc-row');

    function filterTable() {
        const query = (searchInput ? searchInput.value : '').toLowerCase().trim();
        const selectedSpec = specFilter ? specFilter.value : '';

        rows.forEach(row => {
            const textContent = row.textContent.toLowerCase();
            const specialization = row.getAttribute('data-specialization');

            const matchesSearch = textContent.includes(query);
            const matchesSpec = !selectedSpec || specialization === selectedSpec;

            if (matchesSearch && matchesSpec) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    if (searchInput) searchInput.addEventListener('input', filterTable);
    if (specFilter) specFilter.addEventListener('change', filterTable);
})();
</script>
