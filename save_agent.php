<?php
require 'db.php';

$id = $_POST['id'] ?? ''; 
$name = $_POST['name'];
$contact = $_POST['contact_no'];
$address = $_POST['address'];
$status = $_POST['status'];

if ($id == '') {
    
    $stmt = $conn->prepare("INSERT INTO agents ( name, contact_no, address, status, user_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssii", $name, $contact, $address, $status, $_SESSION['user_id']);
} else {
    // Update
    $stmt = $conn->prepare("UPDATE agents SET name=?, contact_no=?, address=?, status=? WHERE id=?");
    $stmt->bind_param("sssii",  $name, $contact, $address, $status, $id);
}
$stmt->execute();
echo json_encode(['status' => 'success']);
