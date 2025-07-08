<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

if (!isset($_GET["post_id"])) {
    echo json_encode(["success" => false, "message" => "Missing post_id"]);
    exit;
}

$post_id = intval($_GET["post_id"]);

$servername = "localhost";
$dbusername = "root";
$dbpassword = "";
$dbname = "prompt_ai";

$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "DB error"]);
    exit;
}

$stmt = $conn->prepare("SELECT COUNT(*) FROM comments WHERE post_id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();
$conn->close();

echo json_encode(["success" => true, "count" => $count]);
?>
