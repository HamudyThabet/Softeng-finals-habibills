<?php
// Shared session bootstrap + auth check for all api/*.php endpoints
session_start();
require_once __DIR__ . '/connection.php';

function requireAuth() {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(["error" => "Not authenticated. Please log in."]);
        exit;
    }
}
