<?php
require_once __DIR__ . '/guard.php';
requireAuth();
header('Content-Type: application/json');

$userId = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $conn->prepare("SELECT income_id, amount, source, date, description FROM income WHERE user_id = ? ORDER BY date DESC, income_id DESC");
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
    $amount = isset($data['amount']) ? (float)$data['amount'] : null;
    $source = trim($data['source'] ?? '');
    $date = $data['date'] ?? '';
    $description = trim($data['description'] ?? '');

    if ($amount === null || $source === '' || $date === '') {
        http_response_code(400);
        echo json_encode(["error" => "Amount, source, and date are required."]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO income (user_id, amount, source, date, description) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("idsss", $userId, $amount, $source, $date, $description);
    $stmt->execute();
    $newId = $stmt->insert_id;
    $stmt->close();

    echo json_encode([
        "income_id" => $newId,
        "amount" => $amount,
        "source" => $source,
        "date" => $date,
        "description" => $description
    ]);

} elseif ($method === 'PUT') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $data = json_decode(file_get_contents('php://input'), true);
    $amount = isset($data['amount']) ? (float)$data['amount'] : null;
    $source = trim($data['source'] ?? '');
    $date = $data['date'] ?? '';
    $description = trim($data['description'] ?? '');

    if ($id === 0 || $amount === null || $source === '' || $date === '') {
        http_response_code(400);
        echo json_encode(["error" => "Amount, source, and date are required."]);
        exit;
    }

    $stmt = $conn->prepare("UPDATE income SET amount = ?, source = ?, date = ?, description = ? WHERE income_id = ? AND user_id = ?");
    $stmt->bind_param("dsssii", $amount, $source, $date, $description, $id, $userId);
    $stmt->execute();
    $stmt->close();

    echo json_encode([
        "income_id" => $id,
        "amount" => $amount,
        "source" => $source,
        "date" => $date,
        "description" => $description
    ]);

} elseif ($method === 'DELETE') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    $stmt = $conn->prepare("DELETE FROM income WHERE income_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $userId);
    $stmt->execute();
    echo json_encode(["success" => $stmt->affected_rows > 0]);
    $stmt->close();

} else {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed."]);
}
?>