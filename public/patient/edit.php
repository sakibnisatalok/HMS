<?php
session_start();
require_once '../../app/config/databaseconnection.php';

/** @var \PDO $pdo */

// Access Control Guard
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'patient') {
    http_response_code(403);
    echo "Unauthorized";
    exit;
}

$userId = $_SESSION['user_id'];

// Handle Update Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $gender = $_POST['gender'] !== '' ? $_POST['gender'] : null;
    $dateOfBirth = $_POST['date_of_birth'] !== '' ? $_POST['date_of_birth'] : null;
    $bloodGroup = trim($_POST['blood_group'] ?? '') !== '' ? trim($_POST['blood_group']) : null;
    $address = trim($_POST['address'] ?? '') !== '' ? trim($_POST['address']) : null;
    $emergencyContact = trim($_POST['emergency_contact'] ?? '') !== '' ? trim($_POST['emergency_contact']) : null;

    try {
        $stmt = $pdo->prepare("
            UPDATE patient
            SET gender = ?, date_of_birth = ?, blood_group = ?, address = ?, emergency_contact = ?
            WHERE user_id = ?
        ");
        $stmt->execute([$gender, $dateOfBirth, $bloodGroup, $address, $emergencyContact, $userId]);

        echo json_encode(['success' => true, 'message' => 'Profile updated successfully!']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Update failed. Please try again.']);
    }
    exit;
}

// Fetch current values to pre-fill the form
$stmt = $pdo->prepare("SELECT gender, date_of_birth, blood_group, address, emergency_contact FROM patient WHERE user_id = ?");
$stmt->execute([$userId]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
?>

<h2>Edit Profile</h2>
<hr style="margin: 15px 0;">
<div id="edit-profile-message"></div>
<form id="edit-profile-form" style="max-width: 400px; display: flex; flex-direction: column; gap: 15px;">
    <div>
        <label style="display:block; margin-bottom:5px;">Gender</label>
        <select name="gender" style="width: 100%; padding: 8px;">
            <option value="" <?= empty($patient['gender']) ? 'selected' : '' ?>>Select gender</option>
            <option value="Male" <?= ($patient['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
            <option value="Female" <?= ($patient['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
            <option value="Other" <?= ($patient['gender'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
        </select>
    </div>
    <div>
        <label style="display:block; margin-bottom:5px;">Date of Birth</label>
        <input type="date" name="date_of_birth" value="<?= htmlspecialchars($patient['date_of_birth'] ?? '') ?>" style="width: 100%; padding: 8px;">
    </div>
    <div>
        <label style="display:block; margin-bottom:5px;">Blood Group</label>
        <input type="text" name="blood_group" value="<?= htmlspecialchars($patient['blood_group'] ?? '') ?>" placeholder="e.g. O+" style="width: 100%; padding: 8px;">
    </div>
    <div>
        <label style="display:block; margin-bottom:5px;">Address</label>
        <textarea name="address" style="width: 100%; padding: 8px;"><?= htmlspecialchars($patient['address'] ?? '') ?></textarea>
    </div>
    <div>
        <label style="display:block; margin-bottom:5px;">Emergency Contact</label>
        <input type="text" name="emergency_contact" value="<?= htmlspecialchars($patient['emergency_contact'] ?? '') ?>" style="width: 100%; padding: 8px;">
    </div>
    <button type="submit" style="padding: 10px; background: #2563eb; color: white; border: none; cursor: pointer; border-radius: 4px;">Update Info</button>
</form>
