<?php
include_once 'db.php';
$sql = "SELECT * FROM notifications WHERE created_on > NOW() - INTERVAL 3 DAY ORDER BY created_on DESC LIMIT 10";
$result = $conn->query($sql);
$notifications = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
}
echo json_encode($notifications);
