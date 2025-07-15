<?php
$servername = "216.126.194.27";
$username = "bluenrol_andile";
$password = "mamogobalale@2001";
$dbname = "bluenrol_prompt_ai";
$port = 3306;

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set MySQL timezone to South African Time (UTC+2)
if (!$conn->query("SET time_zone = '+02:00'")) {
    die("Error setting time zone: " . $conn->error);
}
?>
