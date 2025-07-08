<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$rawInput = file_get_contents("php://input");

echo json_encode([
  "raw_input" => $rawInput
]);
?>
