<?php
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

if (!is_logged_in() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$reportId = (int) ($_POST['report_id'] ?? 0);
$answer = trim($_POST['secret_answer'] ?? '');
if ($reportId <= 0 || $answer === '') {
    echo json_encode(['success' => false, 'message' => 'Missing input']);
    exit;
}

$reportStmt = $pdo->prepare('SELECT id, user_id, secret_answer, status FROM reports WHERE id = ? LIMIT 1');
$reportStmt->execute([$reportId]);
$report = $reportStmt->fetch();
if (!$report || $report['status'] !== 'active') {
    echo json_encode(['success' => false, 'message' => 'Report unavailable']);
    exit;
}
if ((int) $report['user_id'] === (int) $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'Cannot claim your own report']);
    exit;
}

$correct = strcasecmp(trim((string) $report['secret_answer']), $answer) === 0;
if (!$correct) {
    echo json_encode(['success' => false, 'message' => 'Wrong answer', 'answer_correct' => false]);
    exit;
}

$code = generate_code(8);
$claimIns = $pdo->prepare("INSERT INTO claims (report_id, claimant_id, secret_answer, answer_correct, verification_code, status)
                           VALUES (?, ?, ?, 1, ?, 'code_sent')");
$claimIns->execute([$reportId, $_SESSION['user_id'], $answer, $code]);

$pdo->prepare("UPDATE reports SET status='claimed' WHERE id=?")->execute([$reportId]);
$pdo->prepare('INSERT INTO notifications (user_id, report_id, type, message, link) VALUES (?, ?, ?, ?, ?)')->execute([
    $report['user_id'],
    $reportId,
    'claim',
    'A user has submitted a claim for your report.',
    'report-detail.php?id=' . $reportId,
]);

echo json_encode(['success' => true, 'answer_correct' => true, 'claim_id' => (int) $pdo->lastInsertId()]);
?>
