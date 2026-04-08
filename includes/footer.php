<footer class="mt-5 pt-5 pb-3 border-top border-secondary-subtle bg-black">
  <div class="container">
    <div class="row g-4">
      <div class="col-md-4">
        <h5 class="mb-3"><span class="brand-icon">◈</span> FindIt</h5>
        <p class="text-secondary mb-0">A modern community platform helping people reconnect with lost belongings through trusted local reporting.</p>
      </div>
      <div class="col-6 col-md-2">
        <h6 class="text-uppercase text-secondary small">Platform</h6>
        <ul class="list-unstyled small">
          <li><a class="footer-link" href="/findit/index.php">Home</a></li>
          <li><a class="footer-link" href="/findit/search.php">Search</a></li>
          <li><a class="footer-link" href="/findit/reports.php">Reports</a></li>
        </ul>
      </div>
      <div class="col-6 col-md-3">
        <h6 class="text-uppercase text-secondary small">Categories</h6>
        <ul class="list-unstyled small">
          <li><a class="footer-link" href="/findit/reports.php?category=electronics">Electronics</a></li>
          <li><a class="footer-link" href="/findit/reports.php?category=documents">Documents</a></li>
          <li><a class="footer-link" href="/findit/reports.php?category=accessories">Accessories</a></li>
        </ul>
      </div>
      <div class="col-md-3">
        <h6 class="text-uppercase text-secondary small">Account</h6>
        <ul class="list-unstyled small mb-0">
          <li><a class="footer-link" href="/findit/login.php">Login</a></li>
          <li><a class="footer-link" href="/findit/register.php">Register</a></li>
          <li><a class="footer-link" href="/findit/dashboard.php">Dashboard</a></li>
        </ul>
      </div>
    </div>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center pt-4 mt-4 border-top border-secondary-subtle text-secondary small">
      <span>&copy; <?= date('Y'); ?> FindIt. All rights reserved.</span>
      <span>Built with &hearts; for the community</span>
    </div>
  </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="/findit/assets/js/main.js"></script>
