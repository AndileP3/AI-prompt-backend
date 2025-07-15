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
}else{
    echo "Connected Successfully";
}