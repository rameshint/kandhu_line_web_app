<?php
require 'db.php';
$id = $_GET['id'];
$result = $conn->query("SELECT * FROM customers WHERE id = $id");
echo json_encode($result->fetch_assoc());
