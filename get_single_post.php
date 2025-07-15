<?php
include 'conn.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

if (!isset($_GET['post_id'])) {
    echo json_encode(['success' => false, 'message' => 'No post ID specified']);
    exit;
}

$postId = intval($_GET['post_id']);


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

$stmt->bind_param("i", $postId);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    // Decode image JSON from DB
    $images = json_decode($row['image'], true);
    if (!is_array($images)) $images = [];

    // Prefix full image URLs
    $images = array_map(fn($img) => "https://keailand.ct.ws/uploads/" . $img, $images);

    echo json_encode([
        'success' => true,
        'post' => [
            'post_id' => intval($row['post_id']),
            'user_id' => intval($row['user_id']),
            'prompt' => $row['message'],
            'image' => $images,
            'username' => $row['username'],
            'date' => $row['date'],
            'likes_count' => intval($row['likes_count']),
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Post not found']);
}

$stmt->close();
$conn->close();
?>
