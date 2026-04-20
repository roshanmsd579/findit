<?php
require_once __DIR__ . '/includes/db.php';

$reportId = (int) ($_GET['id'] ?? 0);
if ($reportId <= 0) {
    redirect('reports.php');
}

$pdo->prepare('UPDATE reports SET views = views + 1 WHERE id = ?')->execute([$reportId]);

$stmt = $pdo->prepare("SELECT r.*, u.name AS user_name, u.role AS user_role, u.id AS owner_id
                       FROM reports r
                       JOIN users u ON r.user_id = u.id
                       WHERE r.id = ? LIMIT 1");
$stmt->execute([$reportId]);
$report = $stmt->fetch();
if (!$report) {
    redirect('reports.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_message']) && is_logged_in()) {
    $comment = trim($_POST['comment_message']);
    if ($comment !== '') {
        $insComment = $pdo->prepare('INSERT INTO comments (report_id, user_id, message) VALUES (?, ?, ?)');
        $insComment->execute([$reportId, $_SESSION['user_id'], $comment]);
    }
    redirect('report-detail.php?id=' . $reportId);
}

$commentsStmt = $pdo->prepare('SELECT c.*, u.name FROM comments c JOIN users u ON c.user_id=u.id WHERE c.report_id=? ORDER BY c.created_at DESC');
$commentsStmt->execute([$reportId]);
$comments = $commentsStmt->fetchAll();

$claimStmt = $pdo->prepare('SELECT c.*, u.name AS claimant_name FROM claims c JOIN users u ON c.claimant_id=u.id WHERE c.report_id=? ORDER BY c.created_at DESC LIMIT 1');
$claimStmt->execute([$reportId]);
$claim = $claimStmt->fetch();

