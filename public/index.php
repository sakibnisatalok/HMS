
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo "My PHP Website"; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            color: #333;
            margin: 40px;
        }
        .container {
            max-width: 600px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

    <div style="position: absolute; top: 10px; right: 10px;">
        <a href="login.php">Login</a>
    </div>


    <?php
    // public/index.php
    
    // 1. Path to app/config/database.php from the public/ directory
    require_once __DIR__ . '/../app/config/databaseconnection.php';

     /** @var \PDO $pdo */
    
    // 2. Check if $pdo instance exists and run a basic query
    if (isset($pdo) && $pdo instanceof PDO) {
        try {
            // Run a quick query to fetch the database version
            $stmt = $pdo->query("SELECT VERSION() AS version");
            $version = $stmt->fetchColumn();
    
            echo "<div style='font-family: sans-serif; padding: 20px; border: 2px solid #2e7d32; background: #e8f5e9; color: #1b5e20; border-radius: 8px;'>";
            echo "<h2>✅ Database Connected Successfully!</h2>";
            echo "<p><strong>MySQL Server Version:</strong> " . htmlspecialchars($version) . "</p>";
            echo "</div>";
    
        } catch (PDOException $e) {
            echo "<div style='font-family: sans-serif; padding: 20px; border: 2px solid #c62828; background: #ffebee; color: #b71c1c; border-radius: 8px;'>";
            echo "<h2>❌ Connection Established, but Query Failed</h2>";
            echo "<p><strong>Error Details:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "</div>";
        }
    } else {
        echo "<div style='font-family: sans-serif; padding: 20px; border: 2px solid #c62828; background: #ffebee; color: #b71c1c; border-radius: 8px;'>";
        echo "<h2>❌ Database Connection Failed</h2>";
        echo "<p>The <code>\$pdo</code> variable was not properly created in <code>app/config/database.php</code>.</p>";
        echo "</div>";
    }
    ?>


    <br>
    <br>
    <br>
    <br>    
    

    <div class="container">
        <h1>Welcome to My Website</h1>
        <p>This is standard dummy text written in HTML.</p>

        <hr>

        <h3>Server Information (via PHP):</h3>
        <p>
            <strong>Current Date & Time:</strong> 
            <?php echo date('Y-m-d H:i:s'); ?>
        </p>
        
        <p>
            <strong>Status:</strong> 
            <?php 
                $status = "Online";
                echo "<span style='color: green;'>$status</span>"; 
            ?>
        </p>
    </div>

</body>
</html>
