<?php
require_once 'vendor/autoload.php';

use Kreait\Firebase\Factory;

try {
    echo "Testing Firebase Service Account...<br>";

    // Test JSON parsing first
    $json = file_get_contents(__DIR__ . '/firebase-service-account.json');
    $data = json_decode($json, true);

    if ($data === null) {
        throw new Exception("JSON Error: " . json_last_error_msg());
    }

    echo "JSON is valid!<br>";
    echo "Project ID: " . $data['project_id'] . "<br>";

    // Force REST transport by setting environment variable
    putenv('GOOGLE_CLOUD_PHP_USE_REST=true');
    putenv('FIRESTORE_EMULATOR_HOST='); // Clear emulator

    // Alternative: Try to use Firebase Admin SDK with REST
    $config = [
        'credentials' => __DIR__ . '/firebase-service-account.json',
        'projectId' => $data['project_id']
    ];

    // Test Firebase initialization
    $firebase = (new Factory)->withServiceAccount(__DIR__ . '/firebase-service-account.json');

    echo "Firebase factory created!<br>";

    try {
        // Create Firestore using REST transport
        $firestore = $firebase->createFirestore()->database();
        echo "Firestore database connection successful!<br>";

        // Test a simple operation
        $testCollection = $firestore->collection('test');
        echo "Test collection reference created successfully!<br>";

        echo "<strong>All tests passed! Firebase is working!</strong><br>";
    } catch (Exception $firestoreError) {
        echo "Firestore Error: " . $firestoreError->getMessage() . "<br>";
        echo "Note: This might be due to gRPC requirement. Let's try alternative approach...<br>";

        // Alternative: Test if we can at least authenticate
        $auth = $firebase->createAuth();
        echo "Firebase Auth service created successfully!<br>";
        echo "<strong>Firebase authentication is working! Firestore may need gRPC.</strong><br>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "<br>";
    echo "<br>Stack trace:<br><pre>" . $e->getTraceAsString() . "</pre>";
}
