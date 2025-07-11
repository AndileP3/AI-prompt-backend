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

// ...

if (!isset($_POST['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing user_id']);
    exit;
}

$message = trim($_POST['message'] ?? '');
$user_id = intval($_POST['user_id']);

$uploadedFilenames = [];

// Only process images if provided
if (!empty($_FILES['image']['name'][0])) {
    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    foreach ($_FILES['image']['name'] as $i => $name) {
        if ($_FILES['image']['error'][$i] === UPLOAD_ERR_OK) {
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            $filename = uniqid('post_', true) . '.' . $ext;
            $filepath = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['image']['tmp_name'][$i], $filepath)) {
                $uploadedFilenames[] = $filename;
            }
        }
    }
}

$imageJson = json_encode($uploadedFilenames); // Store as JSON string

$conn = new mysqli("localhost", "root", "", "prompt_ai");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'DB connection failed: ' . $conn->connect_error]);
    exit;
}

$stmt = $conn->prepare("INSERT INTO posts (user_id, message, image) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $user_id, $message, $imageJson);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Post saved', 'filenames' => $uploadedFilenames]);
} else {
    echo json_encode(['success' => false, 'message' => 'Insert failed: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
exit;

?>
