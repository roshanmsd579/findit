<?php
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['messages' => [], 'error' => 'Unauthorized']);
    exit;
}

$reportId = (int) ($_GET['report_id'] ?? 0);
$with = (int) ($_GET['with'] ?? 0);
$after = (int) ($_GET['after'] ?? 0);
if ($reportId <= 0 || $with <= 0) {
    echo json_encode(['messages' => []]);
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM chat_messages WHERE report_id = ? AND ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)) AND id > ? ORDER BY id ASC');
$stmt->execute([$reportId, $_SESSION['user_id'], $with, $with, $_SESSION['user_id'], $after]);
$messages = $stmt->fetchAll();

$pdo->prepare('UPDATE chat_messages SET is_read = 1 WHERE report_id = ? AND sender_id = ? AND receiver_id = ?')->execute([$reportId, $with, $_SESSION['user_id']]);

echo json_encode(['messages' => $messages]);
?>
