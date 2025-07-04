<?php
require 'db.php';

$id = $_POST['id'] ?? null;
$customer_no = $_POST['customer_no'];

// Check for duplicate customer_no
if (!$id) { // Creating new customer
    
    $stmt = $conn->prepare("SELECT id FROM customers WHERE customer_no = ?");
    $stmt->bind_param("s", $customer_no);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Customer No already exists.']);
        exit;
    }
} else { // Updating customer - check if customer_no belongs to another record
    
    $stmt = $conn->prepare("SELECT id FROM customers WHERE customer_no = ? AND id != ?");
    $stmt->bind_param("si", $customer_no, $id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Customer No already exists.']);
        exit;
    }
}

if ($id) {
    $sql = "UPDATE customers SET customer_no=?, name=?,  address_line1=?, address_line2=?, district=?, pincode=?, contact_no=?, secondary_contact_no=?, aadharcard=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssssi", $_POST['customer_no'], $_POST['name'], $_POST['address_line1'], $_POST['address_line2'], $_POST['district'], $_POST['pincode'], $_POST['contact_no'], $_POST['secondary_contact_no'], $_POST['aadharcard'], $id);
} else {
    $sql = "INSERT INTO customers (customer_no, name,   address_line1, address_line2, district, pincode, contact_no, secondary_contact_no, aadharcard, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssssi", $_POST['customer_no'], $_POST['name'],  $_POST['address_line1'], $_POST['address_line2'], $_POST['district'], $_POST['pincode'], $_POST['contact_no'], $_POST['secondary_contact_no'], $_POST['aadharcard'], $_SESSION['user_id']);
}

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error.']);
}
