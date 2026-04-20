<?php
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

if (!is_logged_in() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(401);
    echo json_encode(['success' => false]);
    exit;
}

$claimId = (int) ($_POST['claim_id'] ?? 0);
$reviewedId = (int) ($_POST['reviewed_id'] ?? 0);
$rating = (int) ($_POST['rating'] ?? 0);
$comment = trim($_POST['comment'] ?? '');
if ($claimId <= 0 || $reviewedId <= 0 || $rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$exists = $pdo->prepare('SELECT COUNT(*) FROM reviews WHERE claim_id = ? AND reviewer_id = ?');
$exists->execute([$claimId, $_SESSION['user_id']]);
if ((int) $exists->fetchColumn() > 0) {
    echo json_encode(['success' => false, 'message' => 'Already rated']);
    exit;
}

$pdo->prepare('INSERT INTO reviews (claim_id, reviewer_id, reviewed_id, rating, comment) VALUES (?, ?, ?, ?, ?)')->execute([$claimId, $_SESSION['user_id'], $reviewedId, $rating, $comment]);
$avgStmt = $pdo->prepare('SELECT AVG(rating) AS avg_rating, COUNT(*) AS total FROM reviews WHERE reviewed_id = ?');
$avgStmt->execute([$reviewedId]);
$agg = $avgStmt->fetch();
$pdo->prepare('UPDATE users SET rating = ?, rating_count = ? WHERE id = ?')->execute([round((float) $agg['avg_rating'], 2), (int) $agg['total'], $reviewedId]);

echo json_encode(['success' => true]);
?>
