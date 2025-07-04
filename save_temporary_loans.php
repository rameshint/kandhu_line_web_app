<?php
require 'db.php';

$stmt = $conn->prepare("INSERT INTO temp_loans (source_name, borrow_date, amount, remarks, user_id) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("ssdsi", $_POST['source_name'], $_POST['borrow_date'], $_POST['amount'], $_POST['remarks'], $_SESSION['user_id']);
$stmt->execute();
header("Location: temporary_loans.php");
?>
