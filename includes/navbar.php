<?php
require_once __DIR__ . '/db.php';
$unreadNotif = 0;
$unreadChat = 0;
if (isset($_SESSION['user_id'])) {
    $s = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
    $s->execute([$_SESSION['user_id']]);
    $unreadNotif = (int) $s->fetchColumn();

    $s = $pdo->prepare('SELECT COUNT(*) FROM chat_messages WHERE receiver_id = ? AND is_read = 0');
    $s->execute([$_SESSION['user_id']]);
    $unreadChat = (int) $s->fetchColumn();
}

$currentPage = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar navbar-expand-lg navbar-custom sticky-top">
  <div class="container">
    <a class="navbar-brand" href="<?= BASE_URL ?>index.php">
      <span class="brand-icon">◈</span> Find<strong>It</strong>
      <small class="brand-sub">Campus Portal</small>
    </a>
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-controls="navMenu" aria-expanded="false" aria-label="Toggle navigation">
      <i class="bi bi-list text-white fs-4"></i>
    </button>
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav me-auto gap-1">
        <li class="nav-item"><a class="nav-link <?= $currentPage === 'index.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link <?= $currentPage === 'search.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>search.php">Search</a></li>
        <li class="nav-item"><a class="nav-link <?= $currentPage === 'reports.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>reports.php">Reports</a></li>
        <?php if (isset($_SESSION['user_id'])): ?>
          <li class="nav-item"><a class="nav-link btn-accent-nav <?= $currentPage === 'create-report.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>create-report.php"><i class="bi bi-plus-circle"></i> New Report</a></li>
        <?php endif; ?>
      </ul>
      <div class="d-flex align-items-center gap-2">
        <button class="theme-toggle" id="theme-toggle" type="button">
          <span id="theme-icon">☀️</span>
          <span id="theme-label" class="d-none d-lg-inline">Light</span>
        </button>

        <?php if (isset($_SESSION['user_id'])): ?>
          <a href="<?= BASE_URL ?>dashboard.php" class="nav-icon-btn position-relative" title="Chats">
            <i class="bi bi-chat-dots"></i>
            <?php if ($unreadChat > 0): ?><span class="notif-badge"><?= $unreadChat ?></span><?php endif; ?>
          </a>
          <a href="<?= BASE_URL ?>dashboard.php#notifications" class="nav-icon-btn position-relative" title="Notifications">
            <i class="bi bi-bell"></i>
            <?php if ($unreadNotif > 0): ?><span class="notif-badge"><?= $unreadNotif ?></span><?php endif; ?>
          </a>
          <div class="dropdown">
            <button class="user-menu-btn dropdown-toggle" data-bs-toggle="dropdown" type="button" aria-expanded="false">
              <div class="avatar-sm"><?= h(strtoupper(substr($_SESSION['user_name'], 0, 2))) ?></div>
              <span class="d-none d-lg-inline"><?= h($_SESSION['user_name']) ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end dropdown-dark-custom">
              <li><a class="dropdown-item" href="<?= BASE_URL ?>profile.php"><i class="bi bi-person"></i> Profile</a></li>
              <li><a class="dropdown-item" href="<?= BASE_URL ?>dashboard.php"><i class="bi bi-grid"></i> Dashboard</a></li>
              <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>admin.php"><i class="bi bi-shield-check"></i> Admin Panel</a></li>
              <?php endif; ?>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
            </ul>
          </div>
        <?php else: ?>
          <a href="<?= BASE_URL ?>login.php" class="btn btn-outline-custom btn-sm">Login</a>
          <a href="<?= BASE_URL ?>register.php" class="btn btn-accent btn-sm">Register</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>
