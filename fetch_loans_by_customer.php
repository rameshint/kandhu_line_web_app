<?php
require 'db.php';
$customer_id = $_GET['customer_id'];

$stmt = $conn->prepare("
    SELECT COUNT(*) cnt FROM collections c 
    INNER JOIN loans l ON l.id = c.loan_id
    INNER JOIN customers cs ON cs.id = l.customer_id
    WHERE cs.id = ? AND c.flag = 0
");
$stmt->bind_param("s", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
if ($row['cnt'] > 0){
    
    // Send JSON error message
    echo json_encode([
        "error" => "Error",
        "message" => "Amount already entered."
    ]);
    exit;
}

$stmt = $conn->prepare("
    SELECT l.id,l.loan_date,l.expiry_date ,l.loan_type, l.amount, l.amount - IFNULL(c.collected,0) balance, 
    case when l.expiry_date IS NOT NULL and l.status = 'Open' then if(l.expiry_date<CURRENT_DATE,1,0) ELSE 0 END overdue
    FROM loans l
    JOIN customers ON customers.id = l.customer_id
    LEFT JOIN(SELECT loan_id , SUM(amount) collected FROM collections where head = 'EMI' GROUP BY loan_id) c ON c.loan_id = l.id
    WHERE customers.id = ? and l.status = 'Open' AND l.loan_type = ?
");
$stmt->bind_param("ss", $customer_id, $_SESSION['line']);
$stmt->execute();
$result = $stmt->get_result();

$loans = [];
while ($row = $result->fetch_assoc()) {
    $loans[] = $row;
}
 
echo json_encode(['status' => 'success', 'loans' => $loans]);
