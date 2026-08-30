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

// 3. Handle POST: Record/Update Consultation Report with status (Completed or Cancelled)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $admissionId = filter_input(INPUT_POST, 'admission_id', FILTER_VALIDATE_INT);
    $consultDatetime = trim($_POST['consult_datetime'] ?? '');
    $report = trim($_POST['report'] ?? '');
    $status = trim($_POST['status'] ?? 'Completed');

    if (!$admissionId) {
        echo json_encode(['success' => false, 'message' => 'Invalid Admission ID.']);
        exit;
    }

    if (empty($consultDatetime)) {
        $consultDatetime = date('Y-m-d H:i:s');
    }

    if (empty($report)) {
        echo json_encode(['success' => false, 'message' => 'Please provide clinical diagnosis or cancellation notes.']);
        exit;
    }

    // Only allow Completed or Cancelled as requested
    if (!in_array($status, ['Completed', 'Cancelled'])) {
        $status = 'Completed';
    }

    try {
        // Verify this admission belongs to this doctor and is a Consultation request
        $admCheck = $pdo->prepare("SELECT admission_id FROM admission WHERE admission_id = ? AND doctor_id = ?");
        $admCheck->execute([$admissionId, $doctorId]);
        if (!$admCheck->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Admission record not found for this doctor.']);
            exit;
        }

        // Check if consultation already exists for this admission
        $cCheck = $pdo->prepare("SELECT consultation_id FROM consultation WHERE admission_id = ?");
        $cCheck->execute([$admissionId]);
        $existingConsult = $cCheck->fetch(PDO::FETCH_ASSOC);

        if ($existingConsult) {
            $updateStmt = $pdo->prepare("
                UPDATE consultation
                SET consult_datetime = ?, report = ?, status = ?
                WHERE consultation_id = ?
            ");
            $updateStmt->execute([$consultDatetime, $report, $status, $existingConsult['consultation_id']]);
        } else {
            $insertStmt = $pdo->prepare("
                INSERT INTO consultation (admission_id, doctor_id, consult_datetime, report, status)
                VALUES (?, ?, ?, ?, ?)
            ");
            $insertStmt->execute([$admissionId, $doctorId, $consultDatetime, $report, $status]);
        }

        echo json_encode(['success' => true, 'message' => 'Consultation marked as ' . $status . ' and saved!']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// 4. Fetch Pending / Active Consultation Requests assigned to this doctor
$reqStmt = $pdo->prepare("
    SELECT a.admission_id, u.full_name AS patient_name, u.email AS patient_email, 
           a.admission_date, a.problem, a.status AS admission_status,
           c.consultation_id, c.report, c.status AS consult_status
    FROM admission a
    JOIN patient p ON a.patient_id = p.patient_id
    JOIN user u ON p.user_id = u.user_id
    LEFT JOIN consultation c ON a.admission_id = c.admission_id
    WHERE a.doctor_id = ? AND a.status = 'Consult'
    ORDER BY a.admission_date DESC
");
$reqStmt->execute([$doctorId]);
$requests = $reqStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
?>

<h2>Consultation Requests</h2>
<hr style="margin: 15px 0;">

<div id="consultation-message" style="margin-bottom: 15px; display: none; padding: 10px; border-radius: 4px;"></div>

<!-- Assigned Consultation Requests Table -->
<table border="1" cellpadding="10" cellspacing="0" style="width: 100%; border-collapse: collapse; text-align: left; margin-bottom: 30px;">
    <thead>
        <tr style="background-color: #f0fdfa; color: #115e59;">
            <th>ID</th>
            <th>Patient Name</th>
            <th>Appointment Date</th>
            <th>Reported Problem</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($requests)): ?>
            <tr><td colspan="6" style="text-align: center; color: #6b7280;">No pending consultation requests found.</td></tr>
        <?php else: ?>
            <?php foreach ($requests as $row): ?>
                <tr>
                    <td><strong>#<?= htmlspecialchars($row['admission_id']) ?></strong></td>
                    <td>
                        <div style="font-weight: bold;"><?= htmlspecialchars($row['patient_name']) ?></div>
                        <div style="font-size: 12px; color: #6b7280;"><?= htmlspecialchars($row['patient_email'] ?? '') ?></div>
                    </td>
                    <td><?= htmlspecialchars($row['admission_date'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($row['problem'] ?? 'None provided') ?></td>
                    <td>
                        <span style="background: #fef3c7; color: #92400e; padding: 3px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;">
                            <?= htmlspecialchars($row['admission_status']) ?>
                        </span>
                        <?php if (!empty($row['consult_status'])): ?>
                            <div style="font-size: 11px; margin-top: 4px; font-weight: bold; color: <?= $row['consult_status'] === 'Completed' ? '#15803d' : '#b91c1c' ?>;">
                                Status: <?= htmlspecialchars($row['consult_status']) ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <button type="button" 
                                class="btn-open-consult-modal" 
                                data-admission-id="<?= htmlspecialchars($row['admission_id']) ?>"
                                data-patient-name="<?= htmlspecialchars($row['patient_name']) ?>"
                                data-report="<?= htmlspecialchars($row['report'] ?? '') ?>"
                                data-status="<?= htmlspecialchars($row['consult_status'] ?? 'Completed') ?>"
                                style="background: #0d9488; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 13px;">
                            <?= !empty($row['report']) ? 'Update Report' : 'Consult / Update' ?>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<!-- Record / Update Consultation Form Drawer -->
<div id="consultation-form-container" style="display: none; background: #f0fdfa; border: 1px solid #0d9488; border-radius: 8px; padding: 20px; margin-bottom: 30px;">
    <h3 style="color: #115e59; margin-bottom: 12px;">Consultation Report: <span id="form-patient-name" style="color: #0f766e;"></span></h3>
    <form id="record-consultation-form" style="display: flex; flex-direction: column; gap: 12px;">
        <input type="hidden" name="admission_id" id="form-admission-id">

        <div>
            <label style="display: block; font-weight: bold; margin-bottom: 4px; font-size: 14px;">Consultation Date & Time</label>
            <input type="datetime-local" name="consult_datetime" id="form-consult-date" required style="width: 100%; max-width: 350px; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
        </div>

        <div>
            <label style="display: block; font-weight: bold; margin-bottom: 4px; font-size: 14px;">Consultation Decision / Status</label>
            <select name="status" id="form-consult-status" required style="width: 100%; max-width: 350px; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                <option value="Completed">Completed</option>
                <option value="Cancelled">Cancelled</option>
            </select>
        </div>

        <div>
            <label style="display: block; font-weight: bold; margin-bottom: 4px; font-size: 14px;">Medical Report / Diagnosis / Notes</label>
            <textarea name="report" id="form-consult-report" required rows="4" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;" placeholder="Enter diagnosis, clinical recommendations, or cancellation reason..."></textarea>
        </div>

        <div style="display: flex; gap: 10px;">
            <button type="submit" style="background: #0d9488; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-weight: bold;">Save Decision</button>
            <button type="button" id="btn-cancel-consult" style="background: #e5e7eb; color: #374151; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer;">Cancel</button>
        </div>
    </form>
</div>

<script>
(function() {
    const formContainer = document.getElementById('consultation-form-container');
    const patientNameSpan = document.getElementById('form-patient-name');
    const admissionIdInput = document.getElementById('form-admission-id');
    const consultDateInput = document.getElementById('form-consult-date');
    const consultStatusSelect = document.getElementById('form-consult-status');
    const consultReportTextarea = document.getElementById('form-consult-report');
    const cancelBtn = document.getElementById('btn-cancel-consult');
    const buttons = document.querySelectorAll('.btn-open-consult-modal');

    buttons.forEach(btn => {
        btn.addEventListener('click', function() {
            const admId = this.getAttribute('data-admission-id');
            const pName = this.getAttribute('data-patient-name');
            const report = this.getAttribute('data-report');
            const status = this.getAttribute('data-status');

            if (formContainer && admissionIdInput && patientNameSpan) {
                admissionIdInput.value = admId;
                patientNameSpan.textContent = pName + ' (Admission #' + admId + ')';
                consultReportTextarea.value = report || '';
                consultStatusSelect.value = (status === 'Cancelled') ? 'Cancelled' : 'Completed';

                const now = new Date();
                const localIso = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
                consultDateInput.value = localIso;

                formContainer.style.display = 'block';
                formContainer.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });

    if (cancelBtn && formContainer) {
        cancelBtn.addEventListener('click', function() {
            formContainer.style.display = 'none';
        });
    }
})();
</script>
