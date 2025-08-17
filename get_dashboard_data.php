<?php
include_once 'db.php';
ini_set("display_errors", 0);
$sql = "SELECT * FROM (
            SELECT 'closing_balance' head, net_amount FROM day_summary where line = '" . $_SESSION['line'] . "' ORDER BY id DESC LIMIT 1
        ) a
        UNION all
        SELECT 'total_investments' head, SUM(amount) value FROM investments l where l.line = '" . $_SESSION['line'] . "'
        UNION ALL
        SELECT 'loan_outstanding' head, SUM(l.amount - IFNULL(c.collected,0)) value FROM loans l 
        LEFT JOIN (select loan_id, sum(amount) collected from collections WHERE flag = 1 AND head= 'EMI' GROUP BY loan_id) c ON c.loan_id = l.id
        WHERE l.`status` = 'Open' and l.loan_type = '" . $_SESSION['line'] . "'
        UNION ALL
        SELECT 'temporary_loan_outstanding' head, SUM(t.amount - t.repaid_amount)  FROM temp_loans t 
        WHERE t.flag = 1 and t.line = '" . $_SESSION['line'] . "'";

$result = mysqli_query($conn, $sql);
$tiles = [];
while ($row = mysqli_fetch_assoc($result)) {
    $tiles[] = $row;
}

$output = ['tiles' => $tiles];

$loan_collections = [];
$date = new DateTime();
for ($i = 6; $i >= 1; $i--) {
    $loan_collections[$date->format('Y-m')] = [];
    $date->modify('-1 months');
}

$sql = "SELECT date_format(l.loan_date, '%Y-%m') mon, SUM(amount) amt FROM loans l 
        WHERE loan_date >= ADDDATE(CURRENT_DATE,INTERVAL -6 month) and loan_type = '" . $_SESSION['line'] . "'
        GROUP BY date_format(l.loan_date, '%Y-%m')";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    if (array_key_exists($row['mon'], $loan_collections))
        $loan_collections[$row['mon']]['loans'] = $row['amt'];
}

$sql = "SELECT DATE_FORMAT(l.collection_date, '%Y-%m') mon, SUM(l.amount) amt FROM collections l 
        inner join loans c on c.id = l.loan_id and c.loan_type = '" . $_SESSION['line'] . "'
        WHERE l.collection_date >= ADDDATE(CURRENT_DATE,INTERVAL -6 month)  and l.collection_date <> '2025-06-30'
        GROUP BY date_format(l.collection_date, '%Y-%m')";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    if (array_key_exists($row['mon'], $loan_collections))
        $loan_collections[$row['mon']]['collections'] = $row['amt'];
}


$loan_col_months = [];
$loans = [];
$collections = [];
foreach ($loan_collections as $mon => $detail) {
    $loan_col_months[] = $mon . '-01';
    $loans[] = $detail['loans'] !== null ? $detail['loans'] : 0;
    $collections[] = $detail['collections'] !== null ? $detail['collections'] : 0;
}

$output['loan_collections'] =  ['months' => $loan_col_months, 'loans' => $loans, 'collections' => $collections];

$loan_collections = [];
$date = new DateTime();
$date->modify('-7 days');
for ($i = 6; $i >= 0; $i--) {
    $loan_collections[$date->format('Y-m-d')] = [];
    $date->modify('+1 days');
}

$sql = "SELECT l.loan_date dat, SUM(amount) amt FROM loans l 
WHERE loan_date >= ADDDATE(CURRENT_DATE,INTERVAL -7 DAY) -- AND loan_date <= current_date
AND flag = 1 and loan_type = '" . $_SESSION['line'] . "'
GROUP BY l.loan_date";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    if (array_key_exists($row['dat'], $loan_collections))
        $loan_collections[$row['dat']]['loans'] = $row['amt'];
}

