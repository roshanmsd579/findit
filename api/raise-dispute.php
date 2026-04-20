<?php
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

if (!is_logged_in() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(401);
    echo json_encode(['success' => false]);
    exit;
}

$claimId = (int) ($_POST['claim_id'] ?? 0);
$reason = trim($_POST['reason'] ?? '');
if ($claimId <= 0 || $reason === '') {
    echo json_encode(['success' => false, 'message' => 'Reason required']);
    exit;
}

$stmt = $pdo->prepare('SELECT c.id, c.report_id FROM claims c WHERE c.id=? LIMIT 1');
$stmt->execute([$claimId]);
$claim = $stmt->fetch();
if (!$claim) {
    echo json_encode(['success' => false, 'message' => 'Claim not found']);
    exit;
}

$pdo->prepare('INSERT INTO disputes (claim_id, raised_by, reason) VALUES (?, ?, ?)')->execute([$claimId, $_SESSION['user_id'], $reason]);
$pdo->prepare("UPDATE claims SET status='disputed' WHERE id=?")->execute([$claimId]);
$pdo->prepare("UPDATE reports SET status='disputed' WHERE id=?")->execute([(int) $claim['report_id']]);

$adminUsers = $pdo->query("SELECT id FROM users WHERE role='admin'")->fetchAll();
foreach ($adminUsers as $admin) {
    $pdo->prepare('INSERT INTO notifications (user_id, report_id, type, message, link) VALUES (?, ?, ?, ?, ?)')->execute([
        (int) $admin['id'],
        (int) $claim['report_id'],
        'dispute',
        'New dispute raised for claim #' . $claimId,
        'admin.php',
    ]);
}

echo json_encode(['success' => true]);
?>
