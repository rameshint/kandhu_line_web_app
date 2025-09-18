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
        // Initialize Firebase
        $this->firebase = (new Factory)
            ->withServiceAccount(__DIR__ . '/firebase-service-account.json');

        // Initialize Realtime Database (no gRPC required)
        $this->database = $this->firebase->createDatabase();

        // Initialize MySQL PDO connection using db.php settings
        $this->pdo = new PDO(
            "mysql:host=localhost;port=3307;dbname=finance",
            "root",
            "",
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
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
                    round(l.amount/l.tenure, 0) emi_amount
                FROM customers c 
                INNER JOIN loans l ON l.customer_id = c.id AND l.loan_closed IS NULL
                LEFT JOIN (
                    SELECT loan_id, SUM(amount) collected_amt 
                    FROM collections cl 
                    WHERE cl.head='EMI' 
                    GROUP BY loan_id
                ) a ON a.loan_id = l.id
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
                    'todayCollectedAmount' => null,
                    'collectedDate' => null,
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

    // === PART 3: Sync collections from Firebase pending loans → MySQL temp table ===
    public function syncCollectionsFromFirebase()
    {
        try {
            $pendingLoansRef = $this->database->getReference('pending_loans');
            $pendingLoansSnapshot = $pendingLoansRef->getSnapshot();
            $pendingLoansData = $pendingLoansSnapshot->getValue();

            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            if ($pendingLoansData) {
                foreach ($pendingLoansData as $loanId => $loanData) {
                    // Check if there's today collection amount and it's not synced yet
                    if (
                        isset($loanData['todayCollectedAmount']) &&
                        floatval($loanData['todayCollectedAmount']) > 0 &&
                        (!isset($loanData['syncFlag']) || $loanData['syncFlag'] !== true)
                    ) {

                        try {
                            // Begin transaction
                            $this->pdo->beginTransaction();

                            // Lookup loan_id using Firebase loanId
                            $mysqlLoanId = intval(str_replace('LOAN_', '', $loanId));
                            $loanStmt = $this->pdo->prepare("SELECT id FROM loans WHERE id = ?");
                            $loanStmt->execute([$mysqlLoanId]);
                            $loanResult = $loanStmt->fetch(PDO::FETCH_ASSOC);

                            if (!$loanResult) {
                                throw new Exception("Loan not found: " . $loanId);
                            }

                            // Get agent ID from loan data or use default
                            $agentId = $loanData['agentId'] ?? 1; // Default agent if not specified

                            // Verify agent exists
                            $agentStmt = $this->pdo->prepare("SELECT id FROM agents WHERE id = ?");
                            $agentStmt->execute([$agentId]);
                            $agentResult = $agentStmt->fetch(PDO::FETCH_ASSOC);

                            if (!$agentResult) {
                                // Use default agent ID 1 if agent not found
                                $agentId = 1;
                            }

                            // Use collected date from Firebase or today's date
                            $collectionDate = isset($loanData['collectedDate']) ?
                                (new DateTime($loanData['collectedDate']))->format('Y-m-d') :
                                date('Y-m-d');
                            $collectionTime = date('H:i:s');

                            // Insert into MySQL collections_temp table
                            $insertStmt = $this->pdo->prepare("
                                INSERT INTO collections_temp (
                                    loan_id, agent_id, collection_date, collection_time, head, amount, 
                                    firebase_collection_id, sync_status, created_on
                                ) VALUES (?, ?, ?, ?, 'EMI', ?, ?, 'synced', NOW())
                            ");

                            $insertStmt->execute([
                                $loanResult['id'],
                                $agentId,
                                $collectionDate,
                                $collectionTime,
                                $loanData['todayCollectedAmount'],
                                $loanId // Store Firebase loan ID as reference
                            ]);

                            // Commit transaction
                            $this->pdo->commit();

                            // Mark as synced in Firebase
                            $this->database->getReference('pending_loans/' . $loanId)->update([
                                'syncFlag' => true,
                                'syncedAt' => (new DateTime())->format('c')
                            ]);

                            $successCount++;
                        } catch (Exception $e) {
                            // Rollback transaction
                            $this->pdo->rollBack();
                            $errorCount++;
                            $errors[] = [
                                'loanId' => $loanId,
                                'error' => $e->getMessage()
                            ];
                        }
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

    // === Helper function to reset today's collection after sync ===
    private function resetTodayCollectionAfterSync($loanId)
    {
        try {
            $pendingLoanRef = $this->database->getReference('pending_loans/' . $loanId);
            $snapshot = $pendingLoanRef->getSnapshot();

            if ($snapshot->exists()) {
                $pendingLoanRef->update([
                    'todayCollectedAmount' => 0,
                    'collectedDate' => null,
                    'syncFlag' => false,
                    'lastUpdated' => (new DateTime())->format('c')
                ]);
            }
        } catch (Exception $e) {
            // Log error but don't fail the main operation
            error_log("Failed to reset today collection: " . $e->getMessage());
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

    // === Reset all collections for new day ===
    public function resetDailyCollections()
    {
        try {
            $pendingLoansRef = $this->database->getReference('pending_loans');
            $pendingLoansSnapshot = $pendingLoansRef->getSnapshot();
            $pendingLoansData = $pendingLoansSnapshot->getValue();

            $resetCount = 0;

            if ($pendingLoansData) {
                foreach ($pendingLoansData as $loanId => $loanData) {
                    $this->resetTodayCollectionAfterSync($loanId);
                    $resetCount++;
                }
            }

            return ['success' => true, 'loans_reset' => $resetCount];
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

            case 'reset_daily_collections':
                echo json_encode($sync->resetDailyCollections());
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
