<?php
include 'conn.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'No input received']);
    exit;
}

$email = isset($data['email']) ? trim($data['email']) : '';
$password = isset($data['password']) ? $data['password'] : '';

if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Email and password required']);
    exit;
}

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$stmt = $conn->prepare("SELECT user_id, username, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'No account with that email']);
    $stmt->close();
    $conn->close();
    exit;
}

$stmt->bind_result($id, $username, $hashed_password);
$stmt->fetch();

if (password_verify($password, $hashed_password)) {
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'user_id' => $id,
        'username' => $username,
        'email' => $email
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Incorrect password']);
}

$stmt->close();
$conn->close();
?>
