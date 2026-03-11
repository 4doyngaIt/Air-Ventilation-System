<?php
session_start();
include "../config/db.php";

$email = $_POST['email'];
$password = $_POST['password'];

$sql = "SELECT * FROM users WHERE email='$email' AND password='$password'";
$result = $conn->query($sql);

if($result->num_rows == 1){

    $row = $result->fetch_assoc();

    $_SESSION['user_id'] = $row['user_id'];
    $_SESSION['username'] = $row['username'];
    $_SESSION['role'] = $row['role'];

    header("Location: ../dashboard.php");

}else{

    echo "Invalid email or password";

}

?>