$sql = "SELECT l.collection_date dat, SUM(l.amount) amt FROM collections l 
inner join loans c on c.id = l.loan_id and c.loan_type = '" . $_SESSION['line'] . "'
WHERE l.collection_date >= ADDDATE(CURRENT_DATE,INTERVAL -7 DAY)  and l.collection_date <> '2025-06-30'
AND l.flag = 1
GROUP BY l.collection_date";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    if (array_key_exists($row['dat'], $loan_collections))
        $loan_collections[$row['dat']]['collections'] = $row['amt'];
}
$loan_col_days = [];
$loans = [];
$collections = [];
foreach ($loan_collections as $date => $detail) {
    $dateObj = new DateTime($date);
    $loan_col_days[] = $dateObj->format('D');
    $loans[] = $detail['loans'] !== null ? $detail['loans'] : 0;
    $collections[] = $detail['collections'] !== null ? $detail['collections'] : 0;
}

$output['loan_collections_days'] =  ['dates' => $loan_col_days, 'loans' => $loans, 'collections' => $collections];


$sql = "SELECT c.collection_date,SUM(c.amount)  amt
FROM collections c
inner join loans l on l.id = c.loan_id and l.loan_type = '" . $_SESSION['line'] . "'
WHERE c.flag = 1 AND c.collection_date >= date_sub(CURRENT_DATE,INTERVAL 35 DAY)  and c.collection_date <> '2025-06-30'
GROUP BY c.collection_date";
$result = mysqli_query($conn, $sql);
$collections = [];
while ($row = mysqli_fetch_assoc($result)) {
    $collections[$row['collection_date']] = $row['amt'];
}
$date = new DateTime();
$date->modify('-1 days');
$i = 1;
$heat_map_collections = [];
$week = 1;
$day_dates = [];
while ($i <= 35) {
    $day_name = $date->format('D');
    $day_dates['Week ' . $week][$day_name] = $date->format('Y-m-d');
    $heat_map_collections['Week ' . $week][$day_name] = intval($collections[$date->format('Y-m-d')]) == 0 ? 0 : intval($collections[$date->format('Y-m-d')]);

    if ($i % 7 == 0)
        $week++;

    $date->modify('-1 days');
    $i++;
}
$heat_map = [];
foreach ($heat_map_collections as $week => $days) {
    $day_col = [];
    foreach ($days as $day => $amount) {
        $day_col[] = [
            'x' => $day,
            'y' => $amount,
            'date' => formatDate($day_dates[$week][$day]) // Use mapped date
        ];
    }
    $day_col = array_reverse($day_col);
    $heat_map[] = [
        'name' => $week,
        'data' => $day_col,
    ];
}

$output['heat_map'] =  $heat_map;

$sql = "WITH cte AS (
SELECT c.customer_no,c.name, l.loan_type, l.loan_date,l.expiry_date,l.amount,l.tenure, l.amount / l.tenure emi, 
case l.loan_type when 'Daily' then TIMESTAMPDIFF(DAY, l.loan_date, CURRENT_DATE) 
when 'Weekly' then TIMESTAMPDIFF(WEEK, l.loan_date, CURRENT_DATE)
when 'Monthly' then TIMESTAMPDIFF(MONTH, l.loan_date, CURRENT_DATE)
END diff,
IFNULL(a.collected,0) collected
FROM loans l
INNER JOIN customers c ON c.id = l.customer_id
LEFT JOIN (select loan_id, sum(amount) collected from collections WHERE flag = 1 GROUP BY loan_id) a ON a.loan_id = l.id
WHERE l.`status` = 'Open' AND l.loan_type = '" . $_SESSION['line'] . "'),
tab1 AS (SELECT * , if(tenure < diff, tenure, diff) * emi to_be_paid , if(loan_date>expiry_date, 'Overdue','LatePay') status FROM cte c)
SELECT * , FLOOR((to_be_paid - collected) / emi) pending_emi FROM tab1
WHERE collected < to_be_paid
ORDER BY FLOOR((to_be_paid - collected) / emi) desc";
$result = mysqli_query($conn, $sql);
$loans = [];
while ($row = mysqli_fetch_assoc($result)) {
    $loans[] = $row;
}
$output['loans'] = $loans;
echo json_encode($output);
