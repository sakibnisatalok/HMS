<?php
session_start();
require_once '../../app/config/databaseconnection.php';

/** @var \PDO $pdo */

// 1. Access Control Guard
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'admin') {
    http_response_code(403);
    echo "Unauthorized";
    exit;
}

$message = '';
$messageType = '';

// 2. Handle Deletion Request (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $targetUserId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    $action = $_POST['action'] ?? '';

    if ($action === 'delete' && $targetUserId) {
        // Prevent deleting the currently logged-in Admin
        if ($targetUserId === (int)$_SESSION['user_id']) {
            $message = "Error: You cannot delete your own administrative account.";
            $messageType = "error";
        } else {
            try {
                // Fetch target user info first for feedback
                $chkStmt = $pdo->prepare("SELECT user_id, full_name, username, role FROM user WHERE user_id = ?");
                $chkStmt->execute([$targetUserId]);
                $targetUser = $chkStmt->fetch(PDO::FETCH_ASSOC);

                if (!$targetUser) {
                    $message = "Error: Client not found.";
                    $messageType = "error";
                } elseif (strtolower($targetUser['role']) === 'admin') {
                    $message = "Error: Administrative accounts cannot be deleted through this interface.";
                    $messageType = "error";
                } else {
                    // Perform CASCADE delete directly from user table
                    $delStmt = $pdo->prepare("DELETE FROM user WHERE user_id = ? AND role IN ('Doctor', 'Patient')");
                    $delStmt->execute([$targetUserId]);

                    $message = "Successfully deleted {$targetUser['role']} '{$targetUser['full_name']}' (@{$targetUser['username']}) and all associated records.";
                    $messageType = "success";
                }
            } catch (PDOException $e) {
                $message = "Database error during deletion: " . $e->getMessage();
                $messageType = "error";
            }
        }
    }
}

