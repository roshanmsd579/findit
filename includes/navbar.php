<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentPage = basename($_SERVER['PHP_SELF'] ?? '');

function navActive(string $file, string $currentPage): string
{
    return $file === $currentPage ? 'active' : '';
}
?>
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center gap-2" href="/findit/index.php">
      <span class="brand-icon">◈</span>
      <span>FindIt</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#finditNav" aria-controls="finditNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="finditNav">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
        <li class="nav-item"><a class="nav-link <?= navActive('index.php', $currentPage); ?>" href="/findit/index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link <?= navActive('search.php', $currentPage); ?>" href="/findit/search.php">Search</a></li>
        <li class="nav-item"><a class="nav-link <?= navActive('reports.php', $currentPage); ?>" href="/findit/reports.php">Reports</a></li>
        <li class="nav-item"><a class="nav-link <?= navActive('create-report.php', $currentPage); ?>" href="/findit/create-report.php">Report Lost/Found</a></li>

        <?php if (!empty($_SESSION['user_id'])): ?>
          <li class="nav-item"><a class="nav-link <?= navActive('dashboard.php', $currentPage); ?>" href="/findit/dashboard.php">Dashboard</a></li>
          <?php if (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <li class="nav-item"><a class="nav-link <?= navActive('admin.php', $currentPage); ?>" href="/findit/admin.php">Admin</a></li>
          <?php endif; ?>
          <li class="nav-item"><a class="nav-link" href="/findit/logout.php">Logout</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link <?= navActive('login.php', $currentPage); ?>" href="/findit/login.php">Login</a></li>
          <li class="nav-item">
            <a class="btn btn-primary-custom px-3 py-2 ms-lg-2" href="/findit/register.php">Register</a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
