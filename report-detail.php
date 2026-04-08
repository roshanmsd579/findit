<?php
session_start();
$pageTitle = 'Report Detail - FindIt';

$reportId = isset($_GET['id']) ? (int) $_GET['id'] : 101;
$report = [
    'id' => $reportId,
    'title' => 'Black Backpack with Laptop',
    'type' => 'lost',
    'category' => 'electronics',
    'description' => 'Lost near the north campus gate around 6:30 PM. Contains a dark gray laptop, charger, and notebooks.',
    'location' => 'North Campus Gate',
    'status' => 'open',
    'reported_at' => '2026-04-02 18:45',
    'contact' => 'finder@findit.community',
    'lat' => 28.6139,
    'lng' => 77.2090
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
      <div class="col-lg-8">
        <article class="bg-card p-4">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <span class="<?= $report['type'] === 'lost' ? 'badge-lost' : 'badge-found'; ?>"><?= strtoupper($report['type']); ?></span>
            <span class="badge-resolved"><?= strtoupper($report['status']); ?></span>
          </div>
          <h1 class="h2 mb-3"><?= htmlspecialchars($report['title']); ?></h1>
          <p class="text-muted-custom mb-4"><?= htmlspecialchars($report['description']); ?></p>

          <div class="row g-3 mb-3">
            <div class="col-sm-6">
              <div class="bg-dark rounded-3 p-3 h-100 border border-secondary-subtle">
                <small class="text-muted-custom d-block mb-1">Category</small>
                <strong><?= htmlspecialchars(ucfirst($report['category'])); ?></strong>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="bg-dark rounded-3 p-3 h-100 border border-secondary-subtle">
                <small class="text-muted-custom d-block mb-1">Location</small>
                <strong><?= htmlspecialchars($report['location']); ?></strong>
              </div>
            </div>
          </div>

          <div class="bg-dark rounded-3 p-3 border border-secondary-subtle mb-4">
            <h6 class="text-muted-custom text-uppercase small mb-2">Status Timeline</h6>
            <ul class="mb-0 ps-3">
              <li>Report published - <?= htmlspecialchars($report['reported_at']); ?></li>
              <li>Verification check queued</li>
              <li>Awaiting matching confirmation</li>
            </ul>
          </div>

          <div id="map" data-lat="<?= $report['lat']; ?>" data-lng="<?= $report['lng']; ?>" data-label="<?= htmlspecialchars($report['location']); ?>"></div>
        </article>
      </div>

      <div class="col-lg-4">
        <aside class="bg-card p-4">
          <h5 class="mb-3">Contact Reporter</h5>
          <p class="text-muted-custom">If this item matches yours, contact securely through FindIt messaging.</p>
          <a href="mailto:<?= htmlspecialchars($report['contact']); ?>" class="btn btn-primary-custom w-100 mb-2">Send Message</a>
          <button class="btn btn-outline-custom w-100">Mark as Potential Match</button>
          <hr class="border-secondary">
          <p class="small text-muted-custom mb-1">Report ID: #<?= $report['id']; ?></p>
          <p class="small text-muted-custom mb-0">Filed at: <?= htmlspecialchars($report['reported_at']); ?></p>
        </aside>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
