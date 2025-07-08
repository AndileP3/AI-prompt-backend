<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

if (!isset($_POST['post_id'])) {
    echo json_encode(['success' => false, 'message' => 'No post ID specified']);
    exit;
}

$post_id = intval($_POST['post_id']);

$servername = "localhost";
$dbusername = "root";
$dbpassword = "";
$dbname = "prompt_ai";

$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Increment the likes count
$stmt = $conn->prepare("UPDATE posts SET likes_count = likes_count + 1 WHERE post_id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(['success' => true, 'message' => 'Like added']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update likes']);
}

$stmt->close();
$conn->close();
?>
