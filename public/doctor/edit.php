<?php
session_start();
require_once '../../app/config/databaseconnection.php';

/** @var \PDO $pdo */

// Access Control Guard
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'doctor') {
    http_response_code(403);
    echo "Unauthorized";
    exit;
}

$userId = $_SESSION['user_id'];

// Handle Update Submission (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $phone = trim($_POST['phone'] ?? '') !== '' ? trim($_POST['phone']) : null;
    $designation = trim($_POST['designation'] ?? '') !== '' ? trim($_POST['designation']) : null;
    $specializationId = filter_input(INPUT_POST, 'specialization_id', FILTER_VALIDATE_INT);
    if ($specializationId === false) {
        $specializationId = null;
    }

    try {
        $stmt = $pdo->prepare("
            UPDATE doctor
            SET phone = ?, designation = ?, specialization_id = ?
            WHERE user_id = ?
        ");
        $stmt->execute([$phone, $designation, $specializationId, $userId]);

        echo json_encode(['success' => true, 'message' => 'Profile updated successfully!']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Update failed. Please try again.']);
    }
    exit;
}

// Fetch current values and specializations (GET)
$stmt = $pdo->prepare("SELECT phone, designation, specialization_id FROM doctor WHERE user_id = ?");
$stmt->execute([$userId]);
$doctor = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

$specStmt = $pdo->query("SELECT specialization_id, name FROM specialization ORDER BY name ASC");
$specializations = $specStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
?>

<h2>Edit Profile</h2>
<hr style="margin: 15px 0;">
<div id="edit-doctor-message"></div>
<form id="edit-doctor-form" style="max-width: 400px; display: flex; flex-direction: column; gap: 15px;">
    <div>
        <label style="display:block; margin-bottom:5px;">Phone Number</label>
        <input type="text" name="phone" value="<?= htmlspecialchars($doctor['phone'] ?? '') ?>" placeholder="e.g. 01711000001" style="width: 100%; padding: 8px;">
    </div>
    <div>
        <label style="display:block; margin-bottom:5px;">Designation</label>
        <input type="text" name="designation" value="<?= htmlspecialchars($doctor['designation'] ?? '') ?>" placeholder="e.g. Consultant Cardiologist" style="width: 100%; padding: 8px;">
    </div>
    <div>
        <label style="display:block; margin-bottom:5px;">Specialization</label>
        <select name="specialization_id" style="width: 100%; padding: 8px;">
            <option value="" <?= empty($doctor['specialization_id']) ? 'selected' : '' ?>>Select Specialization</option>
            <?php foreach ($specializations as $spec): ?>
                <option value="<?= htmlspecialchars($spec['specialization_id']) ?>" <?= ($doctor['specialization_id'] ?? 0) == $spec['specialization_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($spec['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" style="padding: 10px; background: #0d9488; color: white; border: none; cursor: pointer; border-radius: 4px;">Update Info</button>
</form>
