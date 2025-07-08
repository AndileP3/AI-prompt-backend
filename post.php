<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Handle OPTIONS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

// Validate fields
if (!isset($_POST['message']) || !isset($_POST['user_id']) || !isset($_FILES['image'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$message = trim($_POST['message']);
$user_id = intval($_POST['user_id']);

// Validate image
if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'File upload error']);
    exit;
}

// Create uploads dir if needed
$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Generate unique filename
$ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
$filename = uniqid('post_', true) . '.' . $ext;
$filepath = $uploadDir . $filename;

if (!move_uploaded_file($_FILES['image']['tmp_name'], $filepath)) {
    echo json_encode(['success' => false, 'message' => 'Failed to save file']);
    exit;
}

// Save to database
$servername = "localhost";
$dbusername = "root";
$dbpassword = "";
$dbname = "prompt_ai";

$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'DB connection failed: ' . $conn->connect_error]);
    exit;
}

$stmt = $conn->prepare("INSERT INTO posts (user_id, message, image) VALUES (?, ?, ?)");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    $conn->close();
    exit;
}

$stmt->bind_param("iss", $user_id, $message, $filename);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Post saved successfully', 'filename' => $filename]);
} else {
    echo json_encode(['success' => false, 'message' => 'Insert failed: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
exit;
?>
