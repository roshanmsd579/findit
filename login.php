<?php
session_start();
$pageTitle = 'Login - FindIt';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email !== '' && $password !== '') {
        $_SESSION['user_id'] = 1;
        $_SESSION['name'] = 'Community User';
        $_SESSION['role'] = $email === 'admin@findit.local' ? 'admin' : 'member';
        header('Location: /findit/dashboard.php');
        exit;
    }

    $error = 'Please enter both email and password.';
}
?>
<!doctype html>
<html lang="en">
<?php include __DIR__ . '/includes/header.php'; ?>
<body>
<div class="container-fluid">
  <div class="row g-0">
    <div class="col-lg-6 auth-left">
      <div>
        <p class="section-tag">Welcome Back</p>
        <h1 class="display-5 mb-3">Sign in to track your reports in real-time.</h1>
        <p class="text-muted-custom">Access your dashboard, edit reports, and respond to match requests securely.</p>
      </div>
    </div>
    <div class="col-lg-6 auth-right">
      <div class="w-100" style="max-width: 420px;">
        <h3 class="mb-3">Login</h3>
        <?php if ($error): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="post" class="bg-card p-4">
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control-custom" required>
          </div>
          <div class="mb-4">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control-custom" required>
          </div>
          <button class="btn btn-primary-custom w-100" type="submit">Login</button>
          <p class="small text-muted-custom mt-3 mb-0">No account? <a href="/findit/register.php">Register now</a></p>
        </form>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
