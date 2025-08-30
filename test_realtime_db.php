<?php
require_once 'vendor/autoload.php';

use Kreait\Firebase\Factory;

try {
    echo "Testing Firebase Realtime Database (Alternative to Firestore)...<br>";

    // Test JSON parsing first
    $json = file_get_contents(__DIR__ . '/firebase-service-account.json');
    $data = json_decode($json, true);

    if ($data === null) {
        throw new Exception("JSON Error: " . json_last_error_msg());
    }

    echo "JSON is valid!<br>";
    echo "Project ID: " . $data['project_id'] . "<br>";

    // Test Firebase initialization
    $firebase = (new Factory)->withServiceAccount(__DIR__ . '/firebase-service-account.json');

    echo "Firebase factory created!<br>";

    // Try Realtime Database instead of Firestore
    $database = $firebase->createDatabase();
    echo "Firebase Realtime Database connection successful!<br>";

    // Test a simple operation
    $reference = $database->getReference('test');
    echo "Database reference created successfully!<br>";

    // Try to write a test value
    $reference->set(['timestamp' => time(), 'message' => 'Firebase connection test']);
    echo "Test data written to database!<br>";

    // Try to read the test value
    $testData = $reference->getValue();
    echo "Test data read from database: " . json_encode($testData) . "<br>";

    echo "<strong>Firebase Realtime Database is working perfectly!</strong><br>";
    echo "<em>We can use Realtime Database instead of Firestore for the sync.</em><br>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "<br>";
    echo "<br>Stack trace:<br><pre>" . $e->getTraceAsString() . "</pre>";
}
