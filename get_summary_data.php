<?php
require 'db.php';

// Fetch all loans + sum of collections on that date
$stmt = $conn->prepare("
    SELECT c.customer_no, c.name, l.loan_date, l.amount, l.interest, l.file_charge,l.tenure,l.loan_type,a.name agent FROM loans l
INNER JOIN customers c ON l.customer_id = c.id
INNER JOIN agents a ON a.id = l.agent_id
WHERE l.flag = 0 
ORDER BY a.name
"); 
$stmt->execute();
$result = $stmt->get_result();

$loans = [];
while ($row = $result->fetch_assoc()) {
    $loans[] = $row;
}

$flag = 0;

// Fetch all loans + sum of collections on that date
$stmt = $conn->prepare("
    SELECT cs.customer_no, a.name agent, cs.name, l.amount,l.loan_type, c.collection_date,c.head, c.amount collection_amount FROM collections c 
INNER JOIN loans l ON l.id = c.loan_id
INNER JOIN customers cs ON cs.id = l.customer_id
INNER JOIN agents a ON a.id = l.agent_id
WHERE c.flag = 0
ORDER BY a.name
"); 
$stmt->execute();
$result = $stmt->get_result();

$collections = [];
while ($row = $result->fetch_assoc()) {
    $collections[] = $row;
}

$net = 0;

$stmt = $conn->prepare("SELECT  DATE_ADD(closure_date,INTERVAL 1 DAY) closure_date, net_amount 
FROM day_summary ORDER BY id DESC LIMIT 1 ");
$stmt->execute();
$result = $stmt->get_result();
$table = '';
$num_rows = mysqli_num_rows($result);
$closure_date = date("Y-m-d");
if($num_rows > 0){
    $row = $result->fetch_assoc();
    $closure_date = $row['closure_date'];
    $table .= '<tr><th >Date</th><td align=right>'.$closure_date.'</td></tr>';
    $table .= '<tr><th style="text-align:right">Opening Balance</th><td align=right>'.formatToIndianCurrency($row['net_amount']).'</td></tr>';
    $net += $row['net_amount'];
}else{
    $table .= '<tr><th align=left>Date</th><td align=right>'.$closure_date.'</td></tr>';
}

$stmt = $conn->prepare("SELECT a.name investor,SUM(i.amount) amount  FROM investments i 
INNER JOIN agents a ON a.id = i.agent_id
WHERE i.flag = 0
GROUP BY a.id");
$stmt->execute();
$result = $stmt->get_result();
$total_investments = 0;
$num_rows = mysqli_num_rows($result);
if($num_rows > 0){
    $table .= '<tr><th colspan=2 align=left>Investment</th></tr>';
    $total = 0;
    while ($row = $result->fetch_assoc()) {
        $total += $row['amount'];
        $table .= '<tr><td>'.$row['investor'].'</td><td align=right>'.formatToIndianCurrency($row['amount']).'</td></tr>';
    }
    $table .= '<tr><td align=right>Sub. Total (+)</td><td align=right>'.formatToIndianCurrency($total).'</td></tr>';    
    $net += $total;
    $total_investments = $total;
    $flag = 1;
}

$stmt = $conn->prepare("SELECT source_name , amount  FROM temp_loans i WHERE flag = 0");
$stmt->execute();
$result = $stmt->get_result();
$num_rows = mysqli_num_rows($result);
$total_temp_loans = 0;
if($num_rows > 0){
    $table .= '<tr><th colspan=2 align=left>Temporary Loans</th></tr>';
    $total = 0;
    while ($row = $result->fetch_assoc()) {
        $total += $row['amount'];
        $table .= '<tr><td>'.$row['source_name'].'</td><td align=right>'.formatToIndianCurrency($row['amount']).'</td></tr>';
    }
    $table .= '<tr><td align=right>Sub. Total (+)</td><td align=right>'.formatToIndianCurrency($total).'</td></tr>';    
    $net += $total;
    $total_temp_loans = $total;
    $flag = 1;
}


$stmt = $conn->prepare("SELECT t.source_name,SUM(i.amount) amount FROM temp_loan_payments i 
INNER JOIN temp_loans t ON t.id = i.temp_loan_id
WHERE i.flag = 0
GROUP BY i.temp_loan_id");
$stmt->execute();
$result = $stmt->get_result();
$num_rows = mysqli_num_rows($result);
$total_temp_loan_payments = 0;
if($num_rows > 0){
    $table .= '<tr><th colspan=2 align=left>Temporary Loan Payments</th></tr>';
    $total = 0;
    while ($row = $result->fetch_assoc()) {
        $total += $row['amount'];
        $table .= '<tr><td>'.$row['source_name'].'</td><td align=right>'.formatToIndianCurrency($row['amount']).'</td></tr>';
    }
    $table .= '<tr><td align=right>Sub. Total (-)</td><td align=right>'.formatToIndianCurrency($total).'</td></tr>';    
    $net -= $total;
    $total_temp_loan_payments = $total;
    $flag = 1;
}



$stmt = $conn->prepare("SELECT a.name agent,SUM(e.amount - ifnull(e.interest,0) - ifnull(e.file_charge,0)) amount  FROM loans e 
INNER JOIN agents a ON a.id = e.agent_id
WHERE e.flag = 0
GROUP BY a.id");
$stmt->execute();
$result = $stmt->get_result();

$num_rows = mysqli_num_rows($result);
if($num_rows > 0){
    $table .= '<tr><th colspan=2 align=left>Loans</th></tr>';
    $total = 0;
    while ($row = $result->fetch_assoc()) {
        $total += $row['amount'];
        $table .= '<tr><td>'.$row['agent'].'</td><td align=right>'.formatToIndianCurrency($row['amount']).'</td></tr>';
    }
    $table .= '<tr><td  align=right>Sub. Total (-)</td><td align=right>'.formatToIndianCurrency($total).'</td></tr>';  
    $net -= $total;
    $total_loans = $total;
    $flag = 1;
}

$stmt = $conn->prepare("SELECT a.name agent,SUM(e.amount) amount  FROM collections e 
INNER JOIN agents a ON a.id = e.agent_id
WHERE e.flag = 0
GROUP BY a.id");
$stmt->execute();
$result = $stmt->get_result();

$num_rows = mysqli_num_rows($result);
if($num_rows > 0){
    $table .= '<tr><th colspan=2 align=left>Collections</th></tr>';
    $total = 0;
    while ($row = $result->fetch_assoc()) {
        $total += $row['amount'];
        $table .= '<tr><td>'.$row['agent'].'</td><td align=right>'.formatToIndianCurrency($row['amount']).'</td></tr>';
    }
    $table .= '<tr><td align=right>Sub. Total (+)</td><td align=right>'.formatToIndianCurrency($total).'</td></tr>';    
    $net += $total;
    $total_collections = $total;
    $flag = 1;
}

$stmt = $conn->prepare("SELECT category ,SUM(e.amount) amount  FROM expenses e 
INNER JOIN agents a ON a.id = e.agent_id
WHERE e.flag = 0
GROUP BY a.id");
$stmt->execute();
$result = $stmt->get_result();

$num_rows = mysqli_num_rows($result);
if($num_rows > 0){
    $table .= '<tr><th colspan=2 align=left>Expenses</th></tr>';
    $total = 0;
    while ($row = $result->fetch_assoc()) {
        $total += $row['amount'];
        $table .= '<tr><td>'.$row['category'].'</td><td align=right>'.formatToIndianCurrency($row['amount']).'</td></tr>';
    }
    $table .= '<tr><td align=right>Sub. Total (-)</td><td align=right>'.formatToIndianCurrency($total).'</td></tr>'; 
    $net -= $total;  
    $total_expenses = $total; 
    $flag = 1;
}


#if ($flag){
    $table .= '<tr><th style="text-align: right;">Closing Balance</th><th style="text-align: right;">'.formatToIndianCurrency($net).'</th></tr>';
    $table .= '<tr><th style="text-align: center;" colspan=2>
        <form action="save_day_summary.php" method="post">
        <input type="hidden" name="closure_date" value="'.$closure_date.'" />
        <input type="hidden" name="total_loans" value="'.$total_loans.'" />
        <input type="hidden" name="total_collections" value="'.$total_collections.'" />
        <input type="hidden" name="total_investments" value="'.$total_investments.'" />
        <input type="hidden" name="total_expenses" value="'.$total_expenses.'" />
        <input type="hidden" name="total_temp_loans" value="'.$total_temp_loans.'" />
        <input type="hidden" name="total_temp_loan_payments" value="'.$total_temp_loan_payments.'" />
        <input type="hidden" name="net_amount" value="'.$net.'" />';
        if( $closure_date <= date("Y-m-d")){
            $table .='<input type="submit" class="btn btn-primary" value="Day Closure" />';
        }
       $table .='
        </form>
    </th></tr>';
#}


echo json_encode(['loans' => $loans, 'collections' => $collections, 'summary' => $table]);