$matchesStmt = $pdo->prepare("SELECT m.*, lr.title AS lost_title, fr.title AS found_title
                              FROM matches m
                              JOIN reports lr ON m.lost_report_id = lr.id
                              JOIN reports fr ON m.found_report_id = fr.id
                              WHERE m.lost_report_id = ? OR m.found_report_id = ?
                              ORDER BY m.score DESC LIMIT 5");
$matchesStmt->execute([$reportId, $reportId]);
$matches = $matchesStmt->fetchAll();

$pageTitle = $report['title'] . ' - ' . SITE_NAME;
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>
<div class="container py-5">
  <div class="row g-4">
    <div class="col-lg-8">
      <article class="report-card">
        <?php if ($report['photo']): ?>
          <img src="<?= h(UPLOAD_URL . basename($report['photo'])) ?>" class="w-100" style="height:360px;object-fit:cover;" alt="<?= h($report['title']) ?>">
        <?php else: ?>
          <div class="report-card-placeholder" style="height:260px;">📍</div>
        <?php endif; ?>
        <div class="p-4">
          <div class="d-flex flex-wrap gap-2 mb-3">
            <span class="<?= report_type_badge($report['type']) ?>"><?= strtoupper(h($report['type'])) ?></span>
            <span class="badge badge-status <?= status_badge_class($report['status']) ?>"><?= h(ucfirst($report['status'])) ?></span>
          </div>
          <h1 class="h2 mb-3"><?= h($report['title']) ?></h1>
          <p class="mb-4"><?= nl2br(h($report['description'])) ?></p>

          <div class="row g-3 mb-4">
            <div class="col-md-6"><div class="report-card p-3 h-100"><small class="text-muted">Date</small><div><?= h(human_date($report['date_occurred'])) ?></div></div></div>
            <div class="col-md-6"><div class="report-card p-3 h-100"><small class="text-muted">Time</small><div><?= h(human_time($report['time_occurred'])) ?></div></div></div>
            <div class="col-md-6"><div class="report-card p-3 h-100"><small class="text-muted">Campus Location</small><div><?= h(report_location($report)) ?></div></div></div>
            <div class="col-md-6"><div class="report-card p-3 h-100"><small class="text-muted">Category</small><div><?= h(REPORT_CATEGORIES[$report['category']] ?? ucfirst($report['category'])) ?></div></div></div>
            <div class="col-md-6"><div class="report-card p-3 h-100"><small class="text-muted">Views</small><div><?= (int) $report['views'] + 1 ?></div></div></div>
            <div class="col-md-6"><div class="report-card p-3 h-100"><small class="text-muted">Posted By</small><div><?= h($report['user_name']) ?> (<?= h(ucfirst($report['user_role'])) ?>)</div></div></div>
          </div>

          <?php if ($report['latitude'] && $report['longitude']): ?>
            <div id="map" data-lat="<?= h((string) $report['latitude']) ?>" data-lng="<?= h((string) $report['longitude']) ?>"></div>
          <?php endif; ?>
        </div>
      </article>

      <section class="mt-4">
        <h4 class="mb-3">Comments</h4>
        <?php if (is_logged_in()): ?>
          <form method="post" class="mb-3">
            <textarea name="comment_message" class="form-custom mb-2" rows="3" placeholder="Add a helpful comment"></textarea>
            <button class="btn btn-accent" type="submit">Post Comment</button>
          </form>
        <?php endif; ?>
        <div class="d-flex flex-column gap-3">
          <?php foreach ($comments as $c): ?>
            <div class="report-card p-3">
              <div class="d-flex justify-content-between mb-1"><strong><?= h($c['name']) ?></strong><small class="text-muted"><?= h(human_datetime($c['created_at'])) ?></small></div>
              <p class="mb-0"><?= nl2br(h($c['message'])) ?></p>
            </div>
          <?php endforeach; ?>
          <?php if (!$comments): ?><div class="report-card p-3">No comments yet.</div><?php endif; ?>
        </div>
      </section>
    </div>

    <div class="col-lg-4">
      <div class="filter-panel">
        <h5 class="mb-3">Actions</h5>
        <p><strong>Status:</strong> <span class="badge badge-status <?= status_badge_class($report['status']) ?>"><?= h(ucfirst($report['status'])) ?></span></p>

        <?php $isOwner = is_logged_in() && (int) $_SESSION['user_id'] === (int) $report['owner_id']; ?>

        <?php if (is_logged_in() && !$isOwner && $report['status'] === 'active'): ?>
          <button class="btn btn-accent w-100 mb-2" data-bs-toggle="modal" data-bs-target="#claimModal">I Have This Item</button>
          <a class="btn btn-outline-custom w-100 mb-2" href="<?= BASE_URL ?>chat.php?report_id=<?= (int) $report['id'] ?>&with=<?= (int) $report['owner_id'] ?>">Chat with <?= h($report['user_name']) ?></a>
        <?php elseif (!is_logged_in()): ?>
          <a class="btn btn-accent w-100 mb-2" href="<?= BASE_URL ?>login.php">Login to Claim</a>
        <?php endif; ?>

        <?php if ($isOwner && $report['status'] === 'claimed' && $claim): ?>
          <div class="report-card p-3 mb-3">
            <small class="text-muted">Verification Code</small>
            <div class="code-display fs-3 py-3"><?= h($claim['verification_code']) ?></div>
            <p class="small text-muted mt-2 mb-0">Share this in person with finder and proceed to verify.</p>
            <a href="<?= BASE_URL ?>verify-resolution.php?claim_id=<?= (int) $claim['id'] ?>" class="btn btn-accent w-100 mt-2">Go to Verify Page</a>
          </div>
        <?php endif; ?>

        <?php if ($isOwner && in_array($report['status'], ['claimed', 'verified'], true) && $claim): ?>
          <?php if ($report['status'] === 'verified'): ?>
            <a class="btn btn-accent w-100 mb-2" href="<?= BASE_URL ?>verify-resolution.php?claim_id=<?= (int) $claim['id'] ?>">Mark as Resolved</a>
          <?php endif; ?>
          <button class="btn btn-outline-custom w-100 mb-2 dispute-toggle" data-claim="<?= (int) $claim['id'] ?>">Raise Dispute</button>
          <form class="dispute-form d-none" data-claim="<?= (int) $claim['id'] ?>">
            <textarea class="form-custom mb-2" name="reason" rows="3" placeholder="Explain the issue"></textarea>
            <button class="btn btn-danger w-100" type="submit">Submit Dispute</button>
          </form>
        <?php endif; ?>

        <hr>
        <h6>Potential Matches</h6>
        <div class="d-flex flex-column gap-2">
          <?php foreach ($matches as $m): ?>
            <?php $otherId = ((int) $m['lost_report_id'] === $reportId) ? (int) $m['found_report_id'] : (int) $m['lost_report_id']; ?>
            <a href="<?= BASE_URL ?>report-detail.php?id=<?= $otherId ?>" class="report-card p-2 small text-decoration-none">
              <div><?= h($m['lost_title']) ?> ↔ <?= h($m['found_title']) ?></div>
              <div class="text-muted">Score: <?= (int) $m['score'] ?> | <?= h(ucfirst($m['status'])) ?></div>
            </a>
          <?php endforeach; ?>
          <?php if (!$matches): ?><div class="small text-muted">No match suggestions yet.</div><?php endif; ?>
        </div>

        <hr>
        <div class="d-flex gap-2">
          <a class="btn btn-success w-50" target="_blank" href="https://wa.me/?text=<?= urlencode(BASE_URL . 'report-detail.php?id=' . (int) $report['id']) ?>">WhatsApp</a>
          <button class="btn btn-outline-custom w-50" id="copy-link-btn" data-link="<?= h(BASE_URL . 'report-detail.php?id=' . (int) $report['id']) ?>">Copy Link</button>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="claimModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content modal-dark">
      <div class="modal-header border-0"><h5 class="modal-title">Submit Claim</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <p class="small text-muted">Answer the reporter's question to verify ownership.</p>
        <?php if ($report['secret_question']): ?><div class="report-card p-2 mb-2"><strong><?= h($report['secret_question']) ?></strong></div><?php endif; ?>
        <input type="text" class="form-custom" id="claim-answer" placeholder="Your answer">
        <input type="hidden" id="claim-report-id" value="<?= (int) $report['id'] ?>">
      </div>
      <div class="modal-footer border-0"><button type="button" class="btn btn-accent" id="submit-claim-btn">Submit Claim</button></div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
