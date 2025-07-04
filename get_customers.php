<?php
require 'db.php';
$stmt = $conn->prepare("SELECT * FROM customers where line = ?  ORDER BY id DESC");
$stmt->bind_param('s', $_SESSION['line']);
$stmt->execute();
$result = $stmt->get_result();
$customers = [];

while ($row = $result->fetch_assoc()) {
    $customers[] = $row;
}

echo json_encode($customers);
