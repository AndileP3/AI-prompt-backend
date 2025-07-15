<?php
include 'conn.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

if (!isset($_GET['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing user ID']);
    exit;
}

$user_id = intval($_GET['user_id']);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'DB connection failed']);
    exit;
}

$sql = "SELECT message, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 20";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

echo json_encode(['success' => true, 'notifications' => $notifications]);

$stmt->close();
$conn->close();
?>
