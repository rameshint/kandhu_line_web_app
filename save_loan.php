<?php
require 'db.php';
$id = $_POST['id'] ?? null;

if ($id) {
    $stmt = $conn->prepare("UPDATE loans SET loan_date=?, amount=?, interest=?, file_charge=?, loan_type=?, tenure=?, agent_id=?, expiry_date=? WHERE id=?");
    $stmt->bind_param("sdddssisi", $_POST['loan_date'], $_POST['amount'], $_POST['interest'], $_POST['file_charge'], $_POST['loan_type'], $_POST['tenure'], $_POST['agent_id'], $_POST['expiry_date'], $id);
} else {

    // Check if customer is a bad customer
    $customer_id = $_POST['customer_id'];
    $sql = "SELECT customer_no, name, sum(overdue_count) overdue_count FROM (
            SELECT a.customer_no, a.name, DATEDIFF(ifnull(l.loan_closed,CURDATE()), l.expiry_date) > (l.tenure/100)*10 overdue_count, 
            ROW_NUMBER() over(PARTITION BY l.customer_id ORDER BY l.id DESC) rn
            FROM loans l
            INNER JOIN customers a ON a.id = l.customer_id
            LEFT JOIN (select loan_id, sum(amount) amount from collections WHERE flag = 1 and head = 'EMI' GROUP BY loan_id) c  ON l.id = c.loan_id 
            WHERE l.loan_type = ? AND l.customer_id = ?
            ) f WHERE f.rn <=3";
    $stmt_check = $conn->prepare($sql);

    $stmt_check->bind_param("si", $_SESSION['line'], $customer_id);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    $row = $result->fetch_assoc();

    if ($row['overdue_count'] > 0) {
        // Insert notification for admin 
        // Add Customer no, name, and overdue count and current loan details
        $note = "Loan given to <span class='badge bg-danger'>bad customer</span> <br />
        Customer No: {$row['customer_no']}<br /> 
        Customer Name : {$row['name']} <br />
        Overdue Count: {$row['overdue_count']}<br />
        Current Loan Amount: {$_POST['amount']}";
        $stmt_notify = $conn->prepare("INSERT INTO notifications (message) VALUES (?)");
        $stmt_notify->bind_param("s", $note);
        $stmt_notify->execute();
    }
    $stmt_check->close();

    // Insert new loan
    $stmt = $conn->prepare("INSERT INTO loans (customer_id, loan_date, amount, interest, file_charge, loan_type, tenure, agent_id, user_id,expiry_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isdddssiis", $_POST['customer_id'], $_POST['loan_date'], $_POST['amount'], $_POST['interest'], $_POST['file_charge'], $_POST['loan_type'], $_POST['tenure'], $_POST['agent_id'], $_SESSION['user_id'], $_POST['expiry_date']);
}

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Error saving loan']);
}
