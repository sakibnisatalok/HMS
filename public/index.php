
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
