<?php
require_once __DIR__ . '/guard.php';
requireAuth();
header('Content-Type: application/json');

$userId = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $conn->prepare("SELECT goal_id, goal_name, target_amount, current_amount, deadline, status FROM goals WHERE user_id = ? ORDER BY deadline ASC, goal_id DESC");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    echo json_encode($rows);
    $stmt->close();

} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $goalName = trim($data['goal_name'] ?? '');
    $targetAmount = isset($data['target_amount']) ? (float)$data['target_amount'] : null;
    $currentAmount = isset($data['current_amount']) ? (float)$data['current_amount'] : 0;
    $deadline = $data['deadline'] ?? '';

    if ($goalName === '' || $targetAmount === null || $deadline === '') {
        http_response_code(400);
        echo json_encode(["error" => "Goal name, target amount, and deadline are required."]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO goals (user_id, goal_name, target_amount, current_amount, deadline, status) VALUES (?, ?, ?, ?, ?, 'In Progress')");
    $stmt->bind_param("isdds", $userId, $goalName, $targetAmount, $currentAmount, $deadline);
    $stmt->execute();
    $newId = $stmt->insert_id;
    $stmt->close();

    echo json_encode([
        "goal_id" => $newId,
        "goal_name" => $goalName,
        "target_amount" => $targetAmount,
        "current_amount" => $currentAmount,
        "deadline" => $deadline,
        "status" => "In Progress"
    ]);

} elseif ($method === 'PUT') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $data = json_decode(file_get_contents('php://input'), true);
    $goalName = trim($data['goal_name'] ?? '');
    $targetAmount = isset($data['target_amount']) ? (float)$data['target_amount'] : null;
    $currentAmount = isset($data['current_amount']) ? (float)$data['current_amount'] : 0;
    $deadline = $data['deadline'] ?? '';

    if ($id === 0 || $goalName === '' || $targetAmount === null || $deadline === '') {
        http_response_code(400);
        echo json_encode(["error" => "Goal name, target amount, and deadline are required."]);
        exit;
    }

    $stmt = $conn->prepare("UPDATE goals SET goal_name = ?, target_amount = ?, current_amount = ?, deadline = ? WHERE goal_id = ? AND user_id = ?");
    $stmt->bind_param("sddsii", $goalName, $targetAmount, $currentAmount, $deadline, $id, $userId);
    $stmt->execute();
    $stmt->close();

    echo json_encode([
        "goal_id" => $id,
        "goal_name" => $goalName,
        "target_amount" => $targetAmount,
        "current_amount" => $currentAmount,
        "deadline" => $deadline
    ]);

} elseif ($method === 'DELETE') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    $stmt = $conn->prepare("DELETE FROM goals WHERE goal_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $userId);
    $stmt->execute();
    echo json_encode(["success" => $stmt->affected_rows > 0]);
    $stmt->close();

} else {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed."]);
}
?>