<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

$rawInput = file_get_contents("php://input");
$data = json_decode($rawInput, true);

if (!isset($data['post_id'], $data['user_id'], $data['comment_text'], $data['username'])) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

$post_id = intval($data['post_id']);
$user_id = intval($data['user_id']);
$username = $data['username'];
$comment_text = trim($data['comment_text']);

if ($comment_text === "") {
    echo json_encode(['success' => false, 'message' => 'Comment cannot be empty']);
    exit;
}

$conn = new mysqli("localhost", "root", "", "prompt_ai");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'DB connection failed']);
    exit;
}

// Get post owner
$stmtOwner = $conn->prepare("SELECT user_id FROM posts WHERE post_id = ?");
$stmtOwner->bind_param("i", $post_id);
$stmtOwner->execute();
$stmtOwner->bind_result($owner_id);
$stmtOwner->fetch();
$stmtOwner->close();

// Insert comment
$stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, comment_text) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $post_id, $user_id, $comment_text);

if ($stmt->execute()) {
    // Add notification
    if ($owner_id && $owner_id != $user_id) {
        $notif = $conn->prepare("INSERT INTO notifications (user_id, sender_id, post_id, message) VALUES (?, ?, ?, ?)");
        $message = "$username commented on your post";
        $notif->bind_param("iiis", $owner_id, $user_id, $post_id, $message);
        $notif->execute();
        $notif->close();
    }

    echo json_encode(['success' => true, 'message' => 'Comment added']);
} else {
    echo json_encode(['success' => false, 'message' => 'Execute failed: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
