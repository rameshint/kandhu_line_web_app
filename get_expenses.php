<?php
require 'db.php';

$sql = "SELECT e.*, a.name AS agent_name 
        FROM expenses e 
        LEFT JOIN agents a ON a.id = e.agent_id 
        ORDER BY e.expense_date DESC";

$result = $conn->query($sql);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
?>
