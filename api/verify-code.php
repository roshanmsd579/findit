<?php
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

if (!is_logged_in() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$claimId = (int) ($_POST['claim_id'] ?? 0);
$code = strtoupper(trim($_POST['code'] ?? ''));
if ($claimId <= 0 || $code === '') {
    echo json_encode(['success' => false, 'message' => 'Missing claim or code']);
    exit;
}

$stmt = $pdo->prepare('SELECT c.*, r.user_id AS reporter_id FROM claims c JOIN reports r ON c.report_id=r.id WHERE c.id=? LIMIT 1');
$stmt->execute([$claimId]);
$claim = $stmt->fetch();
if (!$claim) {
    echo json_encode(['success' => false, 'message' => 'Claim not found']);
    exit;
}
if ((int) $claim['claimant_id'] !== (int) $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'Only claimant can verify code']);
    exit;
}

if (strtoupper((string) $claim['verification_code']) !== $code) {
    echo json_encode(['success' => false, 'message' => 'Incorrect code']);
    exit;
}

$pdo->prepare("UPDATE claims SET code_entered=?, claimant_confirmed=1, status='verified' WHERE id=?")->execute([$code, $claimId]);
$pdo->prepare("UPDATE reports SET status='verified' WHERE id=?")->execute([(int) $claim['report_id']]);

echo json_encode(['success' => true]);
?>
