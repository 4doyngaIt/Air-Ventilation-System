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
        // NOTE: For production, use password_hash & password_verify
        if($password === $user['password']){
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            header("Location: dashboard.php");
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
        body { font-family: Arial; background: #f4f6f9; display:flex; justify-content:center; align-items:center; height:100vh; }
        .login-box { background:#fff; padding:20px 30px; border-radius:8px; box-shadow:0 0 10px rgba(0,0,0,0.1); width:300px; }
        input { display:block; width:100%; margin-bottom:15px; padding:10px; }
        button { padding:10px; width:100%; background:#007bff; color:#fff; border:none; cursor:pointer; }
        .error { color:red; margin-bottom:10px; }
    </style>
</head>
<body>
<div class="login-box">
    <h2>Login</h2>
    <?php if($error) echo "<div class='error'>$error</div>"; ?>
    <form method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
</div>
</body>
</html>