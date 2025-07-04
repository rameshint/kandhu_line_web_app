<?php
require 'db.php';

$result = $conn->query("SELECT * FROM customers ORDER BY id DESC");
$customers = [];

while ($row = $result->fetch_assoc()) {
    $customers[] = $row;
}

echo json_encode($customers);
