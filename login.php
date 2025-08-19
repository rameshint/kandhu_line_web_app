<?php
session_start();

require 'db.php'; // include your DB connection file



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Query the user by username
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Check password and status
        if (md5(md5($password)) == $user['password']) {
            if ($user['status'] === 'active') {
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['line'] = $user['line'];
                if ($user['role'] == 'agent') {
                    $_SESSION['home_page'] = 'collections.php';
                    echo "<script>sessionStorage.setItem('notificationModalShown', 'false');</script>";
                    header("Location: collections.php");
                } else {
                    $_SESSION['home_page'] = 'home.php';
                    echo "<script>sessionStorage.setItem('notificationModalShown', 'false');</script>";
                    header("Location: home.php");
                }

                exit();
            } else {
                // User is inactive
                header("Location: index.php?error=inactive");
                exit();
            }
        } else {
            // Incorrect password
            header("Location: index.php?error=invalid");
            exit();
        }
    } else {
        // User not found
        header("Location: index.php?error=notfound");
        exit();
    }
}
