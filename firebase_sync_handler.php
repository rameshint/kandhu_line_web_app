<?php
// Firebase sync handler - separate file for AJAX calls
session_start();
require_once 'vendor/autoload.php';

use Kreait\Firebase\Factory;

class FirebaseMySQLSync
{
    private $firebase;
    private $database;
    private $pdo;

    public function __construct()
    {
        try {
            // Initialize Firebase with correct database URL
            $this->firebase = (new Factory)
                ->withServiceAccount(__DIR__ . '/firebase-service-account.json')
                ->withDatabaseUri('https://dhanalakshmi-finance-default-rtdb.firebaseio.com');

            // Initialize Realtime Database
            $this->database = $this->firebase->createDatabase();

            // Inherit MySQL DB variables from db.php
            require_once __DIR__ . '/db.php';
            $this->pdo = new PDO(
                "mysql:host=$servername;port=$db_port;dbname=$dbname",
                $username,
                $password,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (Exception $e) {
            error_log("Firebase initialization error: " . $e->getMessage());
            throw $e;
        }
    }

    // === PART 1: Sync agents from MySQL → Firebase ===
    public function syncAgentsToFirebase()
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, name, contact_no, address, mac_address, status 
                FROM agents 
                WHERE status = 1
            ");
            $stmt->execute();
            $agents = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $agentsSynced = 0;

            foreach ($agents as $agent) {
                $agentData = [
                    'agentId' => $agent['id'],
                    'name' => $agent['name'],
                    'contactNo' => $agent['contact_no'] ?? '',
                    'address' => $agent['address'] ?? '',
                    'macAddress' => $agent['mac_address'] ?? '',
                    'status' => intval($agent['status']),
                    'createdAt' => (new DateTime())->format('c')
                ];

                $this->database->getReference('agents/' . $agent['id'])->set($agentData);
                $agentsSynced++;
            }

            return ['success' => true, 'agents_synced' => $agentsSynced];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // === PART 2: Sync pending loans from MySQL → Firebase ===
    public function syncPendingLoansToFirebase()
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    l.id loan_id, 
                    c.customer_no, 
                    c.name, 
                    l.amount - IFNULL(a.collected_amt, 0) balance_amount, 
                    l.expiry_date close_date, 
                    round(l.amount/l.tenure, 0) emi_amount,
                    IFNULL(t.today_collected_amount, 0) today_collected_amount,
                    t.collected_date
                FROM customers c 
                INNER JOIN loans l ON l.customer_id = c.id AND l.loan_closed IS NULL
                LEFT JOIN (
                    SELECT loan_id, SUM(amount) collected_amt 
                    FROM collections cl 
                    WHERE cl.head='EMI' 
                    GROUP BY loan_id
                ) a ON a.loan_id = l.id
                LEFT JOIN (
                    SELECT loan_id, SUM(amount) today_collected_amount, MAX(collection_date) collected_date
                    FROM collections_temp 
                    WHERE collection_date = CURDATE() AND sync_status = 'synced'
                    GROUP BY loan_id
                ) t ON t.loan_id = l.id
                WHERE l.loan_type = 'Daily' AND l.amount - IFNULL(a.collected_amt, 0) > 0
            ");
            $stmt->execute();
            $loans = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $loansSynced = 0;

            foreach ($loans as $loan) {
                $loanId = 'LOAN_' . str_pad($loan['loan_id'], 6, '0', STR_PAD_LEFT);

                $loanData = [
                    'loanId' => $loanId,
                    'customerNo' => $loan['customer_no'],
                    'customerName' => $loan['name'],
                    'balanceAmount' => floatval($loan['balance_amount']),
                    'closeDate' => (new DateTime($loan['close_date']))->format('c'),
                    'emiAmount' => floatval($loan['emi_amount']),
                    'todayCollectedAmount' => floatval($loan['today_collected_amount']),
                    'collectedDate' => $loan['collected_date'] ? (new DateTime($loan['collected_date']))->format('c') : null,
                    'lastUpdated' => (new DateTime())->format('c')
                ];

                $this->database->getReference('pending_loans/' . $loanId)->set($loanData);
                $loansSynced++;
            }

            return ['success' => true, 'pending_loans_synced' => $loansSynced];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // === PART 3: Sync collections from Firebase → MySQL temp table ===
    public function syncCollectionsFromFirebase()
    {
        try {
            $collectionsRef = $this->database->getReference('collections');
            $collectionsSnapshot = $collectionsRef->getSnapshot();
            $collectionsData = $collectionsSnapshot->getValue();

            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            if ($collectionsData) {
                foreach ($collectionsData as $collectionId => $data) {
                    // Skip if already synced
                    if (isset($data['syncFlag']) && $data['syncFlag'] === true) {
                        continue;
                    }

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
                        $agentStmt = $this->pdo->prepare("SELECT id FROM agents WHERE id = ?");
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
                            $collectionId // Store Firebase collection ID for reference
                        ]);

                        // Commit transaction
                        $this->pdo->commit();

                        // Update syncFlag = true in Firebase
                        $this->database->getReference('collections/' . $collectionId)->update([
                            'syncFlag' => true,
                            'syncedAt' => (new DateTime())->format('c')
                        ]);

                        // Update the pending loan with today's collection
                        $this->updatePendingLoanCollection($data['loanId'], $data['amount'], $collectionDate);

                        $successCount++;
                    } catch (Exception $e) {
                        // Rollback transaction
                        $this->pdo->rollBack();
                        $errorCount++;
                        $errors[] = [
                            'collectionId' => $collectionId,
                            'error' => $e->getMessage()
                        ];
                    }
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

    // === Helper function to update pending loan collection in Firebase ===
    private function updatePendingLoanCollection($loanId, $amount, $collectionDate)
    {
        try {
            $pendingLoanRef = $this->database->getReference('pending_loans/' . $loanId);
            $snapshot = $pendingLoanRef->getSnapshot();
            
            if ($snapshot->exists()) {
                $currentData = $snapshot->getValue();
                $currentTodayAmount = floatval($currentData['todayCollectedAmount'] ?? 0);
                
                $pendingLoanRef->update([
                    'todayCollectedAmount' => $currentTodayAmount + floatval($amount),
                    'collectedDate' => (new DateTime($collectionDate))->format('c'),
                    'lastUpdated' => (new DateTime())->format('c')
                ]);
            }
        } catch (Exception $e) {
            // Log error but don't fail the main operation
            error_log("Failed to update pending loan collection: " . $e->getMessage());
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
                    ) VALUES (?, ?, ?, ?, ?, 1, ?, ?)
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
    public function performFullSync()
    {
        $results = [
            'timestamp' => (new DateTime())->format('c'),
            'agents' => $this->syncAgentsToFirebase(),
            'pending_loans' => $this->syncPendingLoansToFirebase(),
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

        switch ($action) {
            case 'sync_agents':
                echo json_encode($sync->syncAgentsToFirebase());
                break;

            case 'sync_pending_loans':
                echo json_encode($sync->syncPendingLoansToFirebase());
                break;

            case 'sync_collections':
                echo json_encode($sync->syncCollectionsFromFirebase());
                break;

            case 'full_sync':
                echo json_encode($sync->performFullSync());
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
?>
