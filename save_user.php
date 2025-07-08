<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

// Read input
$input = file_get_contents("php://input");
file_put_contents("debug_input.txt", $input);

$data = json_decode($input, true);

$response = [];

if (!$data) {
    $response = ['success' => false, 'message' => 'No input received'];
} else {
    $username = isset($data['username']) ? trim($data['username']) : '';
    $email = isset($data['email']) ? trim($data['email']) : '';
    $password = isset($data['password']) ? $data['password'] : '';

    if (empty($username) || empty($email) || empty($password)) {
        $response = ['success' => false, 'message' => 'All fields are required'];
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response = ['success' => false, 'message' => 'Invalid email format'];
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $servername = "localhost";
        $dbusername = "root";
        $dbpassword = "";
        $dbname = "prompt_ai";

        $conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);
        if ($conn->connect_error) {
            $response = ['success' => false, 'message' => 'DB connection failed: ' . $conn->connect_error];
        } else {
            $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
            if (!$stmt) {
                $response = ['success' => false, 'message' => 'Prepare failed (SELECT): ' . $conn->error];
            } else {
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->store_result();
                if ($stmt->num_rows > 0) {
                    $response = ['success' => false, 'message' => 'Email already registered'];
                } else {
                    $stmt->close();
                    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                    if (!$stmt) {
                        $response = ['success' => false, 'message' => 'Prepare failed (INSERT): ' . $conn->error];
                    } else {
                        $stmt->bind_param("sss", $username, $email, $hashed_password);
                        if ($stmt->execute()) {
                            $response = ['success' => true, 'message' => 'Account created successfully'];
                        } else {
                            $response = ['success' => false, 'message' => 'Insert failed: ' . $stmt->error];
                        }
                    }
                }
            }
            if (isset($stmt) && $stmt) {
                $stmt->close();
            }
            $conn->close();
        }
    }
}

// Capture any PHP notices
$output = ob_get_clean();
if (!empty($output)) {
    file_put_contents("debug_php_errors.txt", $output);
}

// Output JSON as the only response
echo json_encode($response);
exit;
