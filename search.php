<?php
session_start();
$pageTitle = 'Search Reports - FindIt';

$reports = [
    ['title' => 'Black Backpack', 'type' => 'lost', 'category' => 'electronics', 'location' => 'North Campus Gate', 'description' => 'Contains laptop and notebooks.'],
    ['title' => 'Student ID Card', 'type' => 'found', 'category' => 'documents', 'location' => 'Library 2nd Floor', 'description' => 'Found near reading tables.'],
    ['title' => 'Wireless Earbuds', 'type' => 'lost', 'category' => 'electronics', 'location' => 'Coffee Street', 'description' => 'White charging case with initials.'],
    ['title' => 'Car Key Fob', 'type' => 'found', 'category' => 'accessories', 'location' => 'Parking Area C', 'description' => 'Toyota key with blue tag.'],
    ['title' => 'Passport Sleeve', 'type' => 'lost', 'category' => 'documents', 'location' => 'Airport Shuttle', 'description' => 'Dark blue sleeve with passport and boarding pass.'],
    ['title' => 'Sports Bottle', 'type' => 'found', 'category' => 'personal', 'location' => 'Community Gym', 'description' => 'Metal bottle with stickers.']
];
?>
<!doctype html>
<html lang="en">
<?php include __DIR__ . '/includes/header.php'; ?>
<body>
<?php include __DIR__ . '/includes/navbar.php'; ?>

<section class="py-5 mt-4">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-3">
        <aside class="filter-panel">
          <h6>Search Tips</h6>
          <p class="small text-muted-custom">Search by title, details, or location. Results update automatically as you type.</p>
          <h6 class="mt-4">Quick Filters</h6>
          <div class="d-flex flex-wrap gap-2" data-filter-group data-target="#search-results .report-card">
            <button class="filter-tab active" data-type="all">All</button>
            <button class="filter-tab" data-type="lost">Lost</button>
            <button class="filter-tab" data-type="found">Found</button>
          </div>
        </aside>
      </div>

      <div class="col-lg-9">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-3">
          <div>
            <p class="section-tag mb-1">Search Directory</p>
            <h2 class="mb-0">Find Matching Reports</h2>
          </div>
          <div class="text-muted-custom">Results: <strong id="results-count"><?= count($reports); ?></strong></div>
        </div>

        <div class="mb-4">
          <input id="search-input" type="text" class="form-control-custom" placeholder="Try: backpack, library, key, ID card...">
        </div>

        <div class="row g-4" id="search-results">
          <?php foreach ($reports as $report): ?>
            <div class="col-md-6 search-card">
              <article class="report-card h-100 p-3" data-type="<?= $report['type']; ?>" data-category="<?= $report['category']; ?>" data-title="<?= htmlspecialchars($report['title']); ?>" data-description="<?= htmlspecialchars($report['description']); ?>" data-location="<?= htmlspecialchars($report['location']); ?>">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <span class="<?= $report['type'] === 'lost' ? 'badge-lost' : 'badge-found'; ?>"><?= strtoupper($report['type']); ?></span>
                  <small class="text-muted-custom"><?= htmlspecialchars($report['category']); ?></small>
                </div>
                <h5 class="mb-2"><?= htmlspecialchars($report['title']); ?></h5>
                <p class="text-muted-custom mb-2"><?= htmlspecialchars($report['description']); ?></p>
                <p class="mb-0 small text-muted-custom"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($report['location']); ?></p>
              </article>
            </div>
          <?php endforeach; ?>
        </div>

        <div id="empty-state" class="bg-card p-5 text-center d-none mt-4">
          <i class="bi bi-search fs-1 d-block mb-3 text-muted-custom"></i>
          <h5>No matching reports found</h5>
          <p class="text-muted-custom mb-0">Try a broader keyword or remove specific filters.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
