<?php
require 'db.php';
$result = $conn->query("SELECT id, closure_date created_on, total_loans, total_collections, total_investments, total_expenses, total_temp_loans, total_temp_loan_payments,
-total_loans + total_collections + total_investments - total_expenses + total_temp_loans - total_temp_loan_payments net, net_amount 
FROM day_summary ORDER BY closure_date DESC");
$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
?>
