<?php
session_start();
include "config/db.php";

if(isset($_SESSION['user_id'])){
    header("Location: dashboard.php");
    exit();
}

if($_SERVER["REQUEST_METHOD"] == "POST"){

$email = $_POST['email'];
$password = $_POST['password'];

$sql = "SELECT * FROM users WHERE email='$email' AND password='$password'";
$result = $conn->query($sql);

if($result->num_rows == 1){

$user = $result->fetch_assoc();

$_SESSION['user_id'] = $user['user_id'];
$_SESSION['username'] = $user['username'];
$_SESSION['role'] = $user['role'];

header("Location: dashboard.php");
exit();

}else{
$error = "Invalid Email or Password";
}

}
?>

<!DOCTYPE html>
<html>
<head>
<title>Indoor Air Ventilation System</title>
<link rel="stylesheet" href="asset/style.css">
</head>

<body>

<div class="login-box">

<h2>Indoor Air Ventilation System</h2>

<?php if(isset($error)){ ?>
<p style="color:red;"><?php echo $error; ?></p>
<?php } ?>

<form method="POST">

<input type="email" name="email" placeholder="Enter Email" required>

<input type="password" name="password" placeholder="Enter Password" required>

<button type="submit">Login</button>

</form>

</div>

</body>
</html>