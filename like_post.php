<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

if (!isset($_POST['post_id'], $_POST['user_id'], $_POST['username'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$post_id = intval($_POST['post_id']);
$user_id = intval($_POST['user_id']);
$username = $_POST['username'];

$conn = new mysqli("localhost", "root", "", "prompt_ai");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'DB connection failed']);
    exit;
}

// Get the owner of the post
$stmtOwner = $conn->prepare("SELECT user_id FROM posts WHERE post_id = ?");
$stmtOwner->bind_param("i", $post_id);
$stmtOwner->execute();
$stmtOwner->bind_result($owner_id);
$stmtOwner->fetch();
$stmtOwner->close();

// Update likes count
$stmt = $conn->prepare("UPDATE posts SET likes_count = likes_count + 1 WHERE post_id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    // Save notification if liker is not the owner
    if ($owner_id && $owner_id != $user_id) {
        $notif = $conn->prepare("INSERT INTO notifications (user_id, sender_id, post_id, message) VALUES (?, ?, ?, ?)");
        $message = "$username liked your post";
        $notif->bind_param("iiis", $owner_id, $user_id, $post_id, $message);
        $notif->execute();
        $notif->close();
    }

    echo json_encode(['success' => true, 'message' => 'Like added']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update likes']);
}

$stmt->close();
$conn->close();
?>
