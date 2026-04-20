<?php
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

if (!is_logged_in() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$claimId = (int) ($_POST['claim_id'] ?? 0);
$role = $_POST['role'] ?? '';
if ($claimId <= 0 || !in_array($role, ['reporter', 'claimant'], true)) {
    echo json_encode(['success' => false, 'message' => 'Invalid payload']);
    exit;
}

$stmt = $pdo->prepare('SELECT c.*, r.user_id AS reporter_id FROM claims c JOIN reports r ON c.report_id=r.id WHERE c.id=? LIMIT 1');
$stmt->execute([$claimId]);
$claim = $stmt->fetch();
if (!$claim) {
    echo json_encode(['success' => false, 'message' => 'Claim not found']);
    exit;
}

if ($role === 'reporter' && (int) $claim['reporter_id'] !== (int) $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'Not allowed']);
    exit;
}
if ($role === 'claimant' && (int) $claim['claimant_id'] !== (int) $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'Not allowed']);
    exit;
}

$field = $role === 'reporter' ? 'reporter_confirmed' : 'claimant_confirmed';
$pdo->prepare("UPDATE claims SET {$field}=1 WHERE id=?")->execute([$claimId]);

$check = $pdo->prepare('SELECT * FROM claims WHERE id=?');
$check->execute([$claimId]);
$fresh = $check->fetch();
$both = (int) $fresh['reporter_confirmed'] === 1 && (int) $fresh['claimant_confirmed'] === 1;

if ($both) {
    $pdo->prepare("UPDATE claims SET status='verified' WHERE id=?")->execute([$claimId]);
    $pdo->prepare("UPDATE reports SET status='resolved' WHERE id=?")->execute([(int) $fresh['report_id']]);

    $pdo->prepare('INSERT INTO notifications (user_id, report_id, type, message, link) VALUES (?, ?, ?, ?, ?)')->execute([
        (int) $claim['reporter_id'],
        (int) $fresh['report_id'],
        'resolve',
        'Handover complete and report resolved.',
        'verify-resolution.php?claim_id=' . $claimId,
    ]);
    $pdo->prepare('INSERT INTO notifications (user_id, report_id, type, message, link) VALUES (?, ?, ?, ?, ?)')->execute([
        (int) $claim['claimant_id'],
        (int) $fresh['report_id'],
        'resolve',
        'Handover complete and report resolved.',
        'verify-resolution.php?claim_id=' . $claimId,
    ]);
}

echo json_encode(['success' => true, 'both_confirmed' => $both]);
?>
