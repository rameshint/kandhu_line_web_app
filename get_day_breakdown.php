<?php
require 'db.php';
$summary_id = $_GET['id'];

ini_set("display_errors",0);

$res = $conn->query("SELECT a.name investor, i.investment_date, i.amount FROM investments i
INNER JOIN agents a ON a.id = i.agent_id
WHERE i.summary_id = $summary_id");
$rows = "";
while ($r = $res->fetch_assoc()) {
    $rows .= "<tr><td>" . implode("</td><td>", array_map('htmlspecialchars', $r)) . "</td></tr>";
}
if($rows != ''){
    echo "<h5>Investments</h5>
        <table class='table table-sm table-bordered' width=auto>
            <thead>
                <tr>
                    <th>Investor Name</th>
                    <th>Investment Date</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody> $rows </tbody>
        </table>";
}

$res = $conn->query("SELECT i.borrow_date,i.source_name,i.amount,i.remarks FROM temp_loans i
WHERE i.summary_id = $summary_id");
$rows = "";
while ($r = $res->fetch_assoc()) {
    $rows .= "<tr><td>" . implode("</td><td>", array_map('htmlspecialchars', $r)) . "</td></tr>";
}
if($rows != ''){
    echo "<h5>Temporary Loans</h5>
        <table class='table table-sm table-bordered' width=auto>
            <thead>
                <tr>
                    <th>Borrowed Date</th>
                    <th>Source Name</th>
                    <th>Amount</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody> $rows </tbody>
        </table>";
}

$res = $conn->query("SELECT p.repaid_date, i.source_name,p.head,p.amount FROM temp_loans i
INNER JOIN temp_loan_payments p ON p.temp_loan_id = i.id
WHERE p.summary_id = $summary_id");
$rows = "";
while ($r = $res->fetch_assoc()) {
    $rows .= "<tr><td>" . implode("</td><td>", array_map('htmlspecialchars', $r)) . "</td></tr>";
}
if($rows != ''){
    echo "<h5>Temporary Loan Payments</h5>
        <table class='table table-sm table-bordered' width=auto>
            <thead>
                <tr>
                    <th>Paid Date</th>
                    <th>Source Name</th>
                    <th>Head</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody> $rows </tbody>
        </table>";
}

$res = $conn->query("SELECT c.customer_no, c.name customer_name , l.loan_date, l.amount,l.interest,l.file_charge,l.loan_type,l.tenure,a.name agent FROM loans l
INNER JOIN agents a ON a.id = l.agent_id
INNER JOIN customers c ON c.id = l.customer_id
WHERE l.summary_id = $summary_id");
$rows = "";
while ($r = $res->fetch_assoc()) {
    $rows .= "<tr><td>" . implode("</td><td>", array_map('htmlspecialchars', $r)) . "</td></tr>";
}
if($rows != ''){
    echo "<h5>Loans</h5>
        <table class='table table-sm table-bordered'>
            <thead>
                <tr>
                    <th>Customer No</th>
                    <th>Customer Name</th>
                    <th>Loan Date</th>
                    <th>Amount</th>
                    <th>Interest</th>
                    <th>File Charge</th>
                    <th>Type of Loan</th>
                    <th>Tenure</th>
                    <th>Agent</th>
                </tr>
            </thead>
            <tbody> $rows </tbody>
        </table>";
}


$res = $conn->query("SELECT cs.customer_no, cs.name, l.amount loan_amount,c.collection_date,c.head, c.amount, a.name agent FROM collections c 
INNER JOIN loans l ON l.id  = c.loan_id
INNER JOIN customers cs ON cs.id = l.customer_id
left JOIN agents a ON a.id = c.agent_id
WHERE c.summary_id = $summary_id");
$rows = "";
while ($r = $res->fetch_assoc()) {
    $rows .= "<tr><td>" . implode("</td><td>", array_map('htmlspecialchars', $r)) . "</td></tr>";
}
if($rows != ''){
    echo "<h5>Collections</h5>
        <table class='table table-sm table-bordered'>
            <thead>
                <tr>
                    <th>Customer No</th>
                    <th>Customer Name</th>
                    <th>Loan Amount</th>
                    <th>Collection Date</th>
                    <th>Head</th>
                    <th>Collection Amount</th>
                    <th>Agent</th>
                </tr>
            </thead>
            <tbody> $rows </tbody>
        </table>";
}

$res = $conn->query("SELECT a.name investor,i.category, i.expense_date, i.amount FROM expenses i
INNER JOIN agents a ON a.id = i.agent_id
WHERE i.summary_id = $summary_id");
$rows = "";
while ($r = $res->fetch_assoc()) {
    $rows .= "<tr><td>" . implode("</td><td>", array_map('htmlspecialchars', $r)) . "</td></tr>";
}
if($rows != ''){
    echo "<h5>Expenses</h5>
        <table class='table table-sm table-bordered'>
            <thead>
                <tr>
                    <th>Agent</th>
                    <th>Category</th>
                    <th>Expense Date</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody> $rows </tbody>
        </table>";
}
 
?>
