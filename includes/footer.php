<?php require_once __DIR__ . '/config.php'; ?>
<footer class="site-footer">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-4">
        <div class="footer-brand">◈ Find<strong>It</strong></div>
        <p class="footer-desc">Official Lost &amp; Found portal for <?= h(UNIVERSITY) ?>. Helping campus community reunite with their belongings since 2025.</p>
      </div>
      <div class="col-6 col-lg-2">
        <h6 class="footer-heading">Platform</h6>
        <ul class="footer-links">
          <li><a href="<?= BASE_URL ?>create-report.php">Report Lost</a></li>
          <li><a href="<?= BASE_URL ?>create-report.php?type=found">Report Found</a></li>
          <li><a href="<?= BASE_URL ?>search.php">Search</a></li>
          <li><a href="<?= BASE_URL ?>reports.php">All Reports</a></li>
        </ul>
      </div>
      <div class="col-6 col-lg-2">
        <h6 class="footer-heading">Categories</h6>
        <ul class="footer-links">
          <li><a href="<?= BASE_URL ?>search.php?category=id_card">ID Cards</a></li>
          <li><a href="<?= BASE_URL ?>search.php?category=laptop">Laptops</a></li>
          <li><a href="<?= BASE_URL ?>search.php?category=phone">Phones</a></li>
          <li><a href="<?= BASE_URL ?>search.php?category=keys">Keys</a></li>
        </ul>
      </div>
      <div class="col-6 col-lg-2">
        <h6 class="footer-heading">Account</h6>
        <ul class="footer-links">
          <li><a href="<?= BASE_URL ?>login.php">Login</a></li>
          <li><a href="<?= BASE_URL ?>register.php">Register</a></li>
          <li><a href="<?= BASE_URL ?>dashboard.php">Dashboard</a></li>
          <li><a href="<?= BASE_URL ?>profile.php">Profile</a></li>
        </ul>
      </div>
      <div class="col-6 col-lg-2">
        <h6 class="footer-heading">Support</h6>
        <ul class="footer-links">
          <li><a href="#">Help Center</a></li>
          <li><a href="#">Privacy Policy</a></li>
          <li><a href="#">Terms of Use</a></li>
          <li><a href="<?= BASE_URL ?>admin.php">Admin</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <span>© 2026 FindIt — <?= h(UNIVERSITY) ?>. All rights reserved.</span>
      <span>Built with campus trust and transparency</span>
    </div>
  </div>
</footer>

<div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content modal-dark">
      <div class="modal-header border-0">
        <h5 class="modal-title">Confirm Delete</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">This action cannot be undone. Continue?</div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="<?= BASE_URL ?>assets/js/main.js"></script>
</body>
</html>
