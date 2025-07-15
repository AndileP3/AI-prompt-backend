<?php
include 'conn.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Database connection

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Validate user_id parameter
if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing or invalid user_id parameter']);
    exit;
}

$user_id = intval($_GET['user_id']);

// Query posts for the given user
$sql = "SELECT post_id, message, image FROM posts WHERE user_id = ? ORDER BY post_id DESC";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'SQL Prepare Error: ' . $conn->error]);
    $conn->close();
    exit;
}

$stmt->bind_param("i", $user_id);

if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'SQL Execute Error: ' . $stmt->error]);
    $stmt->close();
    $conn->close();
    exit;
}

$result = $stmt->get_result();

$posts = [];

while ($row = $result->fetch_assoc()) {
    $posts[] = [
        'post_id' => $row['post_id'],
        'prompt' => $row['message'],
        'image' => 'http://localhost/AI/uploads/' . $row['image']
    ];
}

echo json_encode([
    'success' => true,
    'total_posts' => count($posts),
    'posts' => $posts
]);

$stmt->close();
$conn->close();
?>
