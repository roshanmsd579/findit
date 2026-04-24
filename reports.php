<?php
require_once __DIR__ . '/includes/db.php';
$pageTitle = 'Reports - ' . SITE_NAME;

$type = $_GET['type'] ?? 'all';
$category = $_GET['category'] ?? 'all';
$status = $_GET['status'] ?? 'active';

$where = [];
$params = [];
if (in_array($type, ['lost', 'found'], true)) {
    $where[] = 'r.type = ?';
    $params[] = $type;
}
if (array_key_exists($category, REPORT_CATEGORIES)) {
    $where[] = 'r.category = ?';
    $params[] = $category;
}
if (in_array($status, ['active', 'claimed', 'verified', 'resolved', 'disputed', 'closed'], true)) {
    $where[] = 'r.status = ?';
    $params[] = $status;
}

$sql = "SELECT r.*, u.name AS user_name FROM reports r JOIN users u ON r.user_id = u.id";
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY r.created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reports = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>
<div class="container py-5">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
      <span class="section-tag">Campus Reports</span>
      <h1 class="section-title mb-0">All Lost &amp; Found Cases</h1>
    </div>
    <div class="d-flex flex-wrap gap-2" data-filter-group data-target="#report-grid .report-card">
      <button class="filter-tab active" data-type="all" data-category="all">All</button>
      <button class="filter-tab" data-type="lost" data-category="all">Lost</button>
      <button class="filter-tab" data-type="found" data-category="all">Found</button>
      <button class="filter-tab" data-type="all" data-category="id_card">ID Cards</button>
      <button class="filter-tab" data-type="all" data-category="phone">Phones</button>
      <button class="filter-tab" data-type="all" data-category="laptop">Laptops</button>
    </div>
  </div>

  <div class="row g-4" id="report-grid">
    <?php foreach ($reports as $report): ?>
      <div class="col-md-6 col-lg-4">
        <a href="<?= BASE_URL ?>report-detail.php?id=<?= (int) $report['id'] ?>" class="report-card h-100" data-type="<?= h($report['type']) ?>" data-category="<?= h($report['category']) ?>">
          <?php if ($report['photo']): ?>
            <img src="<?= h(UPLOAD_URL . basename($report['photo'])) ?>" class="report-card-img" alt="<?= h($report['title']) ?>">
          <?php else: ?>
            <div class="report-card-placeholder"><?= $report['type'] === 'lost' ? '🔍' : '📦' ?></div>
          <?php endif; ?>
          <div class="p-3">
            <div class="d-flex justify-content-between mb-2">
              <span class="<?= report_type_badge($report['type']) ?>"><?= strtoupper(h($report['type'])) ?></span>
              <span class="badge badge-status <?= status_badge_class($report['status']) ?>"><?= h(ucfirst($report['status'])) ?></span>
            </div>
            <h5><?= h($report['title']) ?></h5>
            <p class="small text-muted mb-2"><?= h(mb_strimwidth($report['description'], 0, 100, '...')) ?></p>
            <div class="small text-muted d-flex justify-content-between">
              <span><i class="bi bi-geo-alt"></i> <?= h(report_location($report)) ?></span>
              <span><?= h(human_date($report['date_occurred'])) ?></span>
            </div>
          </div>
        </a>
      </div>
    <?php endforeach; ?>
    <?php if (!$reports): ?>
      <div class="col-12"><div class="report-card p-4 text-center">No reports found.</div></div>
    <?php endif; ?>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
