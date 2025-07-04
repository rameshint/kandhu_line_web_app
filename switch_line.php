<?php
include'db.php';
if($_SESSION['role'] == 'admin'){
    $_SESSION['line'] = $_GET['line'];
}
?>