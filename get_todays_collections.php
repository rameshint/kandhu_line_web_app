<?php
require 'db.php';
 
$date = $_GET['collection_date'];

$stmt = $conn->prepare("
    SELECT collections.id,customers.customer_no,a.name, collections.loan_id,collections.head,loans.loan_date, customers.name AS customer_name, collections.amount, collections.collection_date
    FROM collections
    JOIN loans ON loans.id = collections.loan_id and loans.loan_type = ?
    JOIN customers ON customers.id = loans.customer_id
    JOIN agents a ON a.id = collections.agent_id
    WHERE collections.flag = 0  
    ORDER BY collections.created_on DESC
");
$stmt->bind_param("s", $_SESSION['line']);
$stmt->execute();
$result = $stmt->get_result();

$collections = [];
while ($row = $result->fetch_assoc()) {
    $collections[] = $row;
}
echo json_encode($collections);
 
