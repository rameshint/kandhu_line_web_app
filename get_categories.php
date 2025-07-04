<?php
include 'db.php';
$result = mysqli_query($conn, "SELECT id, name FROM expense_categories ORDER BY name");
$categories = [];
while($row = mysqli_fetch_assoc($result)) {
  $categories[] = $row;
}
echo json_encode($categories);
