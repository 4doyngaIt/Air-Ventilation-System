<?php
session_start();

 unset($_SESSION['sensors']);

// ───────────── INITIAL SENSOR DATA ─────────────
if (!isset($_SESSION['sensors'])) {
    $_SESSION['sensors'] = [
        [
            'id' => 1,
            'name' => 'Alae Sensor',
            'lat' => 8.4210,
            'lng' => 124.8490,
            'temperature' => 24,
            'humidity' => 55,
            'status' => 'Active',
            'power' => 'ON',
            'last_updated' => date('Y-m-d H:i:s')
        ],
        [
            'id' => 2,
            'name' => 'Damilag Sensor',
            'lat' => 8.3698,
            'lng' => 124.8547,
            'temperature' => 24,
            'humidity' => 55,
            'status' => 'Active',
            'power' => 'ON',
            'last_updated' => date('Y-m-d H:i:s')
        ],
        [
            'id' => 3,
            'name' => 'Tankulan Sensor',
            'lat' => 8.3541,
            'lng' => 124.8672,
            'temperature' => 24,
            'humidity' => 55,
            'status' => 'Active',
            'power' => 'ON',
            'last_updated' => date('Y-m-d H:i:s')
        ],
    ];
}
$sensors = &$_SESSION['sensors'];

// ───────────── UPDATE SENSOR READINGS ─────────────
foreach ($sensors as &$sensor) {
    if ($sensor['power'] === 'ON' && $sensor['status'] === 'Active') {
        // Validate numeric before updating
        if (!is_numeric($sensor['temperature'])) $sensor['temperature'] = 24;
        if (!is_numeric($sensor['humidity'])) $sensor['humidity'] = 55;

        $sensor['temperature'] += rand(-1, 1);
        $sensor['humidity'] += rand(-2, 2);
        $sensor['last_updated'] = date('Y-m-d H:i:s');
    }
}

// ───────────── HANDLE TOGGLE POWER ─────────────
if (isset($_POST['toggle'])) {
    $id = (int)$_POST['id'];
    foreach ($sensors as &$sensor) {
        if ($sensor['id'] === $id) {
            $sensor['power'] = ($sensor['power'] === 'ON') ? 'OFF' : 'ON';
            $sensor['last_updated'] = date('Y-m-d H:i:s');
            break; // stop loop when found
        }
    }
}

// ───────────── HANDLE MALFUNCTION TOGGLE ─────────────
if (isset($_POST['malfunction'])) {
    $id = (int)$_POST['id'];
    foreach ($sensors as &$sensor) {
        if ($sensor['id'] === $id) {
            $sensor['status'] = ($sensor['status'] === 'Active') ? 'Malfunction' : 'Active';
            if ($sensor['status'] === 'Active') {
                if (!is_numeric($sensor['temperature'])) $sensor['temperature'] = 24;
                if (!is_numeric($sensor['humidity'])) $sensor['humidity'] = 55;
            }
            $sensor['last_updated'] = date('Y-m-d H:i:s');
            break; // stop loop when found
        }
    }
}

// ───────────── HANDLE ALERTS ─────────────
if (!isset($_SESSION['alerts'])) {
    $_SESSION['alerts'] = [];
}
$alerts = &$_SESSION['alerts'];

if (isset($_POST['send_alert'])) {
    $id = (int)$_POST['id'];
    foreach ($sensors as $sensor) {
        if ($sensor['id'] === $id) {
            $alerts[] = "Alert sent for sensor: " . htmlspecialchars($sensor['name']) . " at " . date('Y-m-d H:i:s');
            break; // stop loop when found
        }
    }
}

