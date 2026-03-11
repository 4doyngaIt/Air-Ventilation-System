-- Create Database
CREATE DATABASE air_ventilation;

USE air_ventilation;

-- user table
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','manager','user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
-- sensor table

CREATE TABLE sensors (
    sensor_id INT AUTO_INCREMENT PRIMARY KEY,
    sensor_name VARCHAR(50) NOT NULL,
    sensor_type ENUM('Temperature','Humidity','CO2','AQI') NOT NULL,
    location VARCHAR(100) NOT NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active'
);

-- readings table

CREATE TABLE air_readings (
    reading_id INT AUTO_INCREMENT PRIMARY KEY,
    sensor_id INT NOT NULL,
    temperature DECIMAL(5,2),
    humidity DECIMAL(5,2),
    co2_level INT,
    air_quality_index INT,
    raining ENUM('yes','no') DEFAULT 'no',   -- rain detection
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (sensor_id) REFERENCES sensors(sensor_id)
    ON DELETE CASCADE
);


-- ventilation system table

CREATE TABLE ventilation_systems (
    ventilation_id INT AUTO_INCREMENT PRIMARY KEY,
    device_name VARCHAR(50) NOT NULL,
    location VARCHAR(100) NOT NULL,
    status ENUM('ON','OFF') NOT NULL DEFAULT 'OFF',
    mode ENUM('manual','automatic') NOT NULL DEFAULT 'automatic'
);

-- alert tables

CREATE TABLE alerts (
    alert_id INT AUTO_INCREMENT PRIMARY KEY,
    sensor_id INT NOT NULL,
    alert_type VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    alert_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active','resolved') NOT NULL DEFAULT 'active',
    
    FOREIGN KEY (sensor_id) REFERENCES sensors(sensor_id)
    ON DELETE CASCADE
);



-- insertion

INSERT INTO users (username, email, password, role) VALUES
('admin1', 'admin1@gmail.com', 'admin123', 'admin'),
('manager1', 'manager1@gmail.com', 'manager123', 'manager'),
('user1', 'user1@gmail.com', 'user123', 'user');

INSERT INTO sensors (sensor_name, sensor_type, location, status) VALUES
('Temperature Sensor 1', 'Temperature', 'Room 101', 'active'),
('Humidity Sensor 1', 'Humidity', 'Room 101', 'active'),
('CO2 Sensor 1', 'CO2', 'Conference Room', 'active'),
('Air Quality Sensor 1', 'AQI', 'Office Area', 'active');

INSERT INTO air_readings (sensor_id, temperature, humidity, co2_level, air_quality_index, raining) VALUES
(1, 32.5, 60.0, 450, 40, 'no'),   -- hot, indoor, ventilation should turn ON
(2, 28.0, 65.0, 470, 50, 'no'),
(3, 30.0, 70.0, 1200, 120, 'yes'), -- raining, ventilation should turn OFF
(4, 29.5, 68.0, 800, 90, 'no');

INSERT INTO ventilation_systems (device_name, location, status, mode) VALUES
('Vent Fan 1', 'Room 101', 'OFF', 'automatic'),
('Vent Fan 2', 'Conference Room', 'OFF', 'automatic'),
('Ceiling Vent 1', 'Office Area', 'OFF', 'automatic');

INSERT INTO alerts (sensor_id, alert_type, message, status) VALUES
(1, 'High Temperature', 'Indoor temperature exceeded threshold', 'active'),
(3, 'High CO2', 'CO2 level exceeded safe limit', 'active'),
(4, 'Poor Air Quality', 'Air quality index is unhealthy', 'active');








