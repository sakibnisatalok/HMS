
<?php


$host = '127.0.0.1'; //   'localhost' is also fine, but 127.0.0.1 prevents socket issues
$dbname = 'hms_opd_new'; // Replace with the actual name of your imported database
$username = 'root'; //   Default MySQL username for local environments
$password = ''; //   Default MySQL password is usually empty
$charset = 'utf8mb4';

// Set up the Data Source Name (DSN)
$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

// Standard, secure options for PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on SQL errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Return results as associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Use native prepared statements for security
];

try {
    // Instantiate the PDO connection
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    // If the connection fails, stop execution and show the error
    die("Database connection failed: " . $e->getMessage());
}
?>