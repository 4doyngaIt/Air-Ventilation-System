<?php
session_start();

// If logged in → dashboard, else → login
if(isset($_SESSION['user_id'])){
    header("Location: dashboard.php");
    exit();
} else {
    header("Location: login.php");
    exit();
}
?>