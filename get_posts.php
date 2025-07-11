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
    $images = json_decode($row['image'], true); // decode JSON to array

    if (!is_array($images)) {
        // If empty string or invalid JSON, fallback to empty array
        $images = [];
    }

    // Filter out any empty values and prefix URLs
    $fullImageUrls = array_map(function($img) {
        return 'http://localhost/AI/uploads/' . $img;
    }, array_filter($images, fn($img) => !empty($img)));

    $posts[] = [
        'post_id' => intval($row['post_id']),
        'prompt' => $row['message'],
        'image' => $fullImageUrls
    ];
}

echo json_encode(['success' => true, 'posts' => $posts]);

$conn->close();
