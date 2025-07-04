<?php
require 'db.php';

$loan_ids = $_POST['loan_id'];
$amounts = $_POST['amount'];
$interests = $_POST['interest'];
$agent_id = $_POST['agent_id'];
$date = $_POST['collection_date'];

for ($i = 0; $i < count($loan_ids); $i++) {
    $loan_id = $loan_ids[$i];
    $amount = $amounts[$i];
    $interest = $interests[$i];
    if ($amount > 0 ) {
        $head = 'EMI';
        $stmt = $conn->prepare("INSERT INTO collections (loan_id, agent_id, collection_date,head, amount, user_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissdi", $loan_id, $agent_id, $date, $head, $amount, $_SESSION['user_id']);
        $stmt->execute();
    }
    if ($interest > 0 ) {
        $head = 'Interest';
        $stmt = $conn->prepare("INSERT INTO collections (loan_id, agent_id, collection_date,head, amount, user_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissdi", $loan_id, $agent_id, $date, $head, $interest, $_SESSION['user_id']);
        $stmt->execute();
    }
}
echo json_encode(['status' => 'success']);
