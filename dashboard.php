<?php
require_once __DIR__ . '/includes/db.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_report') {
    $reportId = (int) ($_POST['report_id'] ?? 0);
    $del = $pdo->prepare('DELETE FROM reports WHERE id = ? AND user_id = ?');
    $del->execute([$reportId, $_SESSION['user_id']]);
    redirect('dashboard.php');
}

$userStmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$userStmt->execute([$_SESSION['user_id']]);
$user = $userStmt->fetch();

$statsStmt = $pdo->prepare("SELECT
  COUNT(*) AS total,
  SUM(CASE WHEN status IN ('active','claimed','verified') THEN 1 ELSE 0 END) AS active_count,
  SUM(CASE WHEN status='resolved' THEN 1 ELSE 0 END) AS resolved_count
  FROM reports WHERE user_id = ?");
$statsStmt->execute([$_SESSION['user_id']]);
$stats = $statsStmt->fetch();

$chatStmt = $pdo->prepare('SELECT COUNT(*) FROM chat_messages WHERE receiver_id = ? AND is_read = 0');
$chatStmt->execute([$_SESSION['user_id']]);
$unreadChats = (int) $chatStmt->fetchColumn();

$myReportsStmt = $pdo->prepare('SELECT * FROM reports WHERE user_id = ? ORDER BY created_at DESC');
$myReportsStmt->execute([$_SESSION['user_id']]);
$myReports = $myReportsStmt->fetchAll();

$claimsStmt = $pdo->prepare("SELECT c.*, r.title AS report_title, u.name AS claimant_name
                             FROM claims c
                             JOIN reports r ON c.report_id = r.id
                             JOIN users u ON c.claimant_id = u.id
                             WHERE r.user_id = ?
                             ORDER BY c.created_at DESC");
$claimsStmt->execute([$_SESSION['user_id']]);
$claims = $claimsStmt->fetchAll();

$notifStmt = $pdo->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50');
$notifStmt->execute([$_SESSION['user_id']]);
$notifications = $notifStmt->fetchAll();

$chatHistoryStmt = $pdo->prepare("SELECT cm.report_id,
    CASE WHEN cm.sender_id = ? THEN cm.receiver_id ELSE cm.sender_id END AS other_user_id,
    MAX(cm.id) AS last_message_id,
    MAX(cm.created_at) AS last_time,
    SUM(CASE WHEN cm.receiver_id = ? AND cm.is_read = 0 THEN 1 ELSE 0 END) AS unread
  FROM chat_messages cm
  WHERE cm.sender_id = ? OR cm.receiver_id = ?
  GROUP BY cm.report_id, other_user_id
  ORDER BY last_time DESC");
$chatHistoryStmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']]);
$chatHistory = $chatHistoryStmt->fetchAll();

$pageTitle = 'Dashboard - ' . SITE_NAME;
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>
<div class="container py-5">
  <div class="report-card p-4 mb-4">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
      <div class="d-flex gap-3 align-items-center">
        <div class="avatar-sm" style="width:72px;height:72px;font-size:24px;"><?= h(strtoupper(substr($user['name'], 0, 2))) ?></div>
        <div>
          <h2 class="mb-1"><?= h($user['name']) ?></h2>
          <div class="small text-muted">ID: <?= h($user['student_id'] ?: '-') ?> | <?= h($user['department'] ?: '-') ?></div>
          <span class="badge badge-status badge-active"><?= h(ucfirst($user['role'])) ?></span>
        </div>
      </div>
      <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>profile.php" class="btn btn-outline-custom">Edit Profile</a>
        <a href="<?= BASE_URL ?>create-report.php" class="btn btn-accent">+ New Report</a>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-6 col-lg-3"><div class="dash-stat"><div class="dash-stat-value"><?= (int) ($stats['total'] ?? 0) ?></div><div class="dash-stat-label">Total Reports</div></div></div>
    <div class="col-6 col-lg-3"><div class="dash-stat"><div class="dash-stat-value"><?= (int) ($stats['active_count'] ?? 0) ?></div><div class="dash-stat-label">Active</div></div></div>
    <div class="col-6 col-lg-3"><div class="dash-stat"><div class="dash-stat-value"><?= (int) ($stats['resolved_count'] ?? 0) ?></div><div class="dash-stat-label">Resolved</div></div></div>
    <div class="col-6 col-lg-3"><div class="dash-stat"><div class="dash-stat-value"><?= $unreadChats ?></div><div class="dash-stat-label">Unread Messages</div></div></div>
  </div>

  <ul class="nav nav-tabs mb-3" id="dashboard-tabs" role="tablist">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-reports" type="button">My Reports</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-claims" type="button">Active Claims</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-notifications" type="button" id="notifications">Notifications</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-chat-history" type="button">Chat History</button></li>
  </ul>

  <div class="tab-content">
    <div class="tab-pane fade show active" id="tab-reports">
      <div class="table-responsive admin-table">
        <table class="table table-dark table-hover mb-0">
          <thead><tr><th>Type</th><th>Category</th><th>Title</th><th>Campus</th><th>Date</th><th>Status</th><th>Views</th><th>Actions</th></tr></thead>
          <tbody>
            <?php foreach ($myReports as $r): ?>
              <tr>
                <td><span class="<?= report_type_badge($r['type']) ?>"><?= strtoupper(h($r['type'])) ?></span></td>
                <td><?= h(REPORT_CATEGORIES[$r['category']] ?? $r['category']) ?></td>
                <td><?= h($r['title']) ?></td>
                <td><?= h(report_location($r)) ?></td>
                <td><?= h(human_date($r['date_occurred'])) ?></td>
                <td><span class="badge badge-status <?= status_badge_class($r['status']) ?>"><?= h(ucfirst($r['status'])) ?></span></td>
                <td><?= (int) $r['views'] ?></td>
                <td>
                  <a class="btn btn-sm btn-outline-custom" href="<?= BASE_URL ?>report-detail.php?id=<?= (int) $r['id'] ?>">View</a>
                  <a class="btn btn-sm btn-outline-custom" href="<?= BASE_URL ?>create-report.php?edit=<?= (int) $r['id'] ?>">Edit</a>
                  <form method="post" class="d-inline delete-form">
                    <input type="hidden" name="action" value="delete_report">
                    <input type="hidden" name="report_id" value="<?= (int) $r['id'] ?>">
                    <button class="btn btn-sm btn-danger btn-delete" type="submit">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (!$myReports): ?><tr><td colspan="8" class="text-center">No reports yet.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="tab-pane fade" id="tab-claims">
      <div class="table-responsive admin-table">
        <table class="table table-dark table-hover mb-0">
          <thead><tr><th>Claimant</th><th>Report</th><th>Date</th><th>Status</th><th>Code</th><th>Action</th></tr></thead>
          <tbody>
            <?php foreach ($claims as $c): ?>
              <tr>
                <td><?= h($c['claimant_name']) ?></td>
                <td><?= h($c['report_title']) ?></td>
                <td><?= h(human_datetime($c['created_at'])) ?></td>
                <td><?= h($c['status']) ?></td>
                <td><?= $c['status'] === 'code_sent' ? h($c['verification_code']) : '-' ?></td>
                <td>
                  <?php if ($c['status'] === 'code_sent'): ?>
                    <a class="btn btn-sm btn-accent" href="<?= BASE_URL ?>verify-resolution.php?claim_id=<?= (int) $c['id'] ?>">Go to Verify Page</a>
                  <?php else: ?>-
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (!$claims): ?><tr><td colspan="6" class="text-center">No active claims.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="tab-pane fade" id="tab-notifications">
      <div class="d-flex justify-content-end mb-2"><button class="btn btn-outline-custom btn-sm" id="mark-notifications-read">Mark All Read</button></div>
      <div class="d-flex flex-column gap-2">
        <?php foreach ($notifications as $n): ?>
          <a href="<?= BASE_URL . h($n['link'] ?: 'dashboard.php') ?>" class="report-card p-3 text-decoration-none d-flex justify-content-between align-items-center">
            <div>
              <strong><?= h(ucfirst($n['type'] ?: 'Notice')) ?></strong>
              <p class="mb-0 small text-muted"><?= h($n['message']) ?></p>
            </div>
            <div class="d-flex align-items-center gap-2">
              <?php if (!(int) $n['is_read']): ?><span class="badge bg-danger rounded-pill">new</span><?php endif; ?>
              <small class="text-muted"><?= h(human_datetime($n['created_at'])) ?></small>
            </div>
          </a>
        <?php endforeach; ?>
        <?php if (!$notifications): ?><div class="report-card p-3">No notifications.</div><?php endif; ?>
      </div>
    </div>

    <div class="tab-pane fade" id="tab-chat-history">
      <div class="d-flex flex-column gap-2">
        <?php foreach ($chatHistory as $row):
          $u = $pdo->prepare('SELECT name FROM users WHERE id=?');
          $u->execute([$row['other_user_id']]);
          $otherName = (string) $u->fetchColumn();
        ?>
          <a href="<?= BASE_URL ?>chat.php?report_id=<?= (int) $row['report_id'] ?>&with=<?= (int) $row['other_user_id'] ?>" class="report-card p-3 text-decoration-none d-flex justify-content-between">
            <div>
              <strong><?= h($otherName ?: 'User') ?></strong>
              <div class="small text-muted">Report #<?= (int) $row['report_id'] ?></div>
            </div>
            <div class="text-end">
              <?php if ((int) $row['unread'] > 0): ?><span class="notif-badge position-static d-inline-flex"><?= (int) $row['unread'] ?></span><?php endif; ?>
              <div class="small text-muted"><?= h(human_datetime($row['last_time'])) ?></div>
            </div>
          </a>
        <?php endforeach; ?>
        <?php if (!$chatHistory): ?><div class="report-card p-3">No conversations yet.</div><?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
