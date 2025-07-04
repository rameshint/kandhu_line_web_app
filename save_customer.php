<?php
require 'db.php';
#ini_set("display_errors",1);

$id = $_POST['id'] ?? null;
$customer_no = $_POST['customer_no'];

// Check for duplicate customer_no
if (!$id) { // Creating new customer
    
    $stmt = $conn->prepare("SELECT id FROM customers WHERE customer_no = ? and line = ?");
    $stmt->bind_param("ss", $customer_no, $_SESSION['line']);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Customer No already exists.']);
        exit;
    }
} else { // Updating customer - check if customer_no belongs to another record
    
    $stmt = $conn->prepare("SELECT id FROM customers WHERE customer_no = ? AND id != ? and line = ?");
    $stmt->bind_param("sis", $customer_no, $id, $_SESSION['line']);
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
    $sql = "INSERT INTO customers (customer_no, name,   address_line1, address_line2, district, pincode, contact_no, secondary_contact_no, aadharcard, user_id, line) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssssis", $_POST['customer_no'], $_POST['name'],  $_POST['address_line1'], $_POST['address_line2'], $_POST['district'], $_POST['pincode'], $_POST['contact_no'], $_POST['secondary_contact_no'], $_POST['aadharcard'], $_SESSION['user_id'], $_SESSION['line']);
}

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error.']);
}
