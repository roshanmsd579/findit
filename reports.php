<?php
session_start();
$pageTitle = 'All Reports - FindIt';

$reports = [
    ['id' => 101, 'title' => 'Black Backpack', 'type' => 'lost', 'category' => 'electronics', 'location' => 'Campus Gate'],
    ['id' => 102, 'title' => 'Office ID Badge', 'type' => 'found', 'category' => 'documents', 'location' => 'Business District'],
    ['id' => 103, 'title' => 'Silver Ring', 'type' => 'lost', 'category' => 'accessories', 'location' => 'Food Court'],
    ['id' => 104, 'title' => 'Car Keys', 'type' => 'found', 'category' => 'accessories', 'location' => 'Parking Lot A'],
    ['id' => 105, 'title' => 'Tablet Device', 'type' => 'lost', 'category' => 'electronics', 'location' => 'Public Library'],
    ['id' => 106, 'title' => 'Wallet with Cards', 'type' => 'found', 'category' => 'documents', 'location' => 'Central Park'],
    ['id' => 107, 'title' => 'Water Flask', 'type' => 'lost', 'category' => 'personal', 'location' => 'Gym Hall'],
    ['id' => 108, 'title' => 'Prescription Glasses', 'type' => 'found', 'category' => 'personal', 'location' => 'Train Station']
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
        <p class="section-tag mb-1">Repository</p>
        <h2 class="mb-0">All Community Reports</h2>
      </div>
      <div class="d-flex gap-2" data-filter-group data-target="#reports-grid .report-card">
        <button class="filter-tab active" data-type="all">All</button>
        <button class="filter-tab" data-type="lost">Lost</button>
        <button class="filter-tab" data-type="found">Found</button>
      </div>
    </div>

    <div class="row g-4" id="reports-grid">
      <?php foreach ($reports as $report): ?>
        <div class="col-md-6 col-xl-3">
          <a href="/findit/report-detail.php?id=<?= $report['id']; ?>" class="text-decoration-none">
            <article class="report-card h-100 p-3" data-type="<?= $report['type']; ?>" data-category="<?= $report['category']; ?>">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="<?= $report['type'] === 'lost' ? 'badge-lost' : 'badge-found'; ?>"><?= strtoupper($report['type']); ?></span>
                <small class="text-muted-custom">#<?= $report['id']; ?></small>
              </div>
              <h6><?= htmlspecialchars($report['title']); ?></h6>
              <p class="small mb-0 text-muted-custom"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($report['location']); ?></p>
            </article>
          </a>
        </div>
      <?php endforeach; ?>
    </div>

    <nav class="mt-5" aria-label="Reports pagination">
      <ul class="pagination justify-content-center">
        <li class="page-item disabled"><span class="page-link bg-dark text-light border-secondary">Previous</span></li>
        <li class="page-item active"><span class="page-link bg-danger border-danger">1</span></li>
        <li class="page-item"><a class="page-link bg-dark text-light border-secondary" href="#">2</a></li>
        <li class="page-item"><a class="page-link bg-dark text-light border-secondary" href="#">3</a></li>
        <li class="page-item"><a class="page-link bg-dark text-light border-secondary" href="#">Next</a></li>
      </ul>
    </nav>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
