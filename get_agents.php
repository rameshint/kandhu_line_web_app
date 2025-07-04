<?php
require 'db.php';
$result = $conn->query("SELECT * FROM agents ORDER BY id DESC");
$agents = [];
while ($row = $result->fetch_assoc()) {
    $agents[] = $row;
}
echo json_encode($agents);
