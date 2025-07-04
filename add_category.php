<?php
include 'db.php';
$name = trim($_POST['name']);

if ($name != '') {
  $stmt = $conn->prepare("INSERT INTO expense_categories (name) VALUES (?)");
  $stmt->bind_param("s", $name);
  if ($stmt->execute()) {
    echo 'success';
  } else {
    echo 'error';
  }
} else {
  echo 'error';
}
