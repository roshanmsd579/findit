<?php
require_once __DIR__ . '/includes/db.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $error = 'Please enter a valid email and password.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            redirect('dashboard.php');
        }

        $error = 'Invalid login credentials.';
    }
}

$pageTitle = 'Login - ' . SITE_NAME;
include __DIR__ . '/includes/header.php';
?>
<div class="auth-split">
  <div class="auth-left">
    <div class="position-relative" style="z-index:2;max-width:520px;">
      <h1 class="display-5 mb-3">Campus Lost &amp; Found, Reimagined.</h1>
      <p class="text-muted mb-4">Sign in to track reports, chat securely, and verify handovers with confidence.</p>
      <div class="row g-3 mb-4">
        <div class="col-4"><div class="dash-stat"><div class="dash-stat-value">24/7</div><div class="dash-stat-label">Support</div></div></div>
        <div class="col-4"><div class="dash-stat"><div class="dash-stat-value">100%</div><div class="dash-stat-label">Campus Scope</div></div></div>
        <div class="col-4"><div class="dash-stat"><div class="dash-stat-value">8</div><div class="dash-stat-label">Code Verify</div></div></div>
      </div>
      <div class="report-card p-3">
        <strong>Demo Credentials</strong>
        <p class="mb-1 small text-muted">Student: aryan@university.edu / password123</p>
        <p class="mb-1 small text-muted">Faculty: sunita@university.edu / password123</p>
        <p class="mb-0 small text-muted">Admin: admin@university.edu / password123</p>
      </div>
    </div>
  </div>
  <div class="auth-right">
    <div class="auth-form">
      <h2 class="mb-2">Welcome Back</h2>
      <p class="text-muted mb-4">Sign in with your university account.</p>
      <?php if ($error): ?>
        <div class="alert alert-danger"><?= h($error) ?></div>
      <?php endif; ?>
      <form method="post" novalidate>
        <div class="mb-3">
          <label class="form-label-custom">University Email <span class="req">*</span></label>
          <input type="email" name="email" class="form-custom" placeholder="name<?= h(UNI_EMAIL) ?>" required>
        </div>
        <div class="mb-2">
          <label class="form-label-custom">Password <span class="req">*</span></label>
          <input type="password" name="password" class="form-custom" required>
        </div>
        <div class="text-end mb-3"><a href="#" class="small">Forgot password?</a></div>
        <button type="submit" class="btn btn-accent w-100 mb-3">Login</button>
        <button type="button" class="btn btn-outline-custom w-100 mb-3"><i class="bi bi-google"></i> Continue with Google</button>
      </form>
      <p class="mb-0 text-muted small">New here? <a href="<?= BASE_URL ?>register.php">Create an account</a></p>
    </div>
    <?php include __DIR__ . '/includes/theme-toggle.php'; ?>
  </div>
</div>
</body>
</html>
