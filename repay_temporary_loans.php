<?php
require 'db.php'; 

$stmt = $conn->prepare("INSERT INTO temp_loan_payments (temp_loan_id,head, repaid_date, amount,user_id)values(?,?, ?, ?, ?)");
$stmt->bind_param("issdi", $_POST['id'], $_POST['head'], $_POST['repay_date'], $_POST['repaid_amount'], $_SESSION['user_id']);
$stmt->execute();
header("Location: temporary_loans.php");
?>
