<?php
require_once __DIR__ . '/guard.php';
requireAuth();
header('Content-Type: application/json');

$userId = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $conn->prepare("SELECT expense_id, amount, category, date, description FROM expenses WHERE user_id = ? ORDER BY date DESC, expense_id DESC");
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
    $category = trim($data['category'] ?? '');
    $date = $data['date'] ?? '';
    $description = trim($data['description'] ?? '');

    if ($amount === null || $category === '' || $date === '') {
        http_response_code(400);
        echo json_encode(["error" => "Amount, category, and date are required."]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO expenses (user_id, amount, category, date, description) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("idsss", $userId, $amount, $category, $date, $description);
    $stmt->execute();
    $newId = $stmt->insert_id;
    $stmt->close();

    echo json_encode([
        "expense_id" => $newId,
        "amount" => $amount,
        "category" => $category,
        "date" => $date,
        "description" => $description
    ]);

} elseif ($method === 'PUT') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $data = json_decode(file_get_contents('php://input'), true);
    $amount = isset($data['amount']) ? (float)$data['amount'] : null;
    $category = trim($data['category'] ?? '');
    $date = $data['date'] ?? '';
    $description = trim($data['description'] ?? '');

    if ($id === 0 || $amount === null || $category === '' || $date === '') {
        http_response_code(400);
        echo json_encode(["error" => "Amount, category, and date are required."]);
        exit;
    }

    $stmt = $conn->prepare("UPDATE expenses SET amount = ?, category = ?, date = ?, description = ? WHERE expense_id = ? AND user_id = ?");
    $stmt->bind_param("dsssii", $amount, $category, $date, $description, $id, $userId);
    $stmt->execute();
    $stmt->close();

    echo json_encode([
        "expense_id" => $id,
        "amount" => $amount,
        "category" => $category,
        "date" => $date,
        "description" => $description
    ]);

} elseif ($method === 'DELETE') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    $stmt = $conn->prepare("DELETE FROM expenses WHERE expense_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $userId);
    $stmt->execute();
    echo json_encode(["success" => $stmt->affected_rows > 0]);
    $stmt->close();

} else {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed."]);
}
?>