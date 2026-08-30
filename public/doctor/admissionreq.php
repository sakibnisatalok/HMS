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

// 3. Handle POST: Doctor Approve or Cancel Admission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $admissionId = filter_input(INPUT_POST, 'admission_id', FILTER_VALIDATE_INT);
    $decision = trim($_POST['decision'] ?? '');

    if (!$admissionId) {
        echo json_encode(['success' => false, 'message' => 'Invalid Admission ID.']);
        exit;
    }

    if (!in_array($decision, ['approve', 'cancel'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
        exit;
    }

    // Map decision to database status enum ('Admitted' vs 'Discharged')
    $newStatus = ($decision === 'approve') ? 'Admitted' : 'Discharged';

    try {
        $stmt = $pdo->prepare("
            UPDATE admission
            SET status = ?
            WHERE admission_id = ? AND doctor_id = ?
        ");
        $stmt->execute([$newStatus, $admissionId, $doctorId]);

        $msg = ($decision === 'approve') ? 'Admission approved successfully!' : 'Admission request cancelled.';
        echo json_encode(['success' => true, 'message' => $msg]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// 4. Fetch In-Patient Admissions assigned to this doctor
$admStmt = $pdo->prepare("
    SELECT a.admission_id, u.full_name AS patient_name, u.email AS patient_email,
           p.gender, p.blood_group, p.emergency_contact, p.address,
           a.admission_date, a.problem, a.status AS admission_status
    FROM admission a
    JOIN patient p ON a.patient_id = p.patient_id
    JOIN user u ON p.user_id = u.user_id
    WHERE a.doctor_id = ? AND a.admission_type = 'Admit'
    ORDER BY a.admission_date DESC
");
$admStmt->execute([$doctorId]);
$admissions = $admStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
?>

<h2>Admission Requests</h2>
<hr style="margin: 15px 0;">

<div id="admissionreq-message" style="margin-bottom: 15px; display: none; padding: 10px; border-radius: 4px;"></div>

<p style="color: #4b5563; margin-bottom: 20px;">
    Review in-patient hospital admission requests. You can approve admissions or cancel/discharge them.
</p>

<!-- Table of In-Patient Admissions -->
<table border="1" cellpadding="10" cellspacing="0" style="width: 100%; border-collapse: collapse; text-align: left;" id="doctor-admissions-table">
    <thead>
        <tr style="background-color: #f0fdfa; color: #115e59;">
            <th>ID</th>
            <th>Patient Name</th>
            <th>Vitals & Contact</th>
            <th>Admission Date</th>
            <th>Reported Problem</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($admissions)): ?>
            <tr><td colspan="7" style="text-align: center; color: #6b7280;">No in-patient admission requests found.</td></tr>
        <?php else: ?>
            <?php foreach ($admissions as $row): ?>
                <tr>
                    <td><strong>#<?= htmlspecialchars($row['admission_id']) ?></strong></td>
                    <td>
                        <div style="font-weight: bold;"><?= htmlspecialchars($row['patient_name']) ?></div>
                        <div style="font-size: 12px; color: #6b7280;"><?= htmlspecialchars($row['patient_email'] ?? '') ?></div>
                    </td>
                    <td>
                        <div>Gender: <?= htmlspecialchars($row['gender'] ?? 'N/A') ?></div>
                        <div style="font-size: 12px; color: #b91c1c; font-weight: bold;">Blood: <?= htmlspecialchars($row['blood_group'] ?? 'N/A') ?></div>
                        <div style="font-size: 12px; color: #6b7280;">Contact: <?= htmlspecialchars($row['emergency_contact'] ?? 'N/A') ?></div>
                    </td>
                    <td><?= htmlspecialchars($row['admission_date'] ?? 'N/A') ?></td>
                    <td style="max-width: 200px; font-size: 13px;"><?= htmlspecialchars($row['problem'] ?? 'None provided') ?></td>
                    <td>
                        <span style="display: inline-block; padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: bold; background: <?= $row['admission_status'] === 'Admitted' ? '#dcfce7; color: #15803d;' : ($row['admission_status'] === 'Discharged' ? '#fee2e2; color: #b91c1c;' : '#fef3c7; color: #92400e;') ?>">
                            <?= htmlspecialchars($row['admission_status']) ?>
                        </span>
                    </td>
                    <td>
                        <div style="display: flex; gap: 6px;">
                            <?php if ($row['admission_status'] !== 'Admitted'): ?>
                                <button type="button" 
                                        class="btn-admission-action" 
                                        data-admission-id="<?= htmlspecialchars($row['admission_id']) ?>" 
                                        data-decision="approve"
                                        style="background: #10b981; color: white; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: bold;">
                                    Approve
                                </button>
                            <?php endif; ?>
                            <?php if ($row['admission_status'] !== 'Discharged'): ?>
                                <button type="button" 
                                        class="btn-admission-action" 
                                        data-admission-id="<?= htmlspecialchars($row['admission_id']) ?>" 
                                        data-decision="cancel"
                                        style="background: #ef4444; color: white; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: bold;">
                                    Cancel
                                </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<script>
(function() {
    const actionButtons = document.querySelectorAll('.btn-admission-action');
    const messageBox = document.getElementById('admissionreq-message');

    actionButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const admId = this.getAttribute('data-admission-id');
            const decision = this.getAttribute('data-decision');

            const confirmMsg = decision === 'approve' 
                ? 'Are you sure you want to approve this admission?' 
                : 'Are you sure you want to cancel this admission?';

            if (!confirm(confirmMsg)) return;

            const formData = new FormData();
            formData.append('admission_id', admId);
            formData.append('decision', decision);

            fetch('admissionreq.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Reload admission section
                    fetch('admissionreq.php')
                        .then(r => r.text())
                        .then(html => {
                            const contentArea = document.getElementById('main-content');
                            if (contentArea) {
                                contentArea.innerHTML = html;
                                const scripts = contentArea.querySelectorAll('script');
                                scripts.forEach(s => {
                                    const ns = document.createElement('script');
                                    ns.textContent = s.textContent;
                                    s.parentNode.replaceChild(ns, s);
                                });
                            }
                        });
                } else {
                    if (messageBox) {
                        messageBox.style.display = 'block';
                        messageBox.style.background = '#fee2e2';
                        messageBox.style.color = '#b91c1c';
                        messageBox.textContent = data.message;
                    }
                }
            })
            .catch(() => {
                if (messageBox) {
                    messageBox.style.display = 'block';
                    messageBox.style.background = '#fee2e2';
                    messageBox.style.color = '#b91c1c';
                    messageBox.textContent = 'Failed to process request.';
                }
            });
        });
    });
})();
</script>
