<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$servername = "localhost";
$dbusername = "root";
$dbpassword = "";
$dbname = "prompt_ai";

$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$sql = "SELECT post_id, message, image FROM posts ORDER BY post_id DESC";
$result = $conn->query($sql);

if (!$result) {
    echo json_encode(['success' => false, 'message' => 'SQL Error: ' . $conn->error]);
    $conn->close();
    exit;
}

$posts = [];

while ($row = $result->fetch_assoc()) {
    $posts[] = [
        'post_id' => $row['post_id'],
        'prompt' => $row['message'],
        'image' => 'http://localhost/AI/uploads/' . $row['image']
    ];
}

echo json_encode(['success' => true, 'posts' => $posts]);

$conn->close();
?>
