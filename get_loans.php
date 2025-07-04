<?php
require 'db.php';
$customer_id = $_GET['customer_id'];
$res = $conn->query("SELECT l.* , ifnull(c.bills,0) bills,l.amount - collected balance, case 
	when loan_type = 'Daily' then CONCAT(TIMESTAMPDIFF(DAY, l.loan_date, IFNULL(loan_closed,CURRENT_DATE))  , ' Days')
	when loan_type = 'Weekly' then concat(TIMESTAMPDIFF(WEEK, l.loan_date, IFNULL(loan_closed,CURRENT_DATE)), ' Weeks')
	when loan_type = 'Monthly' then concat(TIMESTAMPDIFF(MONTH, l.loan_date, IFNULL(loan_closed,CURRENT_DATE)), ' Months')
END days FROM loans l 
LEFT JOIN (SELECT loan_id,COUNT(*) bills,SUM(amount) collected FROM collections c where head = 'EMI' GROUP BY loan_id) c ON c.loan_id = l.id
WHERE customer_id = $customer_id ORDER BY id DESC");
$loans = [];
while ($row = $res->fetch_assoc()) {
    $loans[] = $row;
}
echo json_encode($loans);
