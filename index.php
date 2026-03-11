<?php
session_start();

// Redirect based on login
if(isset($_SESSION['user_id'])){
    // Dashboard path inside app/user
    header("Location: app/user/dashboard.php");
    exit();
} else {
    header("Location: login.php");
    exit();
}
?>