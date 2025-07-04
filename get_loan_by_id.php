<?php
require 'db.php';

$id = $_GET['id'] ?? 0;
$stmt = $conn->prepare("SELECT l.* , a.name agent_name FROM loans l left join agents a on l.agent_id = a.id WHERE l.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$loan = $result->fetch_assoc();

$stmt = $conn->prepare("SELECT l.* , a.name agent_name FROM collections l left join agents a on l.agent_id = a.id WHERE l.loan_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$collections = [];
while($row = $result->fetch_assoc()){
    $collections[] = $row;
}

echo json_encode(['loan' => $loan, 'collections'=> $collections]);
