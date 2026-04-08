<?php
session_start();
$pageTitle = 'FindIt - Lost & Found Portal';

$featuredReports = [
    [
        'id' => 1,
        'title' => 'Black Backpack with Laptop',
        'type' => 'lost',
        'category' => 'electronics',
        'location' => 'North Campus Gate',
        'time' => '2 hours ago',
        'icon' => 'bi-backpack4'
    ],
    [
        'id' => 2,
        'title' => 'National ID Card Holder',
        'type' => 'found',
        'category' => 'documents',
        'location' => 'City Library',
        'time' => 'Today, 9:40 AM',
        'icon' => 'bi-person-vcard'
    ],
    [
        'id' => 3,
        'title' => 'Silver Wrist Watch',
        'type' => 'lost',
        'category' => 'accessories',
        'location' => 'Metro Station Exit B',
        'time' => 'Yesterday',
        'icon' => 'bi-watch'
    ],
    [
        'id' => 4,
        'title' => 'Blue Water Flask',
        'type' => 'found',
        'category' => 'personal',
        'location' => 'Fitness Center',
        'time' => 'Yesterday',
        'icon' => 'bi-cup-straw'
    ]
];
?>
<!doctype html>
<html lang="en">
<?php include __DIR__ . '/includes/header.php'; ?>
<body>
<?php include __DIR__ . '/includes/navbar.php'; ?>

<section class="hero-section">
  <div class="hero-grid-overlay"></div>
  <div class="container position-relative">
    <div class="row align-items-center g-5">
      <div class="col-lg-7">
        <p class="section-tag">Community-powered Lost &amp; Found</p>
        <h1 class="display-4 mb-3">Reconnect People With What Matters.</h1>
        <p class="lead text-muted-custom mb-4">FindIt helps neighborhoods, campuses, and workplaces report lost and found items faster with smart search and trusted updates.</p>
        <div class="d-flex flex-wrap gap-3">
          <a href="/findit/create-report.php" class="btn btn-primary-custom btn-lg px-4">Report an Item</a>
          <a href="/findit/search.php" class="btn btn-outline-custom btn-lg px-4">Search Reports</a>
        </div>
      </div>
      <div class="col-lg-5">
        <div class="glass-panel p-4 fade-up">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Live Activity</h5>
            <span class="badge-found">NEW</span>
          </div>
          <ul class="list-unstyled mb-0">
            <li class="py-2 border-bottom border-secondary-subtle d-flex justify-content-between"><span>Wallet found at Central Mall</span><small class="text-muted-custom">8m</small></li>
            <li class="py-2 border-bottom border-secondary-subtle d-flex justify-content-between"><span>Keys reported lost near Park</span><small class="text-muted-custom">19m</small></li>
            <li class="py-2 d-flex justify-content-between"><span>Headphones recovered at Bus Terminal</span><small class="text-muted-custom">33m</small></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="py-4">
  <div class="container bg-card p-0 overflow-hidden">
    <div class="row g-0 text-center">
      <div class="col-lg-3 stat-item">
        <div class="stat-value">1,928</div>
        <div class="stat-label">Reports Published</div>
      </div>
      <div class="col-lg-3 stat-item">
        <div class="stat-value">1,304</div>
        <div class="stat-label">Items Recovered</div>
      </div>
      <div class="col-lg-3 stat-item">
        <div class="stat-value">84%</div>
        <div class="stat-label">Resolution Rate</div>
      </div>
      <div class="col-lg-3 stat-item">
        <div class="stat-value">24/7</div>
        <div class="stat-label">Community Coverage</div>
      </div>
    </div>
  </div>
</section>

<section class="py-5" id="home-reports">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
      <div>
        <p class="section-tag mb-1">Featured Reports</p>
        <h2 class="mb-0">Latest Lost &amp; Found Posts</h2>
      </div>
      <div class="d-flex gap-2" data-filter-group data-target="#home-reports .report-card">
        <button class="filter-tab active" data-type="all">All</button>
        <button class="filter-tab" data-type="lost">Lost</button>
        <button class="filter-tab" data-type="found">Found</button>
      </div>
    </div>

    <div class="row g-4">
      <?php foreach ($featuredReports as $report): ?>
        <div class="col-md-6 col-lg-3">
          <a class="text-decoration-none" href="/findit/report-detail.php?id=<?= $report['id']; ?>">
            <article class="report-card h-100" data-type="<?= $report['type']; ?>" data-category="<?= $report['category']; ?>">
              <div class="card-img-placeholder"><i class="bi <?= $report['icon']; ?>"></i></div>
              <div class="p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <span class="<?= $report['type'] === 'lost' ? 'badge-lost' : 'badge-found'; ?>"><?= strtoupper($report['type']); ?></span>
                  <small class="text-muted-custom"><?= $report['time']; ?></small>
                </div>
                <h6 class="mb-1"><?= htmlspecialchars($report['title']); ?></h6>
                <p class="mb-0 text-muted-custom small"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($report['location']); ?></p>
              </div>
            </article>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
