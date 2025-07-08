<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

$rawInput = file_get_contents("php://input");
file_put_contents("debug_add_comment.txt", $rawInput);

$data = json_decode($rawInput, true);
if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input', 'raw_input' => $rawInput]);
    exit;
}

if (
    !isset($data['post_id']) ||
    !isset($data['user_id']) ||
    !isset($data['comment_text'])
) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters', 'raw_input' => $rawInput]);
    exit;
}

$post_id = intval($data['post_id']);
$user_id = intval($data['user_id']);
$comment_text = trim($data['comment_text']);

if ($comment_text === "") {
    echo json_encode(['success' => false, 'message' => 'Comment cannot be empty']);
    exit;
}

$servername = "localhost";
$dbusername = "root";
$dbpassword = "";
$dbname = "prompt_ai";

$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'DB connection failed: '.$conn->connect_error]);
    exit;
}

$stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, comment_text) VALUES (?, ?, ?)");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed: '.$conn->error]);
    exit;
}

$stmt->bind_param("iis", $post_id, $user_id, $comment_text);

if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Execute failed: '.$stmt->error]);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Comment added']);
$stmt->close();
$conn->close();
?>
