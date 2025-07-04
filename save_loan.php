<?php
require 'db.php';

$id = $_POST['id'] ?? null;

if ($id) {
    $stmt = $conn->prepare("UPDATE loans SET loan_date=?, amount=?, interest=?, file_charge=?, loan_type=?, tenure=?, agent_id=?, expiry_date=? WHERE id=?");
    $stmt->bind_param("sdddssisi", $_POST['loan_date'], $_POST['amount'], $_POST['interest'], $_POST['file_charge'], $_POST['loan_type'], $_POST['tenure'], $_POST['agent_id'],$_POST['expiry_date'], $id);
} else {
    $stmt = $conn->prepare("INSERT INTO loans (customer_id, loan_date, amount, interest, file_charge, loan_type, tenure, agent_id, user_id,expiry_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isdddssiis", $_POST['customer_id'], $_POST['loan_date'], $_POST['amount'], $_POST['interest'], $_POST['file_charge'], $_POST['loan_type'], $_POST['tenure'], $_POST['agent_id'], $_SESSION['user_id'],$_POST['expiry_date']);
}

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Error saving loan']);
}
