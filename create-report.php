<?php
require_once __DIR__ . '/includes/db.php';
require_login();

$pageTitle = 'Create Report - ' . SITE_NAME;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';
    $category = $_POST['category'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $campusLocation = trim($_POST['campus_location'] ?? '');
    $dateOccurred = $_POST['date_occurred'] ?? '';
    $timeOccurred = $_POST['time_occurred'] ?: null;
    $latitude = $_POST['latitude'] !== '' ? (float) $_POST['latitude'] : null;
    $longitude = $_POST['longitude'] !== '' ? (float) $_POST['longitude'] : null;
    $contactPhone = trim($_POST['contact_phone'] ?? '');
    $secretQuestion = trim($_POST['secret_question'] ?? '');
    $secretAnswer = trim($_POST['secret_answer'] ?? '');
    $rewardOffered = isset($_POST['reward_offered']) ? 1 : 0;
    $rewardAmount = $rewardOffered ? (float) ($_POST['reward_amount'] ?? 0) : null;
    $additionalNotes = trim($_POST['additional_notes'] ?? '');

    if (!in_array($type, ['lost', 'found'], true)) {
        $errors[] = 'Please select report type.';
    }
    if (!array_key_exists($category, REPORT_CATEGORIES)) {
        $errors[] = 'Please select a category.';
    }
    if ($title === '' || mb_strlen($title) < 6) {
        $errors[] = 'Title must be at least 6 characters.';
    }
    if ($description === '' || mb_strlen($description) < 15) {
        $errors[] = 'Description must be at least 15 characters.';
    }
    if (!in_array($campusLocation, CAMPUS_LOCATIONS, true)) {
        $errors[] = 'Invalid campus location selected.';
    }
    if ($dateOccurred === '') {
        $errors[] = 'Date is required.';
    }
    if ($type === 'lost' && ($secretQuestion === '' || $secretAnswer === '')) {
        $errors[] = 'Secret question and answer are required for lost reports.';
    }

    $photoPath = null;
    if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($ext, $allowed, true)) {
            $errors[] = 'Photo must be jpg, jpeg, png, or webp.';
        } else {
            $fileName = 'report_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $target = UPLOAD_DIR . $fileName;
            if (!move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
                $errors[] = 'Failed to upload photo.';
            } else {
                $photoPath = $fileName;
            }
        }
    }

    $cameraData = $_POST['webcam_image'] ?? '';
    if (!$photoPath && $cameraData !== '' && str_starts_with($cameraData, 'data:image/')) {
        [$meta, $content] = explode(',', $cameraData, 2);
        $binary = base64_decode($content, true);
        if ($binary !== false) {
            $ext = str_contains($meta, 'png') ? 'png' : 'jpg';
            $fileName = 'webcam_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            file_put_contents(UPLOAD_DIR . $fileName, $binary);
            $photoPath = $fileName;
        }
    }

    if (!$errors) {
        $fullDescription = $description;
        if ($additionalNotes !== '') {
            $fullDescription .= "\n\nNotes: " . $additionalNotes;
        }

        $ins = $pdo->prepare("INSERT INTO reports
            (user_id, type, category, title, description, photo, campus_location, latitude, longitude, date_occurred, time_occurred, contact_phone, secret_question, secret_answer, reward_offered, reward_amount)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $ins->execute([
            $_SESSION['user_id'], $type, $category, $title, $fullDescription, $photoPath,
            $campusLocation, $latitude, $longitude, $dateOccurred, $timeOccurred, $contactPhone,
            $type === 'lost' ? $secretQuestion : null,
            $type === 'lost' ? $secretAnswer : null,
            $rewardOffered,
            $rewardAmount,
        ]);

        redirect('report-detail.php?id=' . (int) $pdo->lastInsertId());
    }
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>
<div class="container py-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <span class="section-tag">Create Report</span>
      <h1 class="section-title mb-0">Submit Lost/Found Item</h1>
    </div>
  </div>

  <?php if ($errors): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>

  <form method="post" enctype="multipart/form-data" id="create-report-form" novalidate>
    <div class="step-indicator" id="step-indicator">
      <div class="step-item active"><div class="step-dot active" data-step="1">1</div><div class="step-label">Type &amp; Category</div></div>
      <div class="step-item"><div class="step-dot" data-step="2">2</div><div class="step-label">Details &amp; Map</div></div>
      <div class="step-item"><div class="step-dot" data-step="3">3</div><div class="step-label">Photo &amp; Contact</div></div>
    </div>

    <input type="hidden" name="type" id="type-input" value="<?= h($_GET['type'] ?? '') ?>">
    <input type="hidden" name="category" id="category-input">

    <div class="form-step" data-step="1">
      <div class="row g-4 mb-4">
        <div class="col-md-6">
          <div class="type-card" data-type-card="lost">
            <h4 class="mb-2">LOST</h4>
            <p class="mb-0 text-muted">I lost an item on campus.</p>
          </div>
        </div>
        <div class="col-md-6">
          <div class="type-card" data-type-card="found">
            <h4 class="mb-2">FOUND</h4>
            <p class="mb-0 text-muted">I found an item and want to return it.</p>
          </div>
        </div>
      </div>

      <label class="form-label-custom">Select Category <span class="req">*</span></label>
      <div class="row g-3 mb-4">
        <?php foreach (REPORT_CATEGORIES as $key => $label): ?>
          <div class="col-6 col-md-4 col-lg-3">
            <div class="cat-card" data-category-card="<?= h($key) ?>">
              <div class="fs-4 mb-2">📍</div>
              <strong><?= h($label) ?></strong>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="mb-4">
        <label class="form-label-custom">Report Title <span class="req">*</span></label>
        <input type="text" class="form-custom required-step-1" name="title" id="title-input" placeholder="Example: Lost ID card near Central Library">
      </div>
      <div class="d-flex justify-content-end"><button class="btn btn-accent next-step" type="button">Next Step</button></div>
    </div>

    <div class="form-step d-none" data-step="2">
      <div class="mb-3">
        <label class="form-label-custom">Description <span class="req">*</span></label>
        <textarea class="form-custom required-step-2" name="description" rows="5" placeholder="Describe color, brand, identifier, where you last saw/found it..."></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label-custom">Campus Location <span class="req">*</span></label>
        <select name="campus_location" class="form-custom required-step-2">
          <option value="">Select location</option>
          <?php foreach (CAMPUS_LOCATIONS as $loc): ?><option value="<?= h($loc) ?>"><?= h($loc) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="row g-3 mb-3">
        <div class="col-md-6"><label class="form-label-custom">Date <span class="req">*</span></label><input type="date" name="date_occurred" class="form-custom required-step-2"></div>
        <div class="col-md-6"><label class="form-label-custom">Time</label><input type="time" name="time_occurred" class="form-custom"></div>
      </div>
      <div class="mb-3">
        <label class="form-label-custom">Pin Approximate Spot on Map</label>
        <div id="map"></div>
        <input type="hidden" name="latitude" id="lat-input">
        <input type="hidden" name="longitude" id="lng-input">
      </div>

      <div class="lost-only d-none">
        <div class="mb-3"><label class="form-label-custom">Secret Question <span class="req">*</span></label><input type="text" name="secret_question" class="form-custom"></div>
        <div class="mb-3"><label class="form-label-custom">Secret Answer <span class="req">*</span></label><input type="text" name="secret_answer" class="form-custom"></div>
      </div>

      <div class="form-check form-switch mb-3">
        <input class="form-check-input" type="checkbox" name="reward_offered" id="reward-toggle">
        <label class="form-check-label" for="reward-toggle">Reward Offered</label>
      </div>
      <div class="mb-4 d-none" id="reward-amount-wrap">
        <label class="form-label-custom">Reward Amount</label>
        <input type="number" step="0.01" min="0" name="reward_amount" class="form-custom" placeholder="Enter amount">
      </div>
      <div class="d-flex justify-content-between">
        <button class="btn btn-outline-custom prev-step" type="button">Previous</button>
        <button class="btn btn-accent next-step" type="button">Next Step</button>
      </div>
    </div>

    <div class="form-step d-none" data-step="3">
      <div class="mb-3">
        <label class="form-label-custom">Upload Photo</label>
        <label class="upload-area" for="photo-input">
          <i class="bi bi-cloud-upload fs-1"></i>
          <p class="mb-0 mt-2">Drop image or tap to browse/capture</p>
          <input type="file" name="photo" id="photo-input" class="d-none" accept="image/*" capture="environment">
        </label>
        <img id="photo-preview" class="img-fluid rounded mt-3 d-none" alt="Preview">
      </div>

      <div class="report-card p-3 mb-3">
        <div class="d-flex gap-2 mb-2">
          <button type="button" class="btn btn-outline-custom btn-sm" id="start-camera">Open Webcam</button>
          <button type="button" class="btn btn-accent btn-sm d-none" id="capture-image">Capture</button>
          <button type="button" class="btn btn-outline-custom btn-sm d-none" id="retake-image">Retake</button>
        </div>
        <video id="camera-stream" class="w-100 rounded d-none" autoplay playsinline></video>
        <canvas id="camera-canvas" class="d-none"></canvas>
        <input type="hidden" name="webcam_image" id="webcam-image-input">
      </div>

      <div class="mb-3"><label class="form-label-custom">Contact Phone</label><input type="text" name="contact_phone" class="form-custom" value="<?= h($_SESSION['phone'] ?? '') ?>"></div>
      <div class="mb-3"><label class="form-label-custom">Additional Notes</label><textarea name="additional_notes" rows="4" class="form-custom" placeholder="Meeting preference, available timing, etc."></textarea></div>
      <div class="alert alert-secondary">Your details are protected. Contact is shared only inside authenticated campus workflow.</div>

      <div class="d-flex justify-content-between">
        <button class="btn btn-outline-custom prev-step" type="button">Previous</button>
        <button class="btn btn-accent" type="submit">Submit Report</button>
      </div>
    </div>
  </form>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
