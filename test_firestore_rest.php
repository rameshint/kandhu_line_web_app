<?php
require_once 'vendor/autoload.php';

use Google\Cloud\Firestore\FirestoreClient;

echo "<h1>Testing Firestore with REST Transport</h1>";

try {
    echo "<p>1. Initializing Firestore client with REST transport...</p>";

    $firestore = new FirestoreClient([
        'keyFilePath' => __DIR__ . '/firebase-service-account.json',
        'transport' => 'rest'
    ]);

    echo "<p style='color: green;'>✓ Firestore client initialized successfully with REST transport!</p>";

    echo "<p>2. Testing connection by creating a test document...</p>";

    $testCollection = $firestore->collection('test');
    $testDoc = $testCollection->document('connection-test');

    $testDoc->set([
        'message' => 'Hello from WAMP server!',
        'timestamp' => new DateTime(),
        'test' => true
    ]);

    echo "<p style='color: green;'>✓ Test document created successfully!</p>";

    echo "<p>3. Testing read operation...</p>";

    $snapshot = $testDoc->snapshot();
    if ($snapshot->exists()) {
        $data = $snapshot->data();
        echo "<p style='color: green;'>✓ Test document read successfully:</p>";
        echo "<pre>" . json_encode($data, JSON_PRETTY_PRINT) . "</pre>";
    } else {
        echo "<p style='color: red;'>✗ Test document not found</p>";
    }

    echo "<p style='color: green; font-weight: bold;'>🎉 Firestore with REST transport is working perfectly!</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p style='color: red;'>Stack trace:</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
