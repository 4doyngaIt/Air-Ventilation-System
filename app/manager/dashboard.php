<?php
session_start();
include "../config/db.php"; // adjust path to your config

// Check if user is logged in and role is manager
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'manager'){
    header("Location: ../login.php");
    exit();
}

// Fetch all sensors with latest readings
$sensors_sql = "
SELECT s.sensor_id, s.sensor_name, s.sensor_type, s.location, s.status,
       ar.temperature, ar.humidity, ar.co2_level, ar.air_quality_index, ar.raining, ar.recorded_at
FROM sensors s
LEFT JOIN air_readings ar ON s.sensor_id = ar.sensor_id
WHERE ar.recorded_at = (SELECT MAX(recorded_at) FROM air_readings WHERE sensor_id = s.sensor_id)
";
$sensors_result = $conn->query($sensors_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manager Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css"/>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin:0; padding:0; background:#f4f4f4;
        }
        header { background:#2c3e50; color:#fff; padding:15px; text-align:center; }
        .container { padding:20px; }
        table { width:100%; border-collapse: collapse; margin-bottom:20px; }
        th, td { border:1px solid #ccc; padding:10px; text-align:center; }
        th { background:#34495e; color:#fff; }
        .status-active { color: green; font-weight:bold; }
        .status-inactive { color: red; font-weight:bold; }
        .map-container { height:400px; margin-bottom:20px; border:1px solid #ccc; }
        button { padding:5px 10px; margin:2px; cursor:pointer; }
        .btn-edit { background:#2980b9; color:#fff; border:none; }
        .btn-delete { background:#c0392b; color:#fff; border:none; }
        .btn-toggle { background:#27ae60; color:#fff; border:none; }
    </style>
</head>
<body>
<header>
    <h1>Manager Dashboard</h1>
    <p>Welcome, <?php echo $_SESSION['username']; ?> | <a href="../logout.php" style="color:#fff;">Logout</a></p>
</header>

<div class="container">
    <h2>Sensor Map</h2>
    <div id="map" class="map-container"></div>

    <h2>Manage Sensors</h2>
    <table>
        <tr>
            <th>Sensor ID</th>
            <th>Name</th>
            <th>Type</th>
            <th>Location</th>
            <th>Status</th>
            <th>Temperature</th>
            <th>Humidity</th>
            <th>CO2</th>
            <th>AQI</th>
            <th>Raining</th>
            <th>Recorded At</th>
            <th>Actions</th>
        </tr>
        <?php while($row = $sensors_result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['sensor_id']; ?></td>
                <td><?php echo $row['sensor_name']; ?></td>
                <td><?php echo $row['sensor_type']; ?></td>
                <td><?php echo $row['location']; ?></td>
                <td class="status-<?php echo strtolower($row['status']); ?>"><?php echo $row['status']; ?></td>
                <td><?php echo $row['temperature']; ?></td>
                <td><?php echo $row['humidity']; ?></td>
                <td><?php echo $row['co2_level']; ?></td>
                <td><?php echo $row['air_quality_index']; ?></td>
                <td><?php echo $row['raining']; ?></td>
                <td><?php echo $row['recorded_at']; ?></td>
                <td>
                    <button class="btn-edit" onclick="editSensor(<?php echo $row['sensor_id']; ?>)">Edit</button>
                    <button class="btn-toggle" onclick="toggleSensor(<?php echo $row['sensor_id']; ?>)">Toggle</button>
                    <button class="btn-delete" onclick="deleteSensor(<?php echo $row['sensor_id']; ?>)">Delete</button>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"></script>
<script>
// Initialize map
var map = L.map('map').setView([14.5995, 120.9842], 12); // Default: Manila

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);

// Sensor markers
<?php
$sensors_query = $conn->query("SELECT sensor_id, sensor_name, location FROM sensors");
while($sensor = $sensors_query->fetch_assoc()):
    // Optional: parse location into lat/lng if stored as "lat,lng" or here just random around Manila for demo
    $lat = 14.5995 + rand(-50,50)/1000;
    $lng = 120.9842 + rand(-50,50)/1000;
?>
L.marker([<?php echo $lat; ?>, <?php echo $lng; ?>]).addTo(map)
    .bindPopup("<b><?php echo $sensor['sensor_name']; ?></b><br>ID: <?php echo $sensor['sensor_id']; ?>");
<?php endwhile; ?>

// Dummy JS functions for managing sensors
function editSensor(id){ alert("Edit sensor ID: "+id); }
function toggleSensor(id){ alert("Toggle status sensor ID: "+id); }
function deleteSensor(id){ 
    if(confirm("Delete sensor ID: "+id+"?")) alert("Deleted sensor ID: "+id); 
}
</script>
</body>
</html>