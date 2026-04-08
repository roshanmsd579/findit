<?php
session_start();
$pageTitle = 'Register - FindIt';

$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($name && $email && $password) {
        $success = true;
    }
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
        <p class="section-tag">Join FindIt</p>
        <h1 class="display-5 mb-3">Create your account and help your community recover items.</h1>
        <p class="text-muted-custom">Report instantly, get updates, and connect responsibly with verified claimants.</p>
      </div>
    </div>
    <div class="col-lg-6 auth-right">
      <div class="w-100" style="max-width: 460px;">
        <h3 class="mb-3">Create Account</h3>
        <?php if ($success): ?>
          <div class="alert alert-success">Registration successful. You can now <a href="/findit/login.php">login</a>.</div>
        <?php endif; ?>
        <form method="post" class="bg-card p-4">
          <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="form-control-custom" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control-custom" required>
          </div>
          <div class="mb-4">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control-custom" required>
          </div>
          <button class="btn btn-primary-custom w-100" type="submit">Register</button>
          <p class="small text-muted-custom mt-3 mb-0">Already registered? <a href="/findit/login.php">Login</a></p>
        </form>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
