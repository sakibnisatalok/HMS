
<?php

/** @var \PDO $pdo */

session_start();
// Include the database connection from the app/config directory
require_once '../app/config/databaseconnection.php'; 

// Handle Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $_SESSION = [];
    session_destroy();
    header("Location: login.php");
    exit;
}

$message = '';
$messageType = '';

// used username for full name in database

// Determine which form to show on reload:
// keep the register form open on a registration error so the user doesn't lose their place,
// otherwise default to the login form (including right after a successful registration).
$showRegisterForm = false;

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1. REGISTRATION LOGIC
    if ($action === 'register') {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $role = $_POST['role'];

        // Enforce the rule: Admin cannot be created from the UI
        if ($role === 'admin') {
            $message = "Error: Admin credentials cannot be created here.";
            $messageType = "error";
            $showRegisterForm = true;
        } else {
            // Hash the password for security
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            try {
                $pdo->beginTransaction();

                // Insert into the user table
                $stmt = $pdo->prepare("INSERT INTO user (username, full_name, email, password_hash, role) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$username, $username, $email, $hashedPassword, $role]);
                $newUserId = $pdo->lastInsertId();

                // Insert into the matching role-specific table
                if ($role === 'doctor') {
                    $stmt2 = $pdo->prepare("INSERT INTO doctor (user_id) VALUES (?)");
                    $stmt2->execute([$newUserId]);
                } elseif ($role === 'patient') {
                    $stmt2 = $pdo->prepare("INSERT INTO patient (user_id) VALUES (?)");
                    $stmt2->execute([$newUserId]);
                }

                $pdo->commit();
                $message = "ID created successfully! You can now log in.";
                $messageType = "success";
            } catch (PDOException $e) {
                // Roll back both inserts if either one failed
                $pdo->rollBack();
                $message = "Registration failed. Email might already exist.";
                $messageType = "error";
                $showRegisterForm = true;
            }
        }
    }

    
    // 2. LOGIN LOGIC
    elseif ($action === 'login') {
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        try {
            // Fetch the user from the database
            $stmt = $pdo->prepare("SELECT user_id, full_name, password_hash, role FROM user WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verify the password matches the hashed password in the DB
            if ($user && password_verify($password, $user['password_hash'])) {
                // Set Session Variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['name'] = $user['full_name'];

                // Redirect based on role to their respective folders
                if ($user['role'] === 'admin') {
                    header("Location: admin/index.php");
                    exit;
                } elseif ($user['role'] === 'doctor') {
                    header("Location: doctor/index.php");
                    exit;
                } elseif ($user['role'] === 'patient') {
                    header("Location: patient/index.php");
                    exit;
                }
            } else {
                $message = "Invalid email or password.";
                $messageType = "error";
            }
        } catch (PDOException $e) {
            $message = "System error during login.";
            $messageType = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Management System - Login</title>
    <style>
        
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7f6;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .auth-container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #666;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #0056b3;
        }
        .toggle-text {
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
        }
        .toggle-text a {
            color: #007bff;
            cursor: pointer;
            text-decoration: none;
        }
        .toggle-text a:hover {
            text-decoration: underline;
        }
        .hidden {
            display: none;
        }
        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            text-align: center;
        }
        .alert.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    </style>
</head>
<body>

<div class="auth-container">
    
    <?php if ($message): ?>
        <div class="alert <?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- LOGIN FORM -->
    <div id="login-section" class="<?= $showRegisterForm ? 'hidden' : '' ?>">
        <h2>Login</h2>
        <form method="POST" action="login.php">
            <input type="hidden" name="action" value="login">
            
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            
            <button type="submit">Login</button>
        </form>
        <div class="toggle-text">
            Don't have an ID? <a onclick="toggleForms()">Create one here</a>
        </div>
    </div>

    <!-- REGISTER FORM -->
    <div id="register-section" class="<?= $showRegisterForm ? '' : 'hidden' ?>">
        <h2>Create ID</h2>
        <form method="POST" action="login.php">
            <input type="hidden" name="action" value="register">
            
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>
            
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label>Role</label>
                <select name="role" required>
                    <option value="" disabled selected>Select your role</option>
                    <option value="doctor">Doctor</option>
                    <option value="patient">Patient</option>
                    <!-- Admin is deliberately excluded from this HTML dropdown -->
                </select>
            </div>
            
            <button type="submit">Register</button>
        </form>
        <div class="toggle-text">
            Already have an ID? <a onclick="toggleForms()">Login here</a>
        </div>
    </div>

</div>

<!-- JAVASCRIPT FOR TOGGLING FORMS -->
<script>
    function toggleForms() {
        const loginSection = document.getElementById('login-section');
        const registerSection = document.getElementById('register-section');
        
        if (loginSection.classList.contains('hidden')) {
            loginSection.classList.remove('hidden');
            registerSection.classList.add('hidden');
        } else {
            loginSection.classList.add('hidden');
            registerSection.classList.remove('hidden');
        }
    }
</script>

</body>
</html>