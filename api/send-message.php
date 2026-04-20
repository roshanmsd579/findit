<?php
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

if (!is_logged_in() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$reportId = (int) ($_POST['report_id'] ?? 0);
$receiverId = (int) ($_POST['receiver_id'] ?? 0);
$message = trim($_POST['message'] ?? '');
if ($reportId <= 0 || $receiverId <= 0 || $message === '') {
    echo json_encode(['success' => false, 'message' => 'Invalid payload']);
    exit;
}

$ins = $pdo->prepare('INSERT INTO chat_messages (report_id, sender_id, receiver_id, message) VALUES (?, ?, ?, ?)');
$ins->execute([$reportId, $_SESSION['user_id'], $receiverId, $message]);
$msgId = (int) $pdo->lastInsertId();

$pdo->prepare('INSERT INTO notifications (user_id, report_id, type, message, link) VALUES (?, ?, ?, ?, ?)')->execute([
    $receiverId,
    $reportId,
    'chat',
    'New message on report #' . $reportId,
    'chat.php?report_id=' . $reportId . '&with=' . (int) $_SESSION['user_id'],
]);

echo json_encode(['success' => true, 'id' => $msgId]);
?>
