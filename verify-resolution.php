<?php
require_once __DIR__ . '/includes/db.php';
require_login();

$claimId = (int) ($_GET['claim_id'] ?? 0);
if ($claimId <= 0) {
    redirect('dashboard.php');
}

$stmt = $pdo->prepare("SELECT c.*, r.title AS report_title, r.user_id AS reporter_id, r.id AS report_id,
                              u1.name AS reporter_name, u2.name AS claimant_name
                       FROM claims c
                       JOIN reports r ON c.report_id = r.id
                       JOIN users u1 ON r.user_id = u1.id
                       JOIN users u2 ON c.claimant_id = u2.id
                       WHERE c.id = ? LIMIT 1");
$stmt->execute([$claimId]);
$claim = $stmt->fetch();
if (!$claim) {
    redirect('dashboard.php');
}

$isReporter = (int) $_SESSION['user_id'] === (int) $claim['reporter_id'];
$isClaimant = (int) $_SESSION['user_id'] === (int) $claim['claimant_id'];
if (!$isReporter && !$isClaimant && !is_admin()) {
    redirect('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_submit'])) {
    $rating = (int) ($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');
    $reviewedId = (int) ($_POST['reviewed_id'] ?? 0);
    if ($rating >= 1 && $rating <= 5 && in_array($reviewedId, [(int) $claim['reporter_id'], (int) $claim['claimant_id']], true) && $reviewedId !== (int) $_SESSION['user_id']) {
        $exists = $pdo->prepare('SELECT COUNT(*) FROM reviews WHERE claim_id = ? AND reviewer_id = ?');
        $exists->execute([$claimId, $_SESSION['user_id']]);
        if ((int) $exists->fetchColumn() === 0) {
            $pdo->prepare('INSERT INTO reviews (claim_id, reviewer_id, reviewed_id, rating, comment) VALUES (?, ?, ?, ?, ?)')->execute([$claimId, $_SESSION['user_id'], $reviewedId, $rating, $comment]);
            $avgStmt = $pdo->prepare('SELECT AVG(rating) AS avg_rating, COUNT(*) AS total FROM reviews WHERE reviewed_id = ?');
            $avgStmt->execute([$reviewedId]);
            $agg = $avgStmt->fetch();
            $pdo->prepare('UPDATE users SET rating = ?, rating_count = ? WHERE id = ?')->execute([round((float) $agg['avg_rating'], 2), (int) $agg['total'], $reviewedId]);
        }
    }
    redirect('verify-resolution.php?claim_id=' . $claimId);
}

$pageTitle = 'Verify Resolution - ' . SITE_NAME;
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>
<div class="container py-5" data-claim-id="<?= (int) $claimId ?>">
  <h1 class="section-title mb-2">Resolution Verification</h1>
  <p class="text-muted mb-4">Report: <?= h($claim['report_title']) ?></p>

  <div class="row g-4 mb-4">
    <div class="col-lg-6">
      <div class="report-card p-4 h-100">
        <h4 class="mb-3">Reporter's Card</h4>
        <p class="text-muted">Show this code to the finder in person.</p>
        <div class="code-display"><?= h($claim['verification_code']) ?></div>
        <?php if ($isReporter): ?>
          <button class="btn btn-accent w-100 mt-3 handover-confirm-btn" data-claim-id="<?= (int) $claimId ?>" data-role="reporter">Reporter Confirms Receipt</button>
        <?php else: ?>
          <div class="alert alert-secondary mt-3">Only reporter can confirm this card.</div>
        <?php endif; ?>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="report-card p-4 h-100">
        <h4 class="mb-3">Finder's Card</h4>
        <p class="text-muted">Enter the code shown by reporter.</p>
        <?php if ($isClaimant): ?>
          <input type="text" id="verify-code-input" class="code-input mb-3" maxlength="8" placeholder="--------">
          <button class="btn btn-accent w-100" id="verify-code-btn" data-claim-id="<?= (int) $claimId ?>">Submit Code</button>
          <button class="btn btn-outline-custom w-100 mt-2 handover-confirm-btn" data-claim-id="<?= (int) $claimId ?>" data-role="claimant">Claimant Confirms Handover</button>
          <div id="verify-result" class="small mt-2"></div>
        <?php else: ?>
          <div class="alert alert-secondary">Only claimant can submit this code.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <?php if ((int) $claim['reporter_confirmed'] === 1 && (int) $claim['claimant_confirmed'] === 1): ?>
    <div class="report-card p-4 mb-4" id="resolution-success" data-confetti="1">
      <h3 class="mb-2">Resolution Complete</h3>
      <p class="mb-0 text-muted">Both sides confirmed. Please leave a rating for trust and transparency.</p>
    </div>

    <?php
      $alreadyRatedStmt = $pdo->prepare('SELECT COUNT(*) FROM reviews WHERE claim_id = ? AND reviewer_id = ?');
      $alreadyRatedStmt->execute([$claimId, $_SESSION['user_id']]);
      $alreadyRated = (int) $alreadyRatedStmt->fetchColumn() > 0;
      $reviewedId = $isReporter ? (int) $claim['claimant_id'] : (int) $claim['reporter_id'];
    ?>

    <?php if (!$alreadyRated && ($isReporter || $isClaimant)): ?>
      <div class="report-card p-4">
        <h4 class="mb-3">Rate <?= h($isReporter ? $claim['claimant_name'] : $claim['reporter_name']) ?></h4>
        <form method="post" id="rating-form">
          <input type="hidden" name="review_submit" value="1">
          <input type="hidden" name="reviewed_id" value="<?= $reviewedId ?>">
          <input type="hidden" name="rating" id="rating-value" value="0">
          <div class="rating-stars mb-3" data-rating-wrap>
            <i class="bi bi-star-fill star" data-value="1"></i>
            <i class="bi bi-star-fill star" data-value="2"></i>
            <i class="bi bi-star-fill star" data-value="3"></i>
            <i class="bi bi-star-fill star" data-value="4"></i>
            <i class="bi bi-star-fill star" data-value="5"></i>
          </div>
          <textarea name="comment" class="form-custom mb-3" rows="3" placeholder="Write a short review"></textarea>
          <button class="btn btn-accent" type="submit">Submit Review</button>
        </form>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
