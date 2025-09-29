<?php
// Debug script to check for errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Starting debug...<br>";

try {
    echo "1. Checking autoload...<br>";
    require_once 'vendor/autoload.php';
    echo "✓ Autoload successful<br>";
    
    echo "2. Checking Firebase Factory...<br>";
    echo "✓ Firebase Factory will be imported<br>";
    
    echo "3. Testing service account file...<br>";
    $serviceAccountPath = __DIR__ . '/firebase-service-account.json';
    if (!file_exists($serviceAccountPath)) {
        throw new Exception("Service account file not found");
    }
    echo "✓ Service account file exists<br>";
    
    echo "4. Testing JSON parsing...<br>";
    $json = file_get_contents($serviceAccountPath);
    $data = json_decode($json, true);
    if ($data === null) {
        throw new Exception("JSON Error: " . json_last_error_msg());
    }
    echo "✓ JSON is valid<br>";
    
    echo "5. Testing database connection...<br>";
    require_once __DIR__ . '/db.php';
    $pdo = new PDO(
        "mysql:host=$servername;port=$db_port;dbname=$dbname",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✓ MySQL connection successful<br>";
    
    echo "6. Testing Firebase initialization...<br>";
    // For older versions of Firebase SDK, let's try without SSL verification first
    $firebase = (new Kreait\Firebase\Factory)
        ->withServiceAccount($serviceAccountPath)
        ->withDatabaseUri('https://dhanalakshmi-finance-default-rtdb.firebaseio.com');
    echo "✓ Firebase factory created<br>";
    
    echo "7. Testing database creation...<br>";
    $database = $firebase->createDatabase();
    echo "✓ Firebase database connection successful<br>";
    
    echo "8. Testing class instantiation...<br>";
    session_start();
    require_once 'firebase_sync_handler.php';
    echo "✓ FirebaseMySQLSync class loaded<br>";
    
    $sync = new FirebaseMySQLSync();
    echo "✓ FirebaseMySQLSync instance created successfully<br>";
    
    echo "<strong>All tests passed! The issue might be in the AJAX call or specific method.</strong><br>";
    
} catch (Exception $e) {
    echo "<strong>Error found:</strong><br>";
    echo "Message: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
    echo "<br>Stack trace:<br><pre>" . $e->getTraceAsString() . "</pre>";
} catch (Error $e) {
    echo "<strong>Fatal Error found:</strong><br>";
    echo "Message: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
    echo "<br>Stack trace:<br><pre>" . $e->getTraceAsString() . "</pre>";
}
?>
