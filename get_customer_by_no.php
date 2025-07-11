<?php
require 'db.php';

$customer_no = $_GET['customer_no'] ?? '';
$stmt = $conn->prepare("SELECT * FROM customers WHERE customer_no = ? and line=?");
$stmt->bind_param("ss", $customer_no, $_SESSION['line']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Customer not found']);
} else {
    $row = $result->fetch_assoc();
    echo json_encode(['status' => 'success', 'customer' => $row]);
}
