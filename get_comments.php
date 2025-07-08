<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

if (!isset($_GET['post_id'])) {
    echo json_encode(['success' => false, 'message' => 'No post ID specified']);
    exit;
}

$post_id = intval($_GET['post_id']);

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
    SELECT c.comment_text, c.created_at, u.username
    FROM comments c
    JOIN users u ON c.user_id = u.user_id
    WHERE c.post_id = ?
    ORDER BY c.created_at ASC
");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();

$comments = [];
while ($row = $result->fetch_assoc()) {
    $comments[] = [
        'username' => $row['username'],
        'text' => $row['comment_text'],
        'date' => $row['created_at']
    ];
}

echo json_encode(['success' => true, 'comments' => $comments]);

$stmt->close();
$conn->close();
?>
