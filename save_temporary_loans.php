<?php
require 'db.php';

$stmt = $conn->prepare("INSERT INTO temp_loans (source_name, borrow_date, amount, remarks, user_id, line) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssdsis", $_POST['source_name'], $_POST['borrow_date'], $_POST['amount'], $_POST['remarks'], $_SESSION['user_id'], $_SESSION['line']);
$stmt->execute();
header("Location: temporary_loans.php");
?>
