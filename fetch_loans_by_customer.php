<?php
require 'db.php';
$customer_no = $_GET['customer_no'];

$stmt = $conn->prepare("
    SELECT l.id,l.loan_date,l.loan_type, l.amount, l.amount - IFNULL(c.collected,0) balance, 
    case when l.expiry_date IS NOT NULL and l.status = 'Open' then if(l.expiry_date<CURRENT_DATE,1,0) ELSE 0 END overdue
    FROM loans l
    JOIN customers ON customers.id = l.customer_id
    LEFT JOIN(SELECT loan_id , SUM(amount) collected FROM collections where head = 'EMI' GROUP BY loan_id) c ON c.loan_id = l.id
    WHERE customers.customer_no = ? and l.status = 'Open'
");
$stmt->bind_param("s", $customer_no);
$stmt->execute();
$result = $stmt->get_result();

$loans = [];
while ($row = $result->fetch_assoc()) {
    $loans[] = $row;
}
echo json_encode($loans);
