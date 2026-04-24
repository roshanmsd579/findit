<?php
require_once __DIR__ . '/includes/db.php';
require_login();
if (!is_admin()) {
    redirect('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'delete_report') {
        $pdo->prepare('DELETE FROM reports WHERE id = ?')->execute([(int) $_POST['report_id']]);
    }
    if ($action === 'reject_claim') {
        $claimId = (int) $_POST['claim_id'];
        $pdo->prepare("UPDATE claims SET status='rejected' WHERE id=?")->execute([$claimId]);
    }
    if ($action === 'resolve_dispute') {
        $pdo->prepare("UPDATE disputes SET status='resolved' WHERE id=?")->execute([(int) $_POST['dispute_id']]);
    }
    if ($action === 'add_admin_note') {
        $pdo->prepare('UPDATE disputes SET admin_note=? WHERE id=?')->execute([trim($_POST['admin_note'] ?? ''), (int) $_POST['dispute_id']]);
    }
    if ($action === 'delete_user') {
        $userId = (int) $_POST['user_id'];
        if ($userId !== $_SESSION['user_id']) { // Prevent admin from deleting themselves
            $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$userId]);
        }
    }
    redirect('admin.php');
}

$stats = [
  'users' => (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
  'reports' => (int) $pdo->query('SELECT COUNT(*) FROM reports')->fetchColumn(),
  'active' => (int) $pdo->query("SELECT COUNT(*) FROM reports WHERE status='active'")->fetchColumn(),
  'resolved' => (int) $pdo->query("SELECT COUNT(*) FROM reports WHERE status='resolved'")->fetchColumn(),
  'disputes' => (int) $pdo->query('SELECT COUNT(*) FROM disputes')->fetchColumn(),
  'claims' => (int) $pdo->query('SELECT COUNT(*) FROM claims')->fetchColumn(),
];

$statusFilter = $_GET['status'] ?? 'all';
$rSql = "SELECT r.*, u.name AS user_name FROM reports r JOIN users u ON r.user_id=u.id";
$rParams = [];
if ($statusFilter !== 'all') {
    $rSql .= ' WHERE r.status=?';
    $rParams[] = $statusFilter;
}
$rSql .= ' ORDER BY r.created_at DESC';
$reportsStmt = $pdo->prepare($rSql);
$reportsStmt->execute($rParams);
$reports = $reportsStmt->fetchAll();

$users = $pdo->query('SELECT u.*, (SELECT COUNT(*) FROM reports r WHERE r.user_id=u.id) AS report_count FROM users u ORDER BY u.created_at DESC')->fetchAll();
$claims = $pdo->query('SELECT c.*, r.title AS report_title, ru.name AS reporter_name, cu.name AS claimant_name FROM claims c JOIN reports r ON c.report_id=r.id JOIN users ru ON r.user_id=ru.id JOIN users cu ON c.claimant_id=cu.id ORDER BY c.created_at DESC')->fetchAll();
$disputes = $pdo->query('SELECT d.*, c.report_id, r.title AS report_title, u.name AS raised_by_name FROM disputes d JOIN claims c ON d.claim_id=c.id JOIN reports r ON c.report_id=r.id JOIN users u ON d.raised_by=u.id ORDER BY d.created_at DESC')->fetchAll();
$activity = $pdo->query("(SELECT 'report' as kind, CONCAT('Report #', id, ': ', title) as text, created_at FROM reports)
  UNION ALL
  (SELECT 'claim' as kind, CONCAT('Claim #', id, ' submitted') as text, created_at FROM claims)
  UNION ALL
  (SELECT 'message' as kind, CONCAT('Chat message #', id) as text, created_at FROM chat_messages)
  ORDER BY created_at DESC LIMIT 20")->fetchAll();

$pageTitle = 'Admin Panel - ' . SITE_NAME;
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>
<div class="container py-5">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div><span class="section-tag">Administration</span><h1 class="section-title mb-0">Campus Control Panel</h1><small class="text-muted"><?= h(UNIVERSITY) ?></small></div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-6 col-lg-2"><div class="dash-stat"><div class="dash-stat-value"><?= $stats['users'] ?></div><div class="dash-stat-label">Users</div></div></div>
    <div class="col-6 col-lg-2"><div class="dash-stat"><div class="dash-stat-value"><?= $stats['reports'] ?></div><div class="dash-stat-label">Reports</div></div></div>
    <div class="col-6 col-lg-2"><div class="dash-stat"><div class="dash-stat-value"><?= $stats['active'] ?></div><div class="dash-stat-label">Active</div></div></div>
    <div class="col-6 col-lg-2"><div class="dash-stat"><div class="dash-stat-value"><?= $stats['resolved'] ?></div><div class="dash-stat-label">Resolved</div></div></div>
    <div class="col-6 col-lg-2"><div class="dash-stat"><div class="dash-stat-value"><?= $stats['disputes'] ?></div><div class="dash-stat-label">Disputes</div></div></div>
    <div class="col-6 col-lg-2"><div class="dash-stat"><div class="dash-stat-value"><?= $stats['claims'] ?></div><div class="dash-stat-label">Claims</div></div></div>
  </div>

  <ul class="nav nav-tabs mb-3">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#admin-reports" type="button">Reports</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#admin-users" type="button">Users</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#admin-claims" type="button">Claims</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#admin-disputes" type="button">Disputes</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#admin-activity" type="button">Activity</button></li>
  </ul>

  <div class="tab-content">
    <div class="tab-pane fade show active" id="admin-reports">
      <form method="get" class="mb-2" style="max-width:240px;">
        <select name="status" class="form-custom" onchange="this.form.submit()">
          <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>All Statuses</option>
          <?php foreach (['active','claimed','verified','resolved','disputed','closed'] as $st): ?><option value="<?= h($st) ?>" <?= $statusFilter === $st ? 'selected' : '' ?>><?= h(ucfirst($st)) ?></option><?php endforeach; ?>
        </select>
      </form>
      <div class="table-responsive admin-table"><table class="table table-dark mb-0"><thead><tr><th>ID</th><th>Type</th><th>Category</th><th>Title</th><th>User</th><th>Campus</th><th>Status</th><th>Date</th><th>Views</th><th>Actions</th></tr></thead><tbody>
        <?php foreach ($reports as $r): ?>
          <tr><td><?= (int) $r['id'] ?></td><td><?= h($r['type']) ?></td><td><?= h($r['category']) ?></td><td><?= h($r['title']) ?></td><td><?= h($r['user_name']) ?></td><td><?= h(report_location($r)) ?></td><td><?= h($r['status']) ?></td><td><?= h(human_date($r['date_occurred'])) ?></td><td><?= (int) $r['views'] ?></td><td>
            <a class="btn btn-sm btn-outline-custom" href="<?= BASE_URL ?>report-detail.php?id=<?= (int) $r['id'] ?>">View</a>
            <form method="post" class="d-inline delete-form"><input type="hidden" name="action" value="delete_report"><input type="hidden" name="report_id" value="<?= (int) $r['id'] ?>"><button class="btn btn-sm btn-danger btn-delete" type="submit">Delete</button></form>
          </td></tr>
        <?php endforeach; ?>
      </tbody></table></div>
    </div>

    <div class="tab-pane fade" id="admin-users">
      <div class="table-responsive admin-table"><table class="table table-dark mb-0"><thead><tr><th>ID</th><th>Student ID</th><th>Name</th><th>Email</th><th>Role</th><th>Department</th><th>Reports</th><th>Joined</th><th>Actions</th></tr></thead><tbody>
        <?php foreach ($users as $u): ?>
          <tr><td><?= (int) $u['id'] ?></td><td><?= h($u['student_id']) ?></td><td><?= h($u['name']) ?></td><td><?= h($u['email']) ?></td><td><span class="badge badge-status <?= $u['role'] === 'admin' ? 'badge-disputed' : 'badge-active' ?>"><?= h(ucfirst($u['role'])) ?></span></td><td><?= h($u['department']) ?></td><td><?= (int) $u['report_count'] ?></td><td><?= h(human_date($u['created_at'])) ?></td><td>
            <?php if ($u['id'] !== $_SESSION['user_id']): ?>
            <form method="post" class="d-inline delete-form"><input type="hidden" name="action" value="delete_user"><input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>"><button class="btn btn-sm btn-danger btn-delete" type="submit">Delete</button></form>
            <?php endif; ?>
          </td></tr>
        <?php endforeach; ?>
      </tbody></table></div>
    </div>

    <div class="tab-pane fade" id="admin-claims">
      <div class="table-responsive admin-table"><table class="table table-dark mb-0"><thead><tr><th>ID</th><th>Report</th><th>Reporter</th><th>Claimant</th><th>Answer Correct</th><th>Code</th><th>Status</th><th>Date</th><th>Action</th></tr></thead><tbody>
      <?php foreach ($claims as $c): ?>
        <tr><td><?= (int) $c['id'] ?></td><td><?= h($c['report_title']) ?></td><td><?= h($c['reporter_name']) ?></td><td><?= h($c['claimant_name']) ?></td><td><?= (int) $c['answer_correct'] ? 'Yes' : 'No' ?></td><td><?= h($c['verification_code']) ?></td><td><?= h($c['status']) ?></td><td><?= h(human_datetime($c['created_at'])) ?></td><td>
          <form method="post"><input type="hidden" name="action" value="reject_claim"><input type="hidden" name="claim_id" value="<?= (int) $c['id'] ?>"><button class="btn btn-sm btn-danger" type="submit">Reject</button></form>
        </td></tr>
      <?php endforeach; ?>
      </tbody></table></div>
    </div>

    <div class="tab-pane fade" id="admin-disputes">
      <div class="table-responsive admin-table"><table class="table table-dark mb-0"><thead><tr><th>ID</th><th>Report</th><th>Raised By</th><th>Reason</th><th>Status</th><th>Admin Note</th><th>Actions</th></tr></thead><tbody>
      <?php foreach ($disputes as $d): ?>
        <tr>
          <td><?= (int) $d['id'] ?></td><td><?= h($d['report_title']) ?></td><td><?= h($d['raised_by_name']) ?></td><td><?= h(mb_strimwidth($d['reason'],0,60,'...')) ?></td><td><?= h($d['status']) ?></td>
          <td>
            <form method="post" class="d-flex gap-2">
              <input type="hidden" name="action" value="add_admin_note">
              <input type="hidden" name="dispute_id" value="<?= (int) $d['id'] ?>">
              <input class="form-control form-control-sm" name="admin_note" value="<?= h($d['admin_note'] ?? '') ?>">
              <button class="btn btn-sm btn-outline-custom">Save</button>
            </form>
          </td>
          <td>
            <form method="post"><input type="hidden" name="action" value="resolve_dispute"><input type="hidden" name="dispute_id" value="<?= (int) $d['id'] ?>"><button class="btn btn-sm btn-accent">Resolve</button></form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody></table></div>
    </div>

    <div class="tab-pane fade" id="admin-activity">
      <div class="d-flex flex-column gap-2">
        <?php foreach ($activity as $a): ?>
          <div class="report-card p-3 d-flex justify-content-between"><div><strong><?= h(ucfirst($a['kind'])) ?></strong> - <?= h($a['text']) ?></div><small class="text-muted"><?= h(human_datetime($a['created_at'])) ?></small></div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
