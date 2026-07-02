<?php
session_start();
require_once __DIR__ . '/connection.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

if ($email === '' || $password === '') {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Email and password are required."]);
    exit;
}

$stmt = $conn->prepare("SELECT user_id, full_name, email, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if ($user && password_verify($password, $user['password'])) {
    // Prevent session fixation
    session_regenerate_id(true);

    $_SESSION['user_id']   = $user['user_id'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['email']     = $user['email'];

    echo json_encode([
        "success" => true,
        "user" => [
            "user_id"   => $user['user_id'],
            "full_name" => $user['full_name'],
            "email"     => $user['email']
        ]
    ]);
} else {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Invalid email or password."]);
}
