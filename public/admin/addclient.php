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

$message = '';
$messageType = '';

// Process Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $roleInput = strtolower(trim($_POST['role'] ?? ''));

    // Whitelist roles allowed to be registered via addClient
    $validRoles = [
        'doctor'  => 'Doctor',
        'patient' => 'Patient'
    ];

    if (empty($username) || empty($email) || empty($password)) {
        $message = "All fields (Username, Email, Password, Role) are required.";
        $messageType = "error";
    } elseif (!isset($validRoles[$roleInput])) {
        $message = "Please select a valid role (Doctor or Patient).";
        $messageType = "error";
    } else {
        $role = $validRoles[$roleInput];
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        try {
            $pdo->beginTransaction();

            // Insert into user table (using username for full_name like in login.php)
            $stmt = $pdo->prepare("INSERT INTO user (username, full_name, email, password_hash, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$username, $username, $email, $hashedPassword, $role]);
            $newUserId = $pdo->lastInsertId();

            // Insert into corresponding role table
            if ($role === 'Doctor') {
                $stmt2 = $pdo->prepare("INSERT INTO doctor (user_id) VALUES (?)");
                $stmt2->execute([$newUserId]);
            } elseif ($role === 'Patient') {
                $stmt2 = $pdo->prepare("INSERT INTO patient (user_id) VALUES (?)");
                $stmt2->execute([$newUserId]);
            }

            $pdo->commit();
            $message = "{$role} account for '{$username}' successfully created!";
            $messageType = "success";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $message = "Account creation failed. Username or email may already be in use.";
            $messageType = "error";
        }
    }
}
?>

<h2>Add New Client</h2>
<hr style="margin: 15px 0;">
<p style="color: #6b7280; margin-bottom: 25px;">Register a new Doctor or Patient account in the hospital system. Newly created users can later complete their profile details in their respective edit settings.</p>

<?php if (!empty($message)): ?>
    <div id="addclient-alert" style="padding: 14px 18px; border-radius: 6px; margin-bottom: 25px; font-weight: 500; <?= $messageType === 'success' ? 'background-color: #dcfce7; color: #15803d; border: 1px solid #86efac;' : 'background-color: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5;' ?>">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<div style="max-width: 520px; background: #ffffff; padding: 25px; border: 1px solid #e5e7eb; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
    <form id="add-client-form" method="POST" action="addclient.php">
        <!-- Role Selection -->
        <div style="margin-bottom: 20px;">
            <label for="client-role" style="display: block; margin-bottom: 6px; font-weight: bold; color: #374151; font-size: 14px;">Select Role <span style="color: #dc2626;">*</span></label>
            <select id="client-role" name="role" required style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; background: #ffffff;">
                <option value="" disabled selected>-- Select Role --</option>
                <option value="doctor">Doctor</option>
                <option value="patient">Patient</option>
            </select>
        </div>

        <!-- Username -->
        <div style="margin-bottom: 20px;">
            <label for="client-username" style="display: block; margin-bottom: 6px; font-weight: bold; color: #374151; font-size: 14px;">Username <span style="color: #dc2626;">*</span></label>
            <input type="text" id="client-username" name="username" placeholder="e.g. drjohnsmith or janesmith" required style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;">
        </div>

        <!-- Email -->
        <div style="margin-bottom: 20px;">
            <label for="client-email" style="display: block; margin-bottom: 6px; font-weight: bold; color: #374151; font-size: 14px;">Email Address <span style="color: #dc2626;">*</span></label>
            <input type="email" id="client-email" name="email" placeholder="e.g. user@hospital.com" required style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;">
        </div>

        <!-- Password -->
        <div style="margin-bottom: 25px;">
            <label for="client-password" style="display: block; margin-bottom: 6px; font-weight: bold; color: #374151; font-size: 14px;">Password <span style="color: #dc2626;">*</span></label>
            <input type="password" id="client-password" name="password" placeholder="Enter secure password" required style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;">
        </div>

        <!-- Submit Button -->
        <button type="submit" id="add-client-submit-btn" style="width: 100%; padding: 12px; background-color: #4f46e5; color: #ffffff; border: none; border-radius: 6px; font-size: 15px; font-weight: bold; cursor: pointer; transition: background-color 0.2s;">
            Create Client Account
        </button>
    </form>
</div>

<script>
(function() {
    const form = document.getElementById('add-client-form');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const submitBtn = document.getElementById('add-client-submit-btn');
        const originalBtnText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Creating Account...';

        const formData = new FormData(form);

        fetch('addclient.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) throw new Error('Submission failed');
            return response.text();
        })
        .then(html => {
            const contentArea = document.getElementById('main-content');
            if (contentArea) {
                contentArea.innerHTML = html;
                // Re-execute scripts
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
            submitBtn.disabled = false;
            submitBtn.textContent = originalBtnText;
        });
    });
})();
</script>
