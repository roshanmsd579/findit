<?php
require_once __DIR__ . '/includes/db.php';
$pageTitle = 'FindIt - Campus Lost & Found';

$recentStmt = $pdo->query(
    "SELECT r.*, u.name AS user_name, u.role AS user_role
     FROM reports r
     JOIN users u ON r.user_id = u.id
     WHERE r.status = 'active'
     ORDER BY r.created_at DESC
     LIMIT 6"
);
$recentReports = $recentStmt->fetchAll();

$stats = [
    'reports' => (int) $pdo->query('SELECT COUNT(*) FROM reports')->fetchColumn(),
    'resolved' => (int) $pdo->query("SELECT COUNT(*) FROM reports WHERE status='resolved'")->fetchColumn(),
    'users' => (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
    'active' => (int) $pdo->query("SELECT COUNT(*) FROM reports WHERE status='active'")->fetchColumn(),
];

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<section class="hero position-relative">
  <div class="hero-grid"></div>
  <div class="container position-relative">
    <div class="row align-items-center g-5">
      <div class="col-lg-7 fade-up">
        <span class="badge rounded-pill mb-3 px-3 py-2" style="background:rgba(79,70,229,0.15);color:var(--accent-2);">
          <?= h(UNIVERSITY) ?> - Official Campus Portal
        </span>
        <h1 class="display-3 mb-3">Lost Something on Campus? <span style="color:var(--accent)">FindIt</span> Fast.</h1>
        <p class="lead text-muted mb-4">Trusted by students, faculty, staff, and security to report and recover items across your university grounds.</p>
        <div class="d-flex flex-wrap gap-2 mb-4">
          <a href="<?= BASE_URL ?>create-report.php" class="btn btn-accent btn-lg px-4">File Report</a>
          <a href="<?= BASE_URL ?>search.php" class="btn btn-outline-custom btn-lg px-4">Search Items</a>
        </div>
        <form action="<?= BASE_URL ?>search.php" method="get" class="d-flex gap-2">
          <input type="text" name="q" class="form-custom" placeholder="Search by item, location, or keyword">
          <button class="btn btn-accent" type="submit"><i class="bi bi-search"></i></button>
        </form>
      </div>
      <div class="col-lg-5 fade-up">
        <div class="report-card p-4">
          <h5 class="mb-3">Campus Activity Snapshot</h5>
          <div class="row g-3">
            <div class="col-6"><div class="dash-stat"><div class="dash-stat-value"><?= number_format($stats['active']) ?></div><div class="dash-stat-label">Active Reports</div></div></div>
            <div class="col-6"><div class="dash-stat"><div class="dash-stat-value"><?= number_format($stats['resolved']) ?></div><div class="dash-stat-label">Resolved</div></div></div>
            <div class="col-6"><div class="dash-stat"><div class="dash-stat-value"><?= number_format($stats['users']) ?></div><div class="dash-stat-label">Community Users</div></div></div>
            <div class="col-6"><div class="dash-stat"><div class="dash-stat-value"><?= number_format($stats['reports']) ?></div><div class="dash-stat-label">Total Reports</div></div></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="stats-bar">
  <div class="container">
    <div class="row g-0">
      <div class="col-6 col-lg-3 stat-item"><div class="stat-value"><?= number_format($stats['reports']) ?></div><div class="stat-label">Reports Filed</div></div>
      <div class="col-6 col-lg-3 stat-item"><div class="stat-value"><?= number_format($stats['active']) ?></div><div class="stat-label">Active Cases</div></div>
      <div class="col-6 col-lg-3 stat-item"><div class="stat-value"><?= number_format($stats['resolved']) ?></div><div class="stat-label">Resolved</div></div>
      <div class="col-6 col-lg-3 stat-item"><div class="stat-value"><?= number_format($stats['users']) ?></div><div class="stat-label">Registered Users</div></div>
    </div>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
      <div>
        <span class="section-tag">Recent Cases</span>
        <h2 class="section-title mb-0">Latest Campus Reports</h2>
      </div>
      <div class="d-flex flex-wrap gap-2" data-filter-group data-target="#recent-grid .report-card">
        <button class="filter-tab active" data-type="all" data-category="all">All</button>
        <button class="filter-tab" data-type="lost" data-category="all">Lost</button>
        <button class="filter-tab" data-type="found" data-category="all">Found</button>
        <button class="filter-tab" data-type="all" data-category="id_card">ID Cards</button>
        <button class="filter-tab" data-type="all" data-category="phone">Phones</button>
        <button class="filter-tab" data-type="all" data-category="laptop">Laptops</button>
      </div>
    </div>

    <div class="row g-4" id="recent-grid">
      <?php foreach ($recentReports as $report): ?>
        <div class="col-md-6 col-lg-4">
          <a class="report-card h-100" href="<?= BASE_URL ?>report-detail.php?id=<?= (int) $report['id'] ?>" data-type="<?= h($report['type']) ?>" data-category="<?= h($report['category']) ?>">
            <?php if (!empty($report['photo'])): ?>
              <img src="<?= h(UPLOAD_URL . basename($report['photo'])) ?>" class="report-card-img" alt="<?= h($report['title']) ?>">
            <?php else: ?>
              <div class="report-card-placeholder"><?= $report['type'] === 'lost' ? '🔍' : '📦' ?></div>
            <?php endif; ?>
            <div class="p-3">
              <div class="d-flex justify-content-between mb-2">
                <span class="<?= report_type_badge($report['type']) ?>"><?= strtoupper(h($report['type'])) ?></span>
                <span class="badge badge-status <?= status_badge_class($report['status']) ?>"><?= h(ucfirst($report['status'])) ?></span>
              </div>
              <h5 class="mb-2"><?= h($report['title']) ?></h5>
              <p class="text-muted small mb-3"><?= h(mb_strimwidth($report['description'], 0, 105, '...')) ?></p>
              <div class="d-flex justify-content-between small text-muted">
                <span><i class="bi bi-geo-alt"></i> <?= h(report_location($report)) ?></span>
                <span><?= h(human_date($report['date_occurred'])) ?></span>
              </div>
            </div>
          </a>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="text-center mt-4">
      <a href="<?= BASE_URL ?>reports.php" class="btn btn-outline-custom px-4">View All Reports <i class="bi bi-arrow-right"></i></a>
    </div>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <span class="section-tag">How It Works</span>
    <h2 class="section-title mb-4">Simple. Safe. Verifiable.</h2>
    <div class="row g-4">
      <div class="col-md-6 col-lg-3"><div class="report-card p-4 h-100"><h5>1. File Report</h5><p class="mb-0 text-muted">Post lost or found details with exact campus location and optional photo.</p></div></div>
      <div class="col-md-6 col-lg-3"><div class="report-card p-4 h-100"><h5>2. Smart Match</h5><p class="mb-0 text-muted">Potential report matches and claims are highlighted for faster recovery.</p></div></div>
      <div class="col-md-6 col-lg-3"><div class="report-card p-4 h-100"><h5>3. Chat Securely</h5><p class="mb-0 text-muted">Coordinate return using in-app chat and claim verification workflow.</p></div></div>
      <div class="col-md-6 col-lg-3"><div class="report-card p-4 h-100"><h5>4. Resolve</h5><p class="mb-0 text-muted">Complete code verification and both users confirm successful handover.</p></div></div>
    </div>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <div class="report-card p-4 p-lg-5">
      <span class="section-tag">Verification Flow</span>
      <div class="row g-4">
        <div class="col-md-3"><h5>Claim</h5><p class="text-muted mb-0">Finder submits intent to return through secure claim.</p></div>
        <div class="col-md-3"><h5>Secret Q&A</h5><p class="text-muted mb-0">Reporter checks ownership with private question.</p></div>
        <div class="col-md-3"><h5>Code Match</h5><p class="text-muted mb-0">8-character code verified during physical handover.</p></div>
        <div class="col-md-3"><h5>Both Confirm</h5><p class="text-muted mb-0">Reporter and claimant confirm to close case safely.</p></div>
      </div>
    </div>
  </div>
</section>

<section class="pb-5">
  <div class="container">
    <div class="report-card p-4 p-lg-5 d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
      <div>
        <h3 class="mb-1">Join FindIt Campus</h3>
        <p class="text-muted mb-0">Keep your campus safe and connected. Register with your university email.</p>
      </div>
      <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>register.php" class="btn btn-accent">Create Account</a>
        <a href="<?= BASE_URL ?>create-report.php" class="btn btn-outline-custom">Report Now</a>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
