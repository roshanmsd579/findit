<?php
session_start();

if (empty($_SESSION['user_id'])) {
    header('Location: /findit/login.php');
    exit;
}

if (($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: /findit/dashboard.php');
    exit;
}

// Placeholder for DB-backed role verification when persistence is integrated.
// Example: query users table by $_SESSION['user_id'] and validate elevated privileges.

$pageTitle = 'Admin Panel - FindIt';

$rows = [
    ['id' => 5001, 'title' => 'MacBook charger', 'type' => 'found', 'status' => 'pending', 'user' => 'R. Cruz'],
    ['id' => 5002, 'title' => 'Brown wallet', 'type' => 'lost', 'status' => 'review', 'user' => 'L. Santos'],
    ['id' => 5003, 'title' => 'ID card holder', 'type' => 'found', 'status' => 'approved', 'user' => 'A. Reyes'],
    ['id' => 5004, 'title' => 'Earbuds case', 'type' => 'lost', 'status' => 'pending', 'user' => 'M. Lim']
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
        <p class="section-tag mb-1">Administration</p>
        <h2 class="mb-0">Moderation Control Panel</h2>
      </div>
      <button class="btn btn-primary-custom">Export Report Log</button>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-md-3"><div class="dash-stat"><div class="dash-stat-value">47</div><div class="dash-stat-label">Pending</div></div></div>
      <div class="col-md-3"><div class="dash-stat"><div class="dash-stat-value">19</div><div class="dash-stat-label">Review</div></div></div>
      <div class="col-md-3"><div class="dash-stat"><div class="dash-stat-value">362</div><div class="dash-stat-label">Approved</div></div></div>
      <div class="col-md-3"><div class="dash-stat"><div class="dash-stat-value">14</div><div class="dash-stat-label">Flagged</div></div></div>
    </div>

    <div class="admin-table table-responsive">
      <table class="table table-dark align-middle mb-0">
        <thead>
          <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Type</th>
            <th>Status</th>
            <th>Reporter</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $row): ?>
            <tr>
              <td>#<?= $row['id']; ?></td>
              <td><?= htmlspecialchars($row['title']); ?></td>
              <td><span class="<?= $row['type'] === 'lost' ? 'badge-lost' : 'badge-found'; ?>"><?= strtoupper($row['type']); ?></span></td>
              <td><span class="badge-resolved"><?= strtoupper($row['status']); ?></span></td>
              <td><?= htmlspecialchars($row['user']); ?></td>
              <td>
                <div class="d-flex gap-2">
                  <button class="btn btn-sm btn-outline-custom">Approve</button>
                  <button class="btn btn-sm btn-outline-custom">Flag</button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
