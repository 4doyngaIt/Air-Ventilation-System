<?php
session_start();

// Include DB from config folder
include "../../config/db.php"; // relative path from app/user to config

// Redirect to login if not logged in
if(!isset($_SESSION['user_id'])){
    header("Location: ../../login.php"); // relative path
    exit();
}

// Fetch user info
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, role FROM users WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Latest sensor readings (using join with index recommended)
$sensor_sql = "
    SELECT s.sensor_name, s.sensor_type, s.location, s.status,
           r.temperature, r.humidity, r.co2_level, r.air_quality_index, r.raining, r.recorded_at
    FROM sensors s
    LEFT JOIN (
        SELECT sensor_id, temperature, humidity, co2_level, air_quality_index, raining, recorded_at
        FROM air_readings
        WHERE (sensor_id, recorded_at) IN (
            SELECT sensor_id, MAX(recorded_at) FROM air_readings GROUP BY sensor_id
        )
    ) r ON s.sensor_id = r.sensor_id
    ORDER BY s.sensor_type
";
$sensor_result = $conn->query($sensor_sql);

// Ventilation systems
$vent_sql = "SELECT * FROM ventilation_systems ORDER BY location";
$vent_result = $conn->query($vent_sql);

// Active alerts
$alert_sql = "SELECT a.*, s.sensor_name FROM alerts a JOIN sensors s ON a.sensor_id = s.sensor_id WHERE a.status='active' ORDER BY alert_time DESC";
$alert_result = $conn->query($alert_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Dashboard - Air Ventilation</title>
<style>
body{font-family:Arial; background:#f4f6f9; margin:0; padding:0;}
.container{width:90%; margin:auto; padding:20px;}
h2,h3{margin-top:0;}
table{width:100%; border-collapse:collapse; margin-bottom:30px;}
th,td{border:1px solid #ccc; padding:8px; text-align:center;}
th{background:#eee;}
.alert{color:red; font-weight:bold;}
a{text-decoration:none; color:#007bff;}
</style>
</head>
<body>
<div class="container">
<h2>Welcome, <?php echo htmlspecialchars($user['username']); ?> (<?php echo $user['role']; ?>)</h2>

<h3>Latest Sensor Readings</h3>
<table>
<tr>
<th>Sensor</th><th>Type</th><th>Location</th><th>Status</th>
<th>Temperature (°C)</th><th>Humidity (%)</th><th>CO2 (ppm)</th><th>AQI</th><th>Raining</th><th>Recorded At</th>
</tr>
<?php while($row = $sensor_result->fetch_assoc()): ?>
<tr>
<td><?php echo $row['sensor_name']; ?></td>
<td><?php echo $row['sensor_type']; ?></td>
<td><?php echo $row['location']; ?></td>
<td><?php echo $row['status']; ?></td>
<td><?php echo $row['temperature'] ?? '-'; ?></td>
<td><?php echo $row['humidity'] ?? '-'; ?></td>
<td><?php echo $row['co2_level'] ?? '-'; ?></td>
<td><?php echo $row['air_quality_index'] ?? '-'; ?></td>
<td><?php echo $row['raining'] ?? '-'; ?></td>
<td><?php echo $row['recorded_at'] ?? '-'; ?></td>
</tr>
<?php endwhile; ?>
</table>

<h3>Ventilation Systems</h3>
<table>
<tr><th>Device</th><th>Location</th><th>Status</th><th>Mode</th></tr>
<?php while($vent = $vent_result->fetch_assoc()): ?>
<tr>
<td><?php echo $vent['device_name']; ?></td>
<td><?php echo $vent['location']; ?></td>
<td><?php echo $vent['status']; ?></td>
<td><?php echo $vent['mode']; ?></td>
</tr>
<?php endwhile; ?>
</table>

<h3>Active Alerts</h3>
<table>
<tr><th>Sensor</th><th>Type</th><th>Message</th><th>Time</th></tr>
<?php while($alert = $alert_result->fetch_assoc()): ?>
<tr class="alert">
<td><?php echo $alert['sensor_name']; ?></td>
<td><?php echo $alert['alert_type']; ?></td>
<td><?php echo $alert['message']; ?></td>
<td><?php echo $alert['alert_time']; ?></td>
</tr>
<?php endwhile; ?>
</table>

<a href="logout.php">Logout</a>
</div>
</body>
</html>