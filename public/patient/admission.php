<?php
session_start();
require_once '../../app/config/databaseconnection.php';

/** @var \PDO $pdo */

// 1. Access Control Guard
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'patient') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// 2. POST handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    // Retrieve patient_id matching session user_id
    $stmt = $pdo->prepare("SELECT patient_id FROM patient WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$patient) {
        echo json_encode(['success' => false, 'message' => 'Patient profile not found.']);
        exit;
    }
    $patientId = $patient['patient_id'];

    // Inputs
    $doctorId = filter_input(INPUT_POST, 'doctor_id', FILTER_VALIDATE_INT);
    $admissionDate = trim($_POST['admission_date'] ?? '');
    $admissionType = trim($_POST['admission_type'] ?? '');
    $problem = trim($_POST['problem'] ?? '');

    // Validation
    if ($doctorId === false || $doctorId === null) {
        echo json_encode(['success' => false, 'message' => 'Please provide a valid Doctor ID.']);
        exit;
    }

    // Verify doctor exists and is active
    $stmt = $pdo->prepare("SELECT doctor_id FROM doctor WHERE doctor_id = ? AND status = 'Active'");
    $stmt->execute([$doctorId]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Selected doctor does not exist or is inactive.']);
        exit;
    }

    if (empty($admissionDate)) {
        echo json_encode(['success' => false, 'message' => 'Please select a consultation/admission date.']);
        exit;
    }

    if (!in_array($admissionType, ['Admit', 'Planned'])) {
        echo json_encode(['success' => false, 'message' => 'Please select a valid admission type.']);
        exit;
    }

    // Status mapping: Planned -> Consult, Admit -> Admitted
    $status = ($admissionType === 'Planned') ? 'Consult' : 'Admitted';
    $problemValue = ($problem === '') ? null : $problem;

    try {
        $stmt = $pdo->prepare("
            INSERT INTO admission (patient_id, doctor_id, admission_date, admission_type, problem, status)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$patientId, $doctorId, $admissionDate, $admissionType, $problemValue, $status]);
        echo json_encode(['success' => true, 'message' => 'Admission request submitted successfully!']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to save admission request: ' . $e->getMessage()]);
    }
    exit;
}
?>

<h2>Request Admission</h2>
<hr style="margin: 15px 0;">
<div id="admission-message"></div>
<form id="admission-form" style="max-width: 400px; display: flex; flex-direction: column; gap: 15px;">
    <div>
        <label style="display:block; margin-bottom:5px;">Doctor ID</label>
        <input type="number" name="doctor_id" required style="width: 100%; padding: 8px;" placeholder="Enter Doctor ID">
    </div>
    <div>
        <label style="display:block; margin-bottom:5px;">Consultation Date</label>
        <input type="datetime-local" name="admission_date" required style="width: 100%; padding: 8px;">
    </div>
    <div>
        <label style="display:block; margin-bottom:5px;">Admission Type</label>
        <select name="admission_type" required style="width: 100%; padding: 8px;">
            <option value="Planned">Planned</option>
            <option value="Admit">Admit</option>
        </select>
    </div>
    <div>
        <label style="display:block; margin-bottom:5px;">Problem (Optional)</label>
        <textarea name="problem" style="width: 100%; padding: 8px;" placeholder="Describe symptoms or reasons..."></textarea>
    </div>
    <button type="submit" style="padding: 10px; background: #2563eb; color: white; border: none; cursor: pointer; border-radius: 4px;">Submit Request</button>
</form>