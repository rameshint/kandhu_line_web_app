<?php
require 'db.php';
$stmt = $conn->prepare("SELECT c.* ,IFNULL( a.open_loans,0) open_loans FROM customers c 
LEFT JOIN (SELECT customer_id,COUNT(*) open_loans FROM loans WHERE STATUS  = 'Open' GROUP by customer_id) a ON a.customer_id = c.id
where line = ? ORDER BY id DESC");
$stmt->bind_param('s', $_SESSION['line']);
$stmt->execute();
$result = $stmt->get_result();
$customers = [];

while ($row = $result->fetch_assoc()) {
    $customers[] = $row;
}

echo json_encode($customers);
