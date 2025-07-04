<?php
require 'db.php';

$sql = "SELECT i.*, a.name AS agent_name 
        FROM investments i 
        LEFT JOIN agents a ON a.id = i.agent_id 
        where line = '".$_SESSION['line']."'
        ORDER BY i.investment_date DESC";

$result = $conn->query($sql);
$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
?>