// ───────────── PAGE SECTION ─────────────
$section = $_GET['section'] ?? 'home';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>PHP Climate Dashboard - Manolo Fortich</title>
    <style>
        body {
            font-family: Arial;
            margin: 0;
            display: flex;
            background: #f4f7f9;
        }

        .sidebar {
            width: 200px;
            background: #0077cc;
            color: white;
            height: 100vh;
            padding-top: 20px;
            flex-shrink: 0;
        }

        .sidebar a {
            display: block;
            padding: 15px;
            color: white;
            text-decoration: none;
            font-weight: bold;
        }

        .sidebar a:hover {
            background: #005fa3;
        }

        .main {
            flex: 1;
            padding: 20px;
        }

        h2 {
            color: #0077cc;
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.2);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }

        th {
            background: #0077cc;
            color: white;
        }

        button {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .on {
            background: green;
            color: white;
        }

        .off {
            background: red;
            color: white;
        }

        .alert-btn {
            background: orange;
            color: white;
        }

        .malf-btn {
            background: #cc0000;
            color: white;
        }

        .alert {
            background: #ffdddd;
            border-left: 5px solid red;
            padding: 10px;
            margin-top: 10px;
            font-weight: bold;
        }

        .map-box {
            margin-top: 10px;
        }

        iframe {
            width: 100%;
            height: 250px;
            border: 0;
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <a href="?section=home">Map Overview</a>
        <a href="?section=monitor">Sensor Monitoring</a>
        <a href="?section=alerts">Notifications</a>
    </div>
    <div class="main">

        <?php if ($section === 'home'): ?>
            <h2>Sensor Map - Manolo Fortich</h2>
            <?php foreach ($sensors as $sensor): ?>
                <div class="card">
                    <h3><?= htmlspecialchars($sensor['name']) ?> - <?= htmlspecialchars($sensor['status']) ?></h3>
                    <div class="map-box">
                        <iframe src="https://maps.google.com/maps?q=<?= $sensor['lat'] ?>,<?= $sensor['lng'] ?>&z=16&output=embed"></iframe>
                    </div>
                </div>
            <?php endforeach; ?>

        <?php elseif ($section === 'monitor'): ?>
            <h2>Sensor Monitoring</h2>
            <?php foreach ($sensors as $sensor): ?>
                <div class="card">
                    <h3><?= htmlspecialchars($sensor['name']) ?> - <?= htmlspecialchars($sensor['status']) ?></h3>
                    <table>
                        <tr>
                            <th>Status</th>
                            <th>Temperature</th>
                            <th>Humidity</th>
                            <th>Last Updated</th>
                            <th>Power</th>
                            <th>Actions</th>
                        </tr>
                        <tr>
                            <td><?= htmlspecialchars($sensor['status']) ?></td>
                            <td><?= is_numeric($sensor['temperature']) ? htmlspecialchars($sensor['temperature']) . '°C' : '--' ?></td>
                            <td><?= is_numeric($sensor['humidity']) ? htmlspecialchars($sensor['humidity']) . '%' : '--' ?></td>
                            <td><?= htmlspecialchars($sensor['last_updated']) ?></td>
                            <td><?= htmlspecialchars($sensor['power']) ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($sensor['id']) ?>" />
                                    <button class="<?= $sensor['power'] === "ON" ? 'on' : 'off' ?>" name="toggle">Toggle Power</button>
                                </form>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($sensor['id']) ?>" />
                                    <button class="alert-btn" name="send_alert">Send Alert</button>
                                </form>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($sensor['id']) ?>" />
                                    <button class="malf-btn" name="malfunction"><?= $sensor['status'] === 'Active' ? 'Simulate Malfunction' : 'Fix Sensor' ?></button>
                                </form>
                            </td>
                        </tr>
                    </table>
                </div>
            <?php endforeach; ?>

        <?php elseif ($section === 'alerts'): ?>
            <h2>Notifications</h2>
            <?php if (!empty($alerts)): ?>
                <ul>
                    <?php foreach ($alerts as $note): ?>
                        <li><?= htmlspecialchars($note) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No alerts yet.</p>
            <?php endif; ?>
        <?php endif; ?>

    </div>
</body>

</html>