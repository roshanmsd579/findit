<?php
session_start();

if (empty($_SESSION['user_id'])) {
    header('Location: /findit/login.php');
    exit;
}

$pageTitle = 'Dashboard - FindIt';
$name = $_SESSION['name'] ?? 'Member';

$myReports = [
    ['id' => 345, 'title' => 'Black Backpack', 'type' => 'lost', 'status' => 'open', 'updated' => '2h ago'],
    ['id' => 352, 'title' => 'Office ID Badge', 'type' => 'found', 'status' => 'matched', 'updated' => '1d ago'],
    ['id' => 359, 'title' => 'Prescription Glasses', 'type' => 'lost', 'status' => 'review', 'updated' => '3d ago']
];
?>
<!doctype html>
<html lang="en">
<?php include __DIR__ . '/includes/header.php'; ?>
<body>
<?php include __DIR__ . '/includes/navbar.php'; ?>

<section class="py-5 mt-4">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
      <div>
        <p class="section-tag mb-1">Member Area</p>
        <h2 class="mb-0">Welcome, <?= htmlspecialchars($name); ?></h2>
      </div>
      <a href="/findit/create-report.php" class="btn btn-primary-custom">+ New Report</a>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-md-4"><div class="dash-stat"><div class="dash-stat-value">12</div><div class="dash-stat-label">Total Reports</div></div></div>
      <div class="col-md-4"><div class="dash-stat"><div class="dash-stat-value">4</div><div class="dash-stat-label">Recovered Items</div></div></div>
      <div class="col-md-4"><div class="dash-stat"><div class="dash-stat-value">3</div><div class="dash-stat-label">Unread Messages</div></div></div>
    </div>

    <div class="bg-card p-4">
      <h5 class="mb-3">My Recent Reports</h5>
      <div class="table-responsive">
        <table class="table align-middle mb-0 text-light">
          <thead>
            <tr>
              <th class="text-muted-custom">Report</th>
              <th class="text-muted-custom">Type</th>
              <th class="text-muted-custom">Status</th>
              <th class="text-muted-custom">Updated</th>
              <th class="text-muted-custom">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($myReports as $item): ?>
              <tr>
                <td>#<?= $item['id']; ?> - <?= htmlspecialchars($item['title']); ?></td>
                <td>
                  <span class="<?= $item['type'] === 'lost' ? 'badge-lost' : 'badge-found'; ?>"><?= strtoupper($item['type']); ?></span>
                </td>
                <td><span class="badge-resolved"><?= strtoupper($item['status']); ?></span></td>
                <td class="text-muted-custom"><?= htmlspecialchars($item['updated']); ?></td>
                <td><a class="btn btn-sm btn-outline-custom" href="/findit/report-detail.php?id=<?= $item['id']; ?>">View</a></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
