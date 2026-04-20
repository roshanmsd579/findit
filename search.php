<?php
require_once __DIR__ . '/includes/db.php';
$pageTitle = 'Search - ' . SITE_NAME;

$q = trim($_GET['q'] ?? '');
$type = $_GET['type'] ?? 'all';
$category = $_GET['category'] ?? 'all';
$location = $_GET['location'] ?? 'all';
$status = $_GET['status'] ?? 'active';

$where = [];
$params = [];
if ($q !== '') {
    $where[] = '(r.title LIKE ? OR r.description LIKE ? OR r.campus_location LIKE ? OR u.name LIKE ?)';
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like, $like);
}
if (in_array($type, ['lost', 'found'], true)) {
    $where[] = 'r.type = ?';
    $params[] = $type;
}
if (array_key_exists($category, REPORT_CATEGORIES)) {
    $where[] = 'r.category = ?';
    $params[] = $category;
}
if (in_array($location, CAMPUS_LOCATIONS, true)) {
    $where[] = 'r.campus_location = ?';
    $params[] = $location;
}
if (in_array($status, ['active', 'claimed', 'verified', 'resolved', 'disputed', 'closed', 'all'], true) && $status !== 'all') {
    $where[] = 'r.status = ?';
    $params[] = $status;
}

$sql = "SELECT r.*, u.name AS user_name
        FROM reports r
        JOIN users u ON r.user_id = u.id";
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY r.created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>
<div class="container py-5">
  <div class="row g-4">
    <div class="col-lg-4 col-xl-3">
      <div class="filter-panel">
        <h5 class="mb-3">Search Filters</h5>
        <form id="search-filter-form" method="get">
          <div class="mb-3">
            <label class="form-label-custom">Keyword</label>
            <input id="search-input" type="text" name="q" class="form-custom" value="<?= h($q) ?>" placeholder="Title, category, location">
          </div>
          <div class="mb-3">
            <label class="form-label-custom">Type</label>
            <select class="form-custom" name="type" id="type-filter">
              <option value="all">All</option>
              <option value="lost" <?= $type === 'lost' ? 'selected' : '' ?>>Lost</option>
              <option value="found" <?= $type === 'found' ? 'selected' : '' ?>>Found</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label-custom">Category</label>
            <select class="form-custom" name="category" id="category-filter">
              <option value="all">All Categories</option>
              <?php foreach (REPORT_CATEGORIES as $key => $label): ?><option value="<?= h($key) ?>" <?= $category === $key ? 'selected' : '' ?>><?= h($label) ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label-custom">Campus Location</label>
            <select class="form-custom" name="location" id="location-filter">
              <option value="all">Any</option>
              <?php foreach (CAMPUS_LOCATIONS as $loc): ?><option value="<?= h($loc) ?>" <?= $location === $loc ? 'selected' : '' ?>><?= h($loc) ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label-custom">Status</label>
            <select class="form-custom" name="status" id="status-filter">
              <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
              <option value="claimed" <?= $status === 'claimed' ? 'selected' : '' ?>>Claimed</option>
              <option value="verified" <?= $status === 'verified' ? 'selected' : '' ?>>Verified</option>
              <option value="resolved" <?= $status === 'resolved' ? 'selected' : '' ?>>Resolved</option>
              <option value="disputed" <?= $status === 'disputed' ? 'selected' : '' ?>>Disputed</option>
              <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>All</option>
            </select>
          </div>
          <div class="d-grid gap-2">
            <button class="btn btn-accent" type="submit">Apply</button>
            <button class="btn btn-outline-custom" type="button" id="reset-filters">Reset Filters</button>
          </div>
        </form>
      </div>
    </div>

    <div class="col-lg-8 col-xl-9">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="section-title mb-0">Search Results</h1>
        <span id="results-count" class="small text-muted"><?= count($results) ?> result(s)</span>
      </div>
      <div id="search-results" class="row g-4">
        <?php foreach ($results as $row): ?>
          <div class="col-md-6 search-item" data-type="<?= h($row['type']) ?>" data-category="<?= h($row['category']) ?>" data-status="<?= h($row['status']) ?>" data-location="<?= h(report_location($row)) ?>">
            <a href="<?= BASE_URL ?>report-detail.php?id=<?= (int) $row['id'] ?>" class="report-card h-100">
              <?php if ($row['photo']): ?><img src="<?= h(UPLOAD_URL . basename($row['photo'])) ?>" class="report-card-img" alt="<?= h($row['title']) ?>"><?php else: ?><div class="report-card-placeholder">📌</div><?php endif; ?>
              <div class="p-3">
                <div class="d-flex justify-content-between mb-2">
                  <span class="<?= report_type_badge($row['type']) ?>"><?= strtoupper(h($row['type'])) ?></span>
                  <span class="badge badge-status <?= status_badge_class($row['status']) ?>"><?= h(ucfirst($row['status'])) ?></span>
                </div>
                <h5 class="mb-2"><?= h($row['title']) ?></h5>
                <p class="small text-muted mb-2"><?= h(mb_strimwidth($row['description'], 0, 95, '...')) ?></p>
                <div class="small text-muted"><i class="bi bi-geo-alt"></i> <?= h(report_location($row)) ?></div>
              </div>
            </a>
          </div>
        <?php endforeach; ?>
      </div>
      <div id="empty-state" class="report-card p-4 text-center mt-4 <?= $results ? 'd-none' : '' ?>">
        <h5>No matching reports found</h5>
        <p class="text-muted mb-0">Try changing filters or keywords.</p>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
