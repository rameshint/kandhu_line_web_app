<?php
require_once 'vendor/autoload.php';

use Kreait\Firebase\Factory;

echo "<h1>Testing Firebase Sync System</h1>";

try {
    echo "<p>1. Testing Firebase Realtime Database connection...</p>";

    $firebase = (new Factory)
        ->withServiceAccount(__DIR__ . '/firebase-service-account.json');

    $database = $firebase->createDatabase();

    echo "<p style='color: green;'>✓ Firebase connection established!</p>";

    echo "<p>2. Testing agent sync structure...</p>";

    $testAgent = [
        'agentId' => 999,
        'name' => 'Test Agent',
        'contactNo' => '1234567890',
        'macAddress' => '00:1B:44:11:3A:B7',
        'status' => 1,
        'createdAt' => (new DateTime())->format('c')
    ];

    $database->getReference('agents/999')->set($testAgent);

    echo "<p style='color: green;'>✓ Agent sync structure working!</p>";

    echo "<p>3. Testing pending loan sync structure...</p>";

    $testLoan = [
        'loanId' => 'LOAN_999999',
        'customerNo' => 'C001',
        'customerName' => 'Test Customer',
        'balanceAmount' => 5000.00,
        'closeDate' => (new DateTime('+30 days'))->format('c'),
        'emiAmount' => 500.00,
        'todayCollectedAmount' => 0.00,
        'collectedDate' => null,
        'lastUpdated' => (new DateTime())->format('c')
    ];

    $database->getReference('pending_loans/LOAN_999999')->set($testLoan);

    echo "<p style='color: green;'>✓ Pending loan sync structure working!</p>";

    echo "<p>4. Testing collection update in pending loans...</p>";

    // Update the test loan with collection data
    $database->getReference('pending_loans/LOAN_999999')->update([
        'todayCollectedAmount' => 500.00,
        'collectedDate' => (new DateTime())->format('c'),
        'syncFlag' => false
    ]);

    echo "<p style='color: green;'>✓ Collection update in pending loans working!</p>";

    echo "<h3 style='color: green;'>🎉 All Firebase sync structures are working perfectly!</h3>";

    echo "<h3>Firebase Database Structure:</h3>";
    echo "<pre>";
    echo "├── agents/\n";
    echo "│   └── {agentId}\n";
    echo "│       ├── agentId\n";
    echo "│       ├── name\n";
    echo "│       ├── contactNo\n";
    echo "│       ├── macAddress (for mobile restriction)\n";
    echo "│       ├── status\n";
    echo "│       └── createdAt\n";
    echo "└── pending_loans/\n";
    echo "    └── {LOAN_xxxxxx}\n";
    echo "        ├── loanId\n";
    echo "        ├── customerNo\n";
    echo "        ├── customerName\n";
    echo "        ├── balanceAmount\n";
    echo "        ├── closeDate\n";
    echo "        ├── emiAmount\n";
    echo "        ├── todayCollectedAmount (updated by mobile app)\n";
    echo "        ├── collectedDate (updated by mobile app)\n";
    echo "        ├── syncFlag (for sync tracking)\n";
    echo "        └── lastUpdated\n";
    echo "</pre>";
    echo "<h3>Mobile App Authorization Flow:</h3>";
    echo "<ol>";
    echo "<li>Mobile app gets device MAC address</li>";
    echo "<li>Checks if MAC exists in Firebase agents collection</li>";
    echo "<li>If MAC not found → Show alert & block app</li>";
    echo "<li>If MAC found → Allow app to proceed</li>";
    echo "</ol>";

    echo "<h3>Collection Flow (Simplified):</h3>";
    echo "<ol>";
    echo "<li>Mobile app loads pending loans from Firebase</li>";
    echo "<li>Agent collects EMI from customer</li>";
    echo "<li>Mobile app directly updates 'todayCollectedAmount' and 'collectedDate' in pending_loans</li>";
    echo "<li>Web app syncs these collections to MySQL collections_temp table</li>";
    echo "<li>Admin approves and moves from temp to main collections table</li>";
    echo "<li>Daily reset clears todayCollectedAmount for next day</li>";
    echo "</ol>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
