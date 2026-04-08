<?php
session_start();
$pageTitle = 'Create Report - FindIt';

$submitted = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted = true;
}
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
        <p class="section-tag mb-1">Create Report</p>
        <h2 class="mb-0">Report Lost or Found Item</h2>
      </div>
      <div class="progress bg-dark" style="height:8px; width:220px;">
        <div id="form-progress" class="progress-bar bg-danger" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
      </div>
    </div>

    <?php if ($submitted): ?>
      <div class="alert alert-success">Your report was submitted successfully. A moderator will review it shortly.</div>
    <?php endif; ?>

    <div class="row g-4">
      <div class="col-lg-3">
        <div class="bg-card p-3">
          <div class="step-dot active" data-step="1">1</div><div class="step-label active" data-step="1">Type</div>
          <div class="step-dot mt-3" data-step="2">2</div><div class="step-label" data-step="2">Details</div>
          <div class="step-dot mt-3" data-step="3">3</div><div class="step-label" data-step="3">Location</div>
          <div class="step-dot mt-3" data-step="4">4</div><div class="step-label" data-step="4">Confirm</div>
        </div>
      </div>

      <div class="col-lg-9">
        <form id="create-report-form" class="bg-card p-4" method="post" enctype="multipart/form-data" data-max-step="4">
          <input type="hidden" id="report_type" name="report_type" required>
          <input type="hidden" id="report_category" name="report_category" required>

          <div class="form-step" id="step-1">
            <h5 class="mb-3">Choose report type and category</h5>
            <div class="row g-3 mb-4">
              <div class="col-md-6">
                <div class="type-card" data-value="lost">
                  <h6 class="mb-1">Lost Item</h6>
                  <p class="small text-muted-custom mb-0">I lost something and need help finding it.</p>
                </div>
              </div>
              <div class="col-md-6">
                <div class="type-card" data-value="found">
                  <h6 class="mb-1">Found Item</h6>
                  <p class="small text-muted-custom mb-0">I found an item and want to return it.</p>
                </div>
              </div>
            </div>

            <div class="row g-3">
              <div class="col-md-4"><div class="category-card" data-value="electronics">Electronics</div></div>
              <div class="col-md-4"><div class="category-card" data-value="documents">Documents</div></div>
              <div class="col-md-4"><div class="category-card" data-value="accessories">Accessories</div></div>
            </div>
          </div>

          <div class="form-step d-none" id="step-2">
            <h5 class="mb-3">Item details</h5>
            <div class="mb-3">
              <label class="form-label">Title</label>
              <input required name="title" class="form-control-custom" placeholder="e.g. Black backpack with laptop">
            </div>
            <div class="mb-3">
              <label class="form-label">Description</label>
              <textarea required name="description" class="form-control-custom" rows="4" placeholder="Distinct marks, contents, and context..."></textarea>
            </div>
            <div class="upload-area">
              <i class="bi bi-cloud-arrow-up fs-2 d-block mb-2"></i>
              <p class="mb-2">Upload image (optional)</p>
              <input id="report_image" name="report_image" type="file" class="d-none" accept="image/*">
              <img id="image-preview" class="img-fluid rounded d-none mt-3" alt="Preview">
            </div>
          </div>

          <div class="form-step d-none" id="step-3">
            <h5 class="mb-3">Where was it lost/found?</h5>
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Location name</label>
                <input required name="location" class="form-control-custom" placeholder="e.g. City Library">
              </div>
              <div class="col-md-6">
                <label class="form-label">Date</label>
                <input required name="date" type="date" class="form-control-custom">
              </div>
              <div class="col-12">
                <label class="form-label">Additional notes</label>
                <textarea name="notes" class="form-control-custom" rows="3" placeholder="Landmarks or time window"></textarea>
              </div>
            </div>
          </div>

          <div class="form-step d-none" id="step-4">
            <h5 class="mb-3">Confirm and submit</h5>
            <p class="text-muted-custom">Please review your inputs. Submission confirms your report is accurate and truthful.</p>
            <div class="mb-3">
              <label class="form-label">Contact Email</label>
              <input required type="email" name="email" class="form-control-custom" placeholder="you@example.com">
            </div>
            <div class="form-check">
              <input required class="form-check-input" type="checkbox" value="1" id="consent" name="consent">
              <label class="form-check-label" for="consent">I agree to FindIt community reporting guidelines.</label>
            </div>
          </div>

          <div class="d-flex justify-content-between mt-4">
            <button type="button" id="btn-prev" class="btn btn-outline-custom d-none">Previous</button>
            <div class="ms-auto d-flex gap-2">
              <button type="button" id="btn-next" class="btn btn-primary-custom">Continue</button>
              <button type="submit" id="btn-submit" class="btn btn-primary-custom d-none">Submit Report</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
