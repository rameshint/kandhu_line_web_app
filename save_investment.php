<?php
require 'db.php';

$date = $_POST['investment_date'];
$source = $_POST['source'];
$desc = $_POST['description'];
$amount = $_POST['amount'];
$agent_id = $_POST['agent_id'];

$stmt = $conn->prepare("INSERT INTO investments (investment_date, source, description, amount, agent_id, user_id) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssdii", $date, $source, $desc, $amount, $agent_id, $_SESSION['user_id']);

if ($stmt->execute()) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to save investment."]);
}
?>
