<?php
session_start();
include "../../config/db.php"; // adjust path to your db.php

// ── Auth + role guard ───────────────────────────────────────────────
if(!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../../login.php");
    exit();
}

// ── Fetch admin info ────────────────────────────────────────────────
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username FROM users WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();

// ── Summary counts ─────────────────────────────────────────────────
$total_users    = $conn->query("SELECT COUNT(*) c FROM users")->fetch_assoc()['c'];
$total_sensors  = $conn->query("SELECT COUNT(*) c FROM sensors")->fetch_assoc()['c'];
$active_sensors = $conn->query("SELECT COUNT(*) c FROM sensors WHERE status='active'")->fetch_assoc()['c'];
$total_vents    = $conn->query("SELECT COUNT(*) c FROM ventilation_systems")->fetch_assoc()['c'];
$vents_on       = $conn->query("SELECT COUNT(*) c FROM ventilation_systems WHERE status='ON'")->fetch_assoc()['c'];
$active_alerts  = $conn->query("SELECT COUNT(*) c FROM alerts WHERE status='active'")->fetch_assoc()['c'];

// ── Latest readings ───────────────────────────────────────────────
$latest_readings = $conn->query("
    SELECT s.sensor_name, s.location, s.sensor_type,
           r.temperature, r.humidity, r.co2_level, r.air_quality_index, r.raining, r.recorded_at
    FROM sensors s
    LEFT JOIN (
        SELECT sensor_id, temperature, humidity, co2_level, air_quality_index, raining, recorded_at
        FROM air_readings
        WHERE (sensor_id, recorded_at) IN (
            SELECT sensor_id, MAX(recorded_at) FROM air_readings GROUP BY sensor_id
        )
    ) r ON s.sensor_id = r.sensor_id
    ORDER BY s.sensor_id
");

// ── Recent alerts ────────────────────────────────────────────────
$recent_alerts = $conn->query("
    SELECT a.*, s.sensor_name FROM alerts a
    JOIN sensors s ON a.sensor_id = s.sensor_id
    ORDER BY a.alert_time DESC LIMIT 5
");

// ── Ventilation systems ─────────────────────────────────────────
$vents = $conn->query("SELECT * FROM ventilation_systems ORDER BY location");

// ── Users ───────────────────────────────────────────────────────
$users = $conn->query("SELECT user_id, username, email, role, created_at FROM users ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard · AirVent</title>
<style>
body { font-family: Arial, sans-serif; background:#f4f6f9; margin:0; padding:0; }
header { background:#007bff; color:#fff; padding:15px; font-size:18px; display:flex; justify-content:space-between; }
main { padding:20px; }
.section { margin-bottom:30px; }
table { width:100%; border-collapse:collapse; }
table th, table td { padding:8px; border:1px solid #ccc; text-align:left; }
.stat-grid { display:flex; gap:20px; margin-bottom:20px; }
.stat-card { flex:1; background:#fff; padding:15px; border-radius:6px; box-shadow:0 0 5px rgba(0,0,0,0.1); }
.stat-card h2 { margin:0; font-size:24px; }
.logout { color:#fff; text-decoration:none; background:#ff4d4d; padding:5px 10px; border-radius:4px; }
.badge { padding:2px 6px; border-radius:4px; color:#fff; font-size:12px; }
.badge-red { background:#ff4d4d; }
.badge-green { background:#28a745; }
.badge-yellow { background:#ffc107; color:#000; }
</style>
</head>
<body>

<header>
    <div>Admin Dashboard - AirVent</div>
    <div>
        Hello, <?php echo htmlspecialchars($admin['username']); ?> | 
        <a href="../../logout.php" class="logout">Logout</a>
    </div>
</header>

<main>

<div class="section stat-grid">
    <div class="stat-card">
        <h2><?php echo $total_users; ?></h2>
        <div>Total Users</div>
    </div>
    <div class="stat-card">
        <h2><?php echo $active_sensors; ?></h2>
        <div>Active Sensors / <?php echo $total_sensors; ?></div>
    </div>
    <div class="stat-card">
        <h2><?php echo $vents_on; ?></h2>
        <div>Vents ON / <?php echo $total_vents; ?></div>
    </div>
    <div class="stat-card">
        <h2><?php echo $active_alerts; ?></h2>
        <div>Active Alerts</div>
    </div>
</div>

<div class="section">
    <h3>Recent Alerts</h3>
    <table>
        <tr><th>Sensor</th><th>Status</th><th>Time</th></tr>
        <?php while($a = $recent_alerts->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($a['sensor_name']); ?></td>
            <td>
                <span class="badge <?php echo $a['status']==='active'?'badge-red':'badge-green'; ?>">
                    <?php echo $a['status']; ?>
                </span>
            </td>
            <td><?php echo date('M d H:i', strtotime($a['alert_time'])); ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<div class="section">
    <h3>Latest Sensor Readings</h3>
    <table>
        <tr><th>Sensor</th><th>Temp</th><th>Humidity</th><th>CO2</th><th>AQI</th><th>Rain</th></tr>
        <?php while($r = $latest_readings->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($r['sensor_name']); ?></td>
            <td><?php echo $r['temperature'] ?? '—'; ?></td>
            <td><?php echo $r['humidity'] ?? '—'; ?></td>
            <td><?php echo $r['co2_level'] ?? '—'; ?></td>
            <td><?php echo $r['air_quality_index'] ?? '—'; ?></td>
            <td><?php echo $r['raining'] ?? '—'; ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<div class="section">
    <h3>Registered Users</h3>
    <table>
        <tr><th>#</th><th>Username</th><th>Email</th><th>Role</th><th>Joined</th></tr>
        <?php while($u = $users->fetch_assoc()): ?>
        <tr>
            <td><?php echo $u['user_id']; ?></td>
            <td><?php echo htmlspecialchars($u['username']); ?></td>
            <td><?php echo htmlspecialchars($u['email']); ?></td>
            <td><?php echo $u['role']; ?></td>
            <td><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

</main>
</body>
</html>