<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

if (!isset($_GET['post_id'])) {
    echo json_encode(['success' => false, 'message' => 'No post ID specified']);
    exit;
}

$id = intval($_GET['post_id']);

$servername = "localhost";
$dbusername = "root";
$dbpassword = "";
$dbname = "prompt_ai";

$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$stmt = $conn->prepare("
   SELECT 
    p.post_id, 
    p.user_id, 
    p.message, 
    p.image, 
    p.date,
    p.likes_count,
    u.username
FROM posts p
JOIN users u ON p.user_id = u.user_id
WHERE p.post_id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
 echo json_encode([
    'success' => true,
    'post' => [
        'post_id' => $row['post_id'],
        'user_id' => $row['user_id'],
        'prompt' => $row['message'],
        'image' => 'http://localhost/AI/uploads/' . $row['image'],
        'username' => $row['username'],
        'date' => $row['date'],
        'likes_count' => intval($row['likes_count'])
    ]
]);

} else {
    echo json_encode(['success' => false, 'message' => 'Post not found']);
}

$stmt->close();
$conn->close();
?>