// 3. Fetch all active Doctor and Patient clients
$clientsStmt = $pdo->query("
    SELECT u.user_id, u.username, u.full_name, u.email, u.role,
           d.doctor_id, d.designation, d.phone AS doctor_phone, s.name AS specialization_name,
           p.patient_id, p.gender, p.blood_group, p.emergency_contact
    FROM user u
    LEFT JOIN doctor d ON u.user_id = d.user_id
    LEFT JOIN specialization s ON d.specialization_id = s.specialization_id
    LEFT JOIN patient p ON u.user_id = p.user_id
    WHERE u.role IN ('Doctor', 'Patient')
    ORDER BY u.role ASC, u.user_id ASC
");
$clients = $clientsStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
?>

<h2>Delete Client</h2>
<hr style="margin: 15px 0;">
<p style="color: #6b7280; margin-bottom: 20px;">Search and remove a Doctor or Patient account from the hospital system. Deleting a client permanently removes their account and all associated admissions and clinical consultation records.</p>

<?php if (!empty($message)): ?>
    <div style="padding: 14px 18px; border-radius: 6px; margin-bottom: 20px; font-weight: 500; <?= $messageType === 'success' ? 'background-color: #dcfce7; color: #15803d; border: 1px solid #86efac;' : 'background-color: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5;' ?>">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<!-- Search & Filter Controls -->
<div style="display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap;">
    <div style="flex: 1; min-width: 260px;">
        <label for="delete-client-search" style="display: block; margin-bottom: 5px; font-weight: bold; font-size: 14px;">Search Client to Delete</label>
        <input type="text" id="delete-client-search" placeholder="Search by name, username, email, phone, or ID..." style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;">
    </div>
    <div style="width: 180px;">
        <label for="delete-role-filter" style="display: block; margin-bottom: 5px; font-weight: bold; font-size: 14px;">Filter by Role</label>
        <select id="delete-role-filter" style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; background: #ffffff;">
            <option value="">All Clients</option>
            <option value="Doctor">Doctors</option>
            <option value="Patient">Patients</option>
        </select>
    </div>
</div>

<!-- Clients Table -->
<table border="1" cellpadding="10" cellspacing="0" style="width: 100%; border-collapse: collapse; text-align: left;" id="delete-clients-table">
    <thead>
        <tr style="background-color: #f3f4f6;">
            <th>User ID</th>
            <th>Role</th>
            <th>Name & Username</th>
            <th>Email & Contact</th>
            <th>Role Details</th>
            <th style="text-align: center;">Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($clients)): ?>
            <tr><td colspan="6" style="text-align:center; padding: 20px; color: #6b7280;">No registered clients found in the system.</td></tr>
        <?php else: ?>
            <?php foreach ($clients as $client): ?>
                <tr class="delete-client-row" data-role="<?= htmlspecialchars($client['role']) ?>" data-user-id="<?= htmlspecialchars($client['user_id']) ?>">
                    <td><strong>#<?= htmlspecialchars($client['user_id']) ?></strong></td>
                    <td>
                        <span style="display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; <?= $client['role'] === 'Doctor' ? 'background: #e0e7ff; color: #3730a3;' : 'background: #e0f2fe; color: #0369a1;' ?>">
                            <?= htmlspecialchars($client['role']) ?>
                        </span>
                    </td>
                    <td>
                        <div style="font-weight: bold;"><?= htmlspecialchars($client['full_name']) ?></div>
                        <div style="font-size: 12px; color: #6b7280;">@<?= htmlspecialchars($client['username']) ?></div>
                    </td>
                    <td>
                        <div><?= htmlspecialchars($client['email']) ?></div>
                        <div style="font-size: 12px; color: #6b7280;">
                            <?= $client['role'] === 'Doctor' ? 'Phone: ' . htmlspecialchars($client['doctor_phone'] ?? 'N/A') : 'Emergency: ' . htmlspecialchars($client['emergency_contact'] ?? 'N/A') ?>
                        </div>
                    </td>
                    <td>
                        <?php if ($client['role'] === 'Doctor'): ?>
                            <div><strong>Specialty:</strong> <?= htmlspecialchars($client['specialization_name'] ?? 'General') ?></div>
                            <div style="font-size: 12px; color: #6b7280;"><?= htmlspecialchars($client['designation'] ?? 'Doctor') ?> (ID: #<?= htmlspecialchars($client['doctor_id']) ?>)</div>
                        <?php else: ?>
                            <div><strong>Blood Group:</strong> <?= htmlspecialchars($client['blood_group'] ?? 'N/A') ?></div>
                            <div style="font-size: 12px; color: #6b7280;">Gender: <?= htmlspecialchars($client['gender'] ?? 'N/A') ?> (ID: #<?= htmlspecialchars($client['patient_id']) ?>)</div>
                        <?php endif; ?>
                    </td>
                    <td style="text-align: center;">
                        <button type="button" class="btn-trigger-delete" 
                                data-user-id="<?= htmlspecialchars($client['user_id']) ?>"
                                data-name="<?= htmlspecialchars($client['full_name']) ?>"
                                data-role="<?= htmlspecialchars($client['role']) ?>"
                                style="padding: 6px 14px; background-color: #ef4444; color: #ffffff; border: none; border-radius: 4px; font-weight: bold; font-size: 13px; cursor: pointer; transition: background-color 0.2s;">
                            Delete
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<script>
(function() {
    const searchInput = document.getElementById('delete-client-search');
    const roleFilter = document.getElementById('delete-role-filter');
    const rows = document.querySelectorAll('.delete-client-row');

    // Live search & filter
    function filterClients() {
        const query = (searchInput ? searchInput.value : '').toLowerCase().trim();
        const selectedRole = roleFilter ? roleFilter.value : '';

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const role = row.getAttribute('data-role');

            const matchesSearch = text.includes(query);
            const matchesRole = !selectedRole || role === selectedRole;

            if (matchesSearch && matchesRole) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    if (searchInput) searchInput.addEventListener('input', filterClients);
    if (roleFilter) roleFilter.addEventListener('change', filterClients);

    // Handle Delete button clicks
    const deleteButtons = document.querySelectorAll('.btn-trigger-delete');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const userId = this.getAttribute('data-user-id');
            const name = this.getAttribute('data-name');
            const role = this.getAttribute('data-role');

            const confirmed = confirm(
                `Are you sure you want to permanently delete ${role} "${name}" (User #${userId})?\n\n` +
                `WARNING: This will permanently delete their account and all associated admissions, bills, and consultation history.`
            );

            if (!confirmed) return;

            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('user_id', userId);

            this.disabled = true;
            this.textContent = 'Deleting...';

            fetch('deleteclient.php', {
                method: 'POST',
                body: formData
            })
            .then(res => {
                if (!res.ok) throw new Error('Deletion failed');
                return res.text();
            })
            .then(html => {
                const contentArea = document.getElementById('main-content');
                if (contentArea) {
                    contentArea.innerHTML = html;
                    const scripts = contentArea.querySelectorAll('script');
                    scripts.forEach(oldScript => {
                        const newScript = document.createElement('script');
                        newScript.textContent = oldScript.textContent;
                        oldScript.parentNode.replaceChild(newScript, oldScript);
                    });
                }
            })
            .catch(err => {
                alert('Error: ' + err.message);
                btn.disabled = false;
                btn.textContent = 'Delete';
            });
        });
    });
})();
</script>
