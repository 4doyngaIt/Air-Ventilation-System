<?php

session_start();
include "config/db.php";

if(!isset($_SESSION['user_id'])){
    header("Location: login/login.php");
    exit();
}

?>

<!DOCTYPE html>
<html>

<head>
<title>User Dashboard</title>
<link rel="stylesheet" href="style.css">
</head>

<body>

<div class="container">

<h2>Welcome <?php echo $_SESSION['username']; ?></h2>

<p>Role: <?php echo $_SESSION['role']; ?></p>

<a href="logout.php">Logout</a>

<hr>

<h3>Sensor List</h3>

<table border="1">

<tr>
<th>ID</th>
<th>Name</th>
<th>Type</th>
<th>Location</th>
<th>Status</th>
</tr>

<?php

$sql = "SELECT * FROM sensors";
$result = $conn->query($sql);

while($row = $result->fetch_assoc()){
?>

<tr>
<td><?php echo $row['sensor_id']; ?></td>
<td><?php echo $row['sensor_name']; ?></td>
<td><?php echo $row['sensor_type']; ?></td>
<td><?php echo $row['location']; ?></td>
<td><?php echo $row['status']; ?></td>
</tr>

<?php } ?>

</table>

</div>

</body>
</html>