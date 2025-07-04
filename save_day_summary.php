<?php
require 'db.php';
ini_set("display_errors", 1);
if ($_POST['net_amount'] != '') {

    $stmt = $conn->prepare("INSERT INTO day_summary (closure_date, total_loans,total_collections, total_investments, total_expenses,total_temp_loans, total_temp_loan_payments, net_amount, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sdddddddi", $_POST['closure_date'], $_POST['total_loans'], $_POST['total_collections'], $_POST['total_investments'], $_POST['total_expenses'], $_POST['total_temp_loans'], $_POST['total_temp_loan_payments'], $_POST['net_amount'], $_SESSION['user_id']);

    if ($stmt->execute()) {
        $summary_id = $conn->insert_id;

        $stmt = $conn->prepare("UPDATE loans SET flag=1, summary_id=? WHERE flag = 0");
        $stmt->bind_param("i", $summary_id);
        $stmt->execute();

        $stmt = $conn->prepare("UPDATE collections SET flag=1, summary_id=? WHERE flag = 0");
        $stmt->bind_param("i", $summary_id);
        $stmt->execute();

        $stmt = $conn->prepare("UPDATE investments SET flag=1, summary_id=? WHERE flag = 0");
        $stmt->bind_param("i", $summary_id);
        $stmt->execute();

        $stmt = $conn->prepare("UPDATE expenses SET flag=1, summary_id=? WHERE flag = 0");
        $stmt->bind_param("i", $summary_id);
        $stmt->execute();

        $stmt = $conn->prepare("UPDATE temp_loans SET flag=1, summary_id=? WHERE flag = 0");
        $stmt->bind_param("i", $summary_id);
        $stmt->execute();

        $stmt = $conn->prepare("UPDATE temp_loans l 
        INNER JOIN (
        SELECT i.temp_loan_id, SUM(i.amount) amount FROM temp_loan_payments i 
        WHERE i.flag = 0 and head = 'Principal'
        GROUP BY i.temp_loan_id) a ON a.temp_loan_id= l.id 
        SET repaid_amount = repaid_amount +  a.amount, repay_date = case when l.amount <= (repaid_amount +  a.amount) then CURRENT_DATE() ELSE NULL end");
        $stmt->execute();

        $stmt = $conn->prepare("UPDATE temp_loan_payments SET flag=1, summary_id=? WHERE flag = 0");
        $stmt->bind_param("i", $summary_id);
        $stmt->execute();

        $stmt = $conn->prepare("UPDATE loans SET loan_closed = CURRENT_DATE(), `status` = 'Closed' 
            WHERE id IN (
            SELECT id FROM (
                SELECT l.id, l.amount, ifnull(SUM(c.amount),0) paid FROM loans l
                INNER JOIN collections c ON c.loan_id = l.id and c.head = 'EMI'
                GROUP BY l.id) g
                WHERE amount - paid <= 0)"
        );  
        $stmt->execute();



        header("Location: day_closure.php");
        exit;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error saving loan']);
    }
}
