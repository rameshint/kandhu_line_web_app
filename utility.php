<?php
include_once'db.php';
function formatToIndianCurrency($amount) {
    $amount = number_format($amount, 2, '.', '');
    $parts = explode('.', $amount);
    $integerPart = $parts[0];
    $decimalPart = $parts[1];

    // Format integer part in Indian style
    $length = strlen($integerPart);
    if ($length > 3) {
        $lastThree = substr($integerPart, -3);
        $restUnits = substr($integerPart, 0, $length - 3);
        $restUnits = preg_replace("/\B(?=(\d{2})+(?!\d))/", ",", $restUnits);
        $formatted = $restUnits . "," . $lastThree;
    } else {
        $formatted = $integerPart;
    }

    return  $formatted . "." . $decimalPart;
}

function formatDate($dateString) {
    if (!$dateString) {
        return '';
    }
    return (new DateTime($dateString))->format('d/m/Y');
}


function getBusinessDate(){
    global $conn;
    $sql = "SELECT date_add(MAX(closure_date),interval 1 DAY) running_date FROM day_summary where line = '".$_SESSION['line']."'";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    $loans = [];
    $row = $result->fetch_assoc();
    return $row['running_date'];
}
?>