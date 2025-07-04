<?php
require 'db.php';
$id = $_POST['id'];

$stmt = $conn->prepare("DELETE FROM temp_loans WHERE id = ? and flag = 0");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => "Delete failed."]);
}
?>
