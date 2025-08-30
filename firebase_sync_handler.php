<?php
// Firebase sync handler - separate file for AJAX calls
session_start();
require_once 'vendor/autoload.php';

use Kreait\Firebase\Factory;
use Google\Cloud\Firestore\FirestoreClient;

class FirebaseMySQLSync
{
    private $firebase;
    private $firestore;
    private $pdo;

    public function __construct()
    {
        // Initialize Firebase
        $this->firebase = (new Factory)
            ->withServiceAccount(__DIR__ . '/firebase-service-account.json');

        // Initialize Firestore database using Google Cloud client with REST transport
        try {
            // Use Google Cloud Firestore client directly with REST transport
            $this->firestore = new FirestoreClient([
                'keyFilePath' => __DIR__ . '/firebase-service-account.json',
                'transport' => 'rest'
            ]);
        } catch (Exception $e) {
            // Fallback: try Firebase SDK default
            $this->firestore = $this->firebase->createFirestore()->database();
        }

        // Initialize MySQL PDO connection using db.php settings
        // get the settings from db.php
        require_once 'db.php';
        $this->pdo = new PDO(
            "mysql:host=$servername;port=$dbport;dbname=$dbname",
            $username,
            $password,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }    // === PART 1: Sync customers from MySQL → Firebase ===
    public function syncCustomersToFirebase($line = 'Daily')
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT customer_no, NAME, address_line1, district, contact_no 
                FROM customers 
                WHERE line = ?
            ");
            $stmt->execute([$line]);
            $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $customersSynced = 0;

            foreach ($customers as $customer) {
                $customerDoc = $this->firestore->collection('customers')->document($customer['customer_no']);

                $customerData = [
                    'customerNumber' => $customer['customer_no'],
                    'name' => $customer['NAME'],
                    'address' => ($customer['address_line1'] ?? '') . ', ' . ($customer['district'] ?? ''),
                    'phone' => $customer['contact_no'] ?? '',
                    'createdAt' => new DateTime()
                ];

                $customerDoc->set($customerData, ['merge' => true]);
                $customersSynced++;
            }

            return ['success' => true, 'customers_synced' => count($customers)];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // === PART 2: Sync loans from MySQL → Firebase ===
    public function syncLoansToFirebase($loanType = 'Daily')
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    l.id AS loan_id,
                    customers.customer_no,
                    l.loan_date AS loanOpeningDate,
                    l.expiry_date AS loanClosingDate,
                    l.amount AS loanAmount,
                    IFNULL(c.collected, 0) AS collectedAmount,
                    l.amount - IFNULL(c.collected, 0) AS balanceAmount
                FROM loans l
                JOIN customers ON customers.id = l.customer_id
                LEFT JOIN (
                    SELECT loan_id, SUM(amount) AS collected 
                    FROM collections 
                    WHERE head = 'EMI' AND flag = 1
                    GROUP BY loan_id
                ) c ON c.loan_id = l.id
                WHERE l.status = 'Open' 
                AND l.loan_type = ? 
                AND l.amount - IFNULL(c.collected, 0) > 0
            ");
            $stmt->execute([$loanType]);
            $loans = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $loansSynced = 0;

            foreach ($loans as $loan) {
                $loanId = 'LOAN_' . str_pad($loan['loan_id'], 6, '0', STR_PAD_LEFT);
                $loanDoc = $this->firestore->collection('loans')->document($loanId);

                $loanData = [
                    'loanId' => $loanId,
                    'customerNumber' => $loan['customer_no'],
                    'loanAmount' => floatval($loan['loanAmount']),
                    'collectedAmount' => floatval($loan['collectedAmount']),
                    'balanceAmount' => floatval($loan['balanceAmount']),
                    'loanOpeningDate' => new DateTime($loan['loanOpeningDate']),
                    'loanClosingDate' => new DateTime($loan['loanClosingDate'])
                ];

                $loanDoc->set($loanData, ['merge' => true]);
                $loansSynced++;
            }

            return ['success' => true, 'loans_synced' => $loansSynced];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // === PART 3: Sync collections from Firebase → MySQL temp table ===
    public function syncCollectionsFromFirebase()
    {
        try {
            $collectionsRef = $this->firestore->collection('collections');
            $query = $collectionsRef->where('syncFlag', '=', false);
            $documents = $query->documents();

            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            foreach ($documents as $document) {
                $data = $document->data();

                try {
                    // Begin transaction
                    $this->pdo->beginTransaction();

                    // Lookup loan_id using Firebase loanId
                    $loanStmt = $this->pdo->prepare("SELECT id FROM loans WHERE id = ?");
                    $mysqlLoanId = intval(str_replace('LOAN_', '', $data['loanId']));
                    $loanStmt->execute([$mysqlLoanId]);
                    $loanResult = $loanStmt->fetch(PDO::FETCH_ASSOC);

                    if (!$loanResult) {
                        throw new Exception("Loan not found: " . $data['loanId']);
                    }

                    // Lookup agent_id using agentId
                    $agentStmt = $this->pdo->prepare("SELECT id FROM agents WHERE agent_id = ?");
                    $agentStmt->execute([$data['agentId']]);
                    $agentResult = $agentStmt->fetch(PDO::FETCH_ASSOC);

                    if (!$agentResult) {
                        throw new Exception("Agent not found: " . $data['agentId']);
                    }

                    // Convert timestamp to collection_date
                    $collectionDate = (new DateTime($data['timestamp']))->format('Y-m-d');
                    $collectionTime = (new DateTime($data['timestamp']))->format('H:i:s');

                    // Insert into MySQL collections_temp table (temporary storage)
                    $insertStmt = $this->pdo->prepare("
                        INSERT INTO collections_temp (
                            loan_id, agent_id, collection_date, collection_time, head, amount, 
                            firebase_collection_id, sync_status, created_on
                        ) VALUES (?, ?, ?, ?, 'EMI', ?, ?, 'synced', NOW())
                    ");

                    $insertStmt->execute([
                        $loanResult['id'],
                        $agentResult['id'],
                        $collectionDate,
                        $collectionTime,
                        $data['amount'],
                        $document->id() // Store Firebase document ID for reference
                    ]);

                    // Commit transaction
                    $this->pdo->commit();

                    // Update syncFlag = true in Firebase
                    $document->reference()->update([
                        ['path' => 'syncFlag', 'value' => true],
                        ['path' => 'syncedAt', 'value' => new DateTime()]
                    ]);

                    $successCount++;
                } catch (Exception $e) {
                    // Rollback transaction
                    $this->pdo->rollBack();
                    $errorCount++;
                    $errors[] = [
                        'collectionId' => $document->id(),
                        'error' => $e->getMessage()
                    ];
                }
            }

            return [
                'success' => true,
                'collections_synced' => $successCount,
                'errors' => $errorCount,
                'error_details' => $errors
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // === PART 4: Move collections from temp to main table (Admin function) ===
    public function moveCollectionsFromTemp($selectedDate, $userId)
    {
        try {
            $this->pdo->beginTransaction();

            // Get collections from temp table for selected date
            $tempStmt = $this->pdo->prepare("
                SELECT * FROM collections_temp 
                WHERE collection_date = ? AND sync_status = 'synced'
            ");
            $tempStmt->execute([$selectedDate]);
            $tempCollections = $tempStmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($tempCollections)) {
                throw new Exception("No collections found for date: " . $selectedDate);
            }

            $movedCount = 0;
            foreach ($tempCollections as $tempCollection) {
                // Insert into main collections table
                $insertStmt = $this->pdo->prepare("
                    INSERT INTO collections (
                        loan_id, agent_id, collection_date, head, amount, flag, user_id, created_on
                    ) VALUES (?, ?, ?, ?, ?, 0, ?, ?)
                ");

                $insertStmt->execute([
                    $tempCollection['loan_id'],
                    $tempCollection['agent_id'],
                    $tempCollection['collection_date'],
                    $tempCollection['head'],
                    $tempCollection['amount'],
                    $userId,
                    $tempCollection['created_on']
                ]);

                // Update temp table status
                $updateStmt = $this->pdo->prepare("
                    UPDATE collections_temp 
                    SET sync_status = 'moved', moved_on = NOW(), moved_by = ?
                    WHERE id = ?
                ");
                $updateStmt->execute([$userId, $tempCollection['id']]);

                $movedCount++;
            }

            $this->pdo->commit();

            return [
                'success' => true,
                'collections_moved' => $movedCount,
                'date' => $selectedDate
            ];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // === Get pending collections from temp table ===
    public function getPendingCollections()
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    ct.collection_date,
                    COUNT(*) as collection_count,
                    SUM(ct.amount) as total_amount,
                    GROUP_CONCAT(DISTINCT a.name) as agents
                FROM collections_temp ct
                LEFT JOIN agents a ON a.id = ct.agent_id
                WHERE ct.sync_status = 'synced'
                GROUP BY ct.collection_date
                ORDER BY ct.collection_date DESC
            ");
            $stmt->execute();
            $pendingCollections = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'pending_collections' => $pendingCollections
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // === Get detailed collections for a specific date ===
    public function getCollectionsByDate($date)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    ct.*,
                    c.customer_no,
                    c.name as customer_name,
                    a.name as agent_name,
                    l.amount as loan_amount,
                    l.loan_type
                FROM collections_temp ct
                LEFT JOIN loans l ON l.id = ct.loan_id
                LEFT JOIN customers c ON c.id = l.customer_id
                LEFT JOIN agents a ON a.id = ct.agent_id
                WHERE ct.collection_date = ? AND ct.sync_status = 'synced'
                ORDER BY ct.created_on
            ");
            $stmt->execute([$date]);
            $collections = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'collections' => $collections,
                'date' => $date
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // === Full Sync Operation ===
    public function performFullSync($line = 'Daily')
    {
        $results = [
            'timestamp' => (new DateTime())->format('c'),
            'customers' => $this->syncCustomersToFirebase($line),
            'loans' => $this->syncLoansToFirebase($line),
            'collections' => $this->syncCollectionsFromFirebase()
        ];

        return $results;
    }
}

// === AJAX Handler ===
if (isset($_POST['action']) || isset($_GET['action'])) {
    header('Content-Type: application/json');

    try {
        $sync = new FirebaseMySQLSync();
        $action = $_POST['action'] ?? $_GET['action'];
        $line = $_POST['line'] ?? $_GET['line'] ?? 'Daily';

        switch ($action) {
            case 'sync_customers':
                echo json_encode($sync->syncCustomersToFirebase($line));
                break;

            case 'sync_loans':
                echo json_encode($sync->syncLoansToFirebase($line));
                break;

            case 'sync_collections':
                echo json_encode($sync->syncCollectionsFromFirebase());
                break;

            case 'full_sync':
                echo json_encode($sync->performFullSync($line));
                break;

            case 'get_pending_collections':
                echo json_encode($sync->getPendingCollections());
                break;

            case 'get_collections_by_date':
                $date = $_POST['date'] ?? $_GET['date'] ?? date('Y-m-d');
                echo json_encode($sync->getCollectionsByDate($date));
                break;

            case 'move_collections':
                $date = $_POST['date'] ?? $_GET['date'] ?? '';
                $userId = $_SESSION['user_id'] ?? 1;
                if ($date) {
                    echo json_encode($sync->moveCollectionsFromTemp($date, $userId));
                } else {
                    echo json_encode(['success' => false, 'error' => 'Date parameter required']);
                }
                break;

            default:
                echo json_encode(['success' => false, 'error' => 'Invalid action']);
                break;
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}
