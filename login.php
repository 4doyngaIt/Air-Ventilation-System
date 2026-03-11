<?php
session_start();
include "config/db.php";

$error = "";

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT user_id, username, password FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows == 1){
        $user = $result->fetch_assoc();

        if($password === $user['password']){
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];

            header("Location: app/user/dashboard.php");
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "User not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login - Air Ventilation</title>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Arial, Helvetica, sans-serif;
}

/* Background Image */
body{
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    background:url("1.2") no-repeat center center/cover;
    position:relative;
}

/* Dark overlay */
body::before{
    content:"";
    position:absolute;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.5);
}

/* Login Card */
.login-box{
    position:relative;
    background:#fff;
    padding:40px;
    width:340px;
    border-radius:12px;
    box-shadow:0 10px 30px rgba(0,0,0,0.3);
    text-align:center;
}

/* Title */
.login-box h2{
    margin-bottom:20px;
    color:#333;
}

/* Inputs */
.login-box input{
    width:100%;
    padding:12px;
    margin-bottom:15px;
    border-radius:6px;
    border:1px solid #ccc;
    transition:0.3s;
}

.login-box input:focus{
    border-color:#007bff;
    outline:none;
    box-shadow:0 0 5px rgba(0,123,255,0.3);
}

/* Password container */
.password-box{
    position:relative;
}

.password-box input{
    padding-right:70px;
}

/* Show button */
.show-btn{
    position:absolute;
    right:10px;
    top:40%;
    transform:translateY(-50%);
    border:none;
    background:#007bff;
    color:white;
    padding:5px 10px;
    font-size:12px;
    border-radius:4px;
    cursor:pointer;
}

.show-btn:hover{
    background:#0056b3;
}

/* Button */
.login-box button[type="submit"]{
    padding:12px;
    width:100%;
    background:#007bff;
    color:#fff;
    border:none;
    border-radius:6px;
    cursor:pointer;
}

.login-box button[type="submit"]:hover{
    background:#0056b3;
}

/* Error message */
.error{
    color:red;
    margin-bottom:10px;
    font-size:14px;
}

</style>

</head>

<body>

<div class="login-box">
    <h2>Air Ventilation System</h2>

    <?php if($error) echo "<div class='error'>$error</div>"; ?>

    <form method="POST">

        <input type="email" name="email" placeholder="Enter Email" required>

        <div class="password-box">
            <input type="password" id="password" name="password" placeholder="Enter Password" required>
            <button type="button" class="show-btn" onclick="togglePassword()">Show</button>
        </div>

        <button type="submit">Login</button>

    </form>

</div>

<script>
function togglePassword() {
    var passwordField = document.getElementById("password");

    if (passwordField.type === "password") {
        passwordField.type = "text";
    } else {
        passwordField.type = "password";
    }
}
</script>

</body>
</html>