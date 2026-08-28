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

// Fetch all specializations for filtering
$specStmt = $pdo->query("SELECT * FROM specialization ORDER BY name ASC");
$specializations = $specStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all active doctors
$docStmt = $pdo->query("
    SELECT d.doctor_id, u.full_name, s.name AS specialization_name, d.designation, d.phone, u.email
    FROM doctor d
    JOIN user u ON d.user_id = u.user_id
    LEFT JOIN specialization s ON d.specialization_id = s.specialization_id
    WHERE d.status = 'Active'
    ORDER BY u.full_name ASC
");
$doctors = $docStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Find Doctors</h2>
<hr style="margin: 15px 0;">

<!-- Filters Section -->
<div style="display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap;">
    <div style="flex: 1; min-width: 250px;">
        <label for="doctor-search" style="display: block; margin-bottom: 5px; font-weight: bold; font-size: 14px;">Search Doctors</label>
        <input type="text" id="doctor-search" placeholder="Search by name, designation..." style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
    </div>
    <div style="width: 200px;">
        <label for="specialization-filter" style="display: block; margin-bottom: 5px; font-weight: bold; font-size: 14px;">Specialization</label>
        <select id="specialization-filter" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
            <option value="">All Specializations</option>
            <?php foreach ($specializations as $spec): ?>
                <option value="<?= htmlspecialchars($spec['name']) ?>"><?= htmlspecialchars($spec['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<!-- Doctors Table -->
<table border="1" cellpadding="10" cellspacing="0" style="width: 100%; border-collapse: collapse; text-align: left;" id="doctors-table">
    <thead>
        <tr style="background-color: #f3f4f6;">
            <th>Doctor ID</th>
            <th>Name</th>
            <th>Specialization</th>
            <th>Designation</th>
            <th>Contact Details</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($doctors)): ?>
            <tr><td colspan="5" style="text-align:center;">No active doctors found.</td></tr>
        <?php else: ?>
            <?php foreach ($doctors as $row): ?>
                <tr class="doctor-row" data-specialization="<?= htmlspecialchars($row['specialization_name'] ?? '') ?>">
                    <td class="doctor-id"><b><?= htmlspecialchars($row['doctor_id']) ?></b></td>
                    <td class="doctor-name"><?= htmlspecialchars($row['full_name']) ?></td>
                    <td class="doctor-spec"><?= htmlspecialchars($row['specialization_name'] ?? 'N/A') ?></td>
                    <td class="doctor-desg"><?= htmlspecialchars($row['designation'] ?? 'N/A') ?></td>
                    <td>
                        <div>Phone: <?= htmlspecialchars($row['phone'] ?? 'N/A') ?></div>
                        <div style="font-size: 12px; color: #666;">Email: <?= htmlspecialchars($row['email'] ?? 'N/A') ?></div>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<!-- Client-side filtering script -->
<script>
(function() {
    const searchInput = document.getElementById('doctor-search');
    const specFilter = document.getElementById('specialization-filter');
    const rows = document.querySelectorAll('.doctor-row');

    function filterTable() {
        const searchQuery = searchInput.value.toLowerCase().trim();
        const selectedSpec = specFilter.value;

        rows.forEach(row => {
            const name = row.querySelector('.doctor-name').textContent.toLowerCase();
            const designation = row.querySelector('.doctor-desg').textContent.toLowerCase();
            const specialization = row.getAttribute('data-specialization');

            const matchesSearch = name.includes(searchQuery) || designation.includes(searchQuery);
            const matchesSpec = !selectedSpec || specialization === selectedSpec;

            if (matchesSearch && matchesSpec) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    if (searchInput && specFilter) {
        searchInput.addEventListener('input', filterTable);
        specFilter.addEventListener('change', filterTable);
    }
})();
</script>
