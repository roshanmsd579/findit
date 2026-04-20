<?php
require_once __DIR__ . '/includes/db.php';
require_login();

$profileId = (int) ($_GET['id'] ?? $_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $profileId === (int) $_SESSION['user_id']) {
    $phone = trim($_POST['phone'] ?? '');
    $department = trim($_POST['department'] ?? '');
    if (in_array($department, DEPARTMENTS, true)) {
        $pdo->prepare('UPDATE users SET phone = ?, department = ? WHERE id = ?')->execute([$phone, $department, $profileId]);
    }
    redirect('profile.php');
}

$userStmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$userStmt->execute([$profileId]);
$user = $userStmt->fetch();
if (!$user) {
    redirect('dashboard.php');
}

$reportsStmt = $pdo->prepare('SELECT * FROM reports WHERE user_id = ? ORDER BY created_at DESC');
$reportsStmt->execute([$profileId]);
$reports = $reportsStmt->fetchAll();

$reviewsStmt = $pdo->prepare('SELECT rv.*, u.name AS reviewer_name FROM reviews rv JOIN users u ON rv.reviewer_id = u.id WHERE rv.reviewed_id = ? ORDER BY rv.created_at DESC');
$reviewsStmt->execute([$profileId]);
$reviews = $reviewsStmt->fetchAll();

$pageTitle = 'Profile - ' . SITE_NAME;
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>
<div class="container py-5">
  <div class="row g-4">
    <div class="col-lg-4">
      <div class="report-card p-4 text-center">
        <div class="avatar-sm mx-auto mb-3" style="width:80px;height:80px;font-size:24px;"><?= h(strtoupper(substr($user['name'], 0, 2))) ?></div>
        <h3 class="mb-1"><?= h($user['name']) ?></h3>
        <div class="text-muted small mb-2"><?= h($user['student_id'] ?: '-') ?> | <?= h($user['department'] ?: '-') ?></div>
        <span class="badge badge-status badge-active"><?= h(ucfirst($user['role'])) ?></span>
        <hr>
        <div class="small text-muted mb-2">Member since <?= h(human_date($user['created_at'])) ?></div>
        <div class="mb-2">⭐ <?= number_format((float) $user['rating'], 2) ?> (<?= (int) $user['rating_count'] ?> reviews)</div>
        <div>Total reports contributed: <?= count($reports) ?></div>
      </div>

      <?php if ($profileId === (int) $_SESSION['user_id']): ?>
      <div class="report-card p-4 mt-3">
        <h5 class="mb-3">Edit Profile</h5>
        <form method="post">
          <div class="mb-2"><label class="form-label-custom">Phone</label><input name="phone" class="form-custom" value="<?= h($user['phone']) ?>"></div>
          <div class="mb-3"><label class="form-label-custom">Department</label>
            <select name="department" class="form-custom">
              <?php foreach (DEPARTMENTS as $dep): ?><option value="<?= h($dep) ?>" <?= $dep === $user['department'] ? 'selected' : '' ?>><?= h($dep) ?></option><?php endforeach; ?>
            </select>
          </div>
          <button class="btn btn-accent w-100" type="submit">Save Changes</button>
        </form>
      </div>
      <?php endif; ?>
    </div>

    <div class="col-lg-8">
      <ul class="nav nav-tabs mb-3">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#profile-reports" type="button">Reports</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-reviews" type="button">Reviews Received</button></li>
      </ul>
      <div class="tab-content">
        <div class="tab-pane fade show active" id="profile-reports">
          <div class="d-flex flex-column gap-2">
            <?php foreach ($reports as $r): ?>
              <a class="report-card p-3 text-decoration-none" href="<?= BASE_URL ?>report-detail.php?id=<?= (int) $r['id'] ?>">
                <div class="d-flex justify-content-between mb-1"><strong><?= h($r['title']) ?></strong><span class="<?= report_type_badge($r['type']) ?>"><?= strtoupper(h($r['type'])) ?></span></div>
                <div class="small text-muted"><?= h(report_location($r)) ?> | <?= h(human_date($r['date_occurred'])) ?> | <?= h(ucfirst($r['status'])) ?></div>
              </a>
            <?php endforeach; ?>
            <?php if (!$reports): ?><div class="report-card p-3">No reports available.</div><?php endif; ?>
          </div>
        </div>
        <div class="tab-pane fade" id="profile-reviews">
          <div class="d-flex flex-column gap-2">
            <?php foreach ($reviews as $rv): ?>
              <div class="report-card p-3">
                <div class="d-flex justify-content-between"><strong><?= str_repeat('★', (int) $rv['rating']) . str_repeat('☆', 5 - (int) $rv['rating']) ?></strong><small class="text-muted"><?= h(human_date($rv['created_at'])) ?></small></div>
                <p class="mb-1 mt-2"><?= h($rv['comment'] ?: 'No comment') ?></p>
                <small class="text-muted">By <?= h($rv['reviewer_name']) ?></small>
              </div>
            <?php endforeach; ?>
            <?php if (!$reviews): ?><div class="report-card p-3">No reviews yet.</div><?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
