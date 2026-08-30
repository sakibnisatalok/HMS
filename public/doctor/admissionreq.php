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

// 3. Handle POST: Doctor Approve (Approved / Admitted) or Cancel (Cancelled / Discharged)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $admissionId = filter_input(INPUT_POST, 'admission_id', FILTER_VALIDATE_INT);
    $decision = trim($_POST['decision'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    if (!$admissionId) {
        echo json_encode(['success' => false, 'message' => 'Invalid Admission ID.']);
        exit;
    }

    if (!in_array($decision, ['approve', 'cancel'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid decision.']);
        exit;
    }

    $consultStatus = ($decision === 'approve') ? 'Approved' : 'Cancelled';
    $admissionStatus = ($decision === 'approve') ? 'Admitted' : 'Discharged';
    $defaultNotes = ($decision === 'approve') ? 'Admission approved by doctor.' : 'Admission cancelled by doctor.';
    $reportText = !empty($notes) ? $notes : $defaultNotes;

    try {
        $pdo->beginTransaction();

        // 1. Update status in admission table
        $admUpdate = $pdo->prepare("
            UPDATE admission
            SET status = ?
            WHERE admission_id = ? AND doctor_id = ?
        ");
        $admUpdate->execute([$admissionStatus, $admissionId, $doctorId]);

        // 2. Insert or Update status in consultation table as 'Approved' or 'Cancelled'
        $cCheck = $pdo->prepare("SELECT consultation_id FROM consultation WHERE admission_id = ?");
        $cCheck->execute([$admissionId]);
        $existingConsult = $cCheck->fetch(PDO::FETCH_ASSOC);

        $now = date('Y-m-d H:i:s');
        if ($existingConsult) {
            $cUpdate = $pdo->prepare("
                UPDATE consultation
                SET consult_datetime = ?, report = ?, status = ?
                WHERE consultation_id = ?
            ");
            $cUpdate->execute([$now, $reportText, $consultStatus, $existingConsult['consultation_id']]);
        } else {
            $cInsert = $pdo->prepare("
                INSERT INTO consultation (admission_id, doctor_id, consult_datetime, report, status)
                VALUES (?, ?, ?, ?, ?)
            ");
            $cInsert->execute([$admissionId, $doctorId, $now, $reportText, $consultStatus]);
        }

        $pdo->commit();

        $msg = ($decision === 'approve') ? 'Admission approved successfully!' : 'Admission request cancelled.';
        echo json_encode(['success' => true, 'message' => $msg]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// 4. Fetch In-Patient Admissions assigned to this doctor
$admStmt = $pdo->prepare("
    SELECT a.admission_id, u.full_name AS patient_name, u.email AS patient_email,
           p.gender, p.blood_group, p.emergency_contact, p.address,
           a.admission_date, a.problem, a.status AS admission_status,
           c.status AS consult_status, c.report AS consult_report
    FROM admission a
    JOIN patient p ON a.patient_id = p.patient_id
    JOIN user u ON p.user_id = u.user_id
    LEFT JOIN consultation c ON a.admission_id = c.admission_id
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
    Review in-patient admission requests. You can approve or cancel requests directly from the action section.
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
                        <?php if (!empty($row['consult_status'])): ?>
                            <div style="font-size: 11px; margin-top: 4px; font-weight: bold; color: <?= $row['consult_status'] === 'Approved' ? '#15803d' : '#b91c1c' ?>;">
                                Consultation: <?= htmlspecialchars($row['consult_status']) ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="display: flex; gap: 6px;">
                            <button type="button" 
                                    class="btn-open-admission-modal" 
                                    data-admission-id="<?= htmlspecialchars($row['admission_id']) ?>" 
                                    data-patient-name="<?= htmlspecialchars($row['patient_name']) ?>"
                                    data-decision="approve"
                                    data-notes="<?= htmlspecialchars($row['consult_report'] ?? '') ?>"
                                    style="background: #10b981; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: bold;">
                                Approve
                            </button>
                            <button type="button" 
                                    class="btn-open-admission-modal" 
                                    data-admission-id="<?= htmlspecialchars($row['admission_id']) ?>" 
                                    data-patient-name="<?= htmlspecialchars($row['patient_name']) ?>"
                                    data-decision="cancel"
                                    data-notes="<?= htmlspecialchars($row['consult_report'] ?? '') ?>"
                                    style="background: #ef4444; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: bold;">
                                Cancel
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<!-- Admission Action Drawer / Popup Window -->
<div id="admission-form-container" style="display: none; border-radius: 8px; padding: 20px; margin-top: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
    <h3 id="admission-form-title" style="margin-bottom: 12px;"></h3>
    <form id="record-admission-form" style="display: flex; flex-direction: column; gap: 14px;">
        <input type="hidden" name="admission_id" id="admission-modal-id">
        <input type="hidden" name="decision" id="admission-modal-decision">

        <div>
            <label id="admission-notes-label" style="display: block; font-weight: bold; margin-bottom: 4px; font-size: 14px;">Doctor Notes / Instructions</label>
            <textarea name="notes" id="admission-modal-notes" rows="4" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;"></textarea>
        </div>

        <div style="display: flex; gap: 10px;">
            <button type="submit" id="btn-save-admission-decision" style="color: white; border: none; padding: 8px 18px; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 14px;">Confirm</button>
            <button type="button" id="btn-close-admission-modal" style="background: #e5e7eb; color: #374151; border: none; padding: 8px 18px; border-radius: 4px; cursor: pointer; font-size: 14px;">Close</button>
        </div>
    </form>
</div>

<script>
(function() {
    const container = document.getElementById('admission-form-container');
    const title = document.getElementById('admission-form-title');
    const notesLabel = document.getElementById('admission-notes-label');
    const notesTextarea = document.getElementById('admission-modal-notes');
    const saveBtn = document.getElementById('btn-save-admission-decision');
    const idInput = document.getElementById('admission-modal-id');
    const decisionInput = document.getElementById('admission-modal-decision');
    const closeBtn = document.getElementById('btn-close-admission-modal');
    const buttons = document.querySelectorAll('.btn-open-admission-modal');
    const messageBox = document.getElementById('admissionreq-message');
    const form = document.getElementById('record-admission-form');

    buttons.forEach(btn => {
        btn.addEventListener('click', function() {
            const admId = this.getAttribute('data-admission-id');
            const pName = this.getAttribute('data-patient-name');
            const decision = this.getAttribute('data-decision');
            const notes = this.getAttribute('data-notes');

            if (container && idInput && decisionInput) {
                idInput.value = admId;
                decisionInput.value = decision;
                notesTextarea.value = notes || '';

                if (decision === 'approve') {
                    title.textContent = 'Approve Admission for ' + pName + ' (#' + admId + ')';
                    title.style.color = '#065f46';
                    container.style.border = '2px solid #10b981';
                    container.style.background = '#ecfdf5';
                    notesLabel.textContent = 'Admission / Treatment Notes (Saved to Consultation as Approved)';
                    notesTextarea.placeholder = 'Enter admission instructions, bed ward notes, or diagnosis...';
                    saveBtn.textContent = 'Approve Admission';
                    saveBtn.style.background = '#10b981';
                } else {
                    title.textContent = 'Cancel Admission for ' + pName + ' (#' + admId + ')';
                    title.style.color = '#991b1b';
                    container.style.border = '2px solid #ef4444';
                    container.style.background = '#fef2f2';
                    notesLabel.textContent = 'Cancellation Reason (Saved to Consultation as Cancelled)';
                    notesTextarea.placeholder = 'Enter reason for cancellation (e.g. bed unavailable, patient cancelled)...';
                    saveBtn.textContent = 'Cancel Admission';
                    saveBtn.style.background = '#ef4444';
                }

                container.style.display = 'block';
                container.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });

    if (closeBtn && container) {
        closeBtn.addEventListener('click', function() {
            container.style.display = 'none';
        });
    }

    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(form);

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
                    messageBox.textContent = 'Failed to process admission decision.';
                }
            });
        });
    }
})();
</script>
