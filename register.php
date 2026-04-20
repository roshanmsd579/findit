<?php
require_once __DIR__ . '/includes/db.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentId = trim($_POST['student_id'] ?? '');
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $department = trim($_POST['department'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $name = trim($firstName . ' ' . $lastName);

    if ($studentId === '' || $name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '' || $department === '') {
        $error = 'Please fill all required fields.';
    } elseif (!valid_uni_email($email)) {
        $error = 'Please use your university email.';
    } elseif (!in_array($department, DEPARTMENTS, true)) {
        $error = 'Invalid department selected.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        $exists = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = ? OR student_id = ?');
        $exists->execute([$email, $studentId]);
        if ((int) $exists->fetchColumn() > 0) {
            $error = 'Email or Student ID already exists.';
        } else {
            $ins = $pdo->prepare('INSERT INTO users (student_id, name, email, password, phone, role, department, is_verified) VALUES (?, ?, ?, ?, ?, ?, ?, 1)');
            $ins->execute([$studentId, $name, $email, password_hash($password, PASSWORD_BCRYPT), $phone, 'student', $department]);
            $_SESSION['user_id'] = (int) $pdo->lastInsertId();
            $_SESSION['user_name'] = $name;
            $_SESSION['role'] = 'student';
            redirect('dashboard.php');
        }
    }
}

$pageTitle = 'Register - ' . SITE_NAME;
include __DIR__ . '/includes/header.php';
?>
<div class="auth-split">
  <div class="auth-left">
    <div class="position-relative" style="z-index:2;max-width:520px;">
      <h1 class="display-5 mb-3">Create Your Campus Account</h1>
      <p class="text-muted mb-4">Only verified university members can access report and claim workflows.</p>
      <div class="report-card p-3">
        <strong>University Email Required</strong>
        <p class="small text-muted mb-0">Use an email ending with <?= h(UNI_EMAIL) ?> to register.</p>
      </div>
    </div>
  </div>
  <div class="auth-right">
    <form method="post" class="auth-form" novalidate>
      <h2 class="mb-2">Register</h2>
      <p class="text-muted mb-4">Join the FindIt campus community.</p>
      <?php if ($error): ?><div class="alert alert-danger"><?= h($error) ?></div><?php endif; ?>

      <div class="mb-3">
        <label class="form-label-custom">Student ID <span class="req">*</span></label>
        <input type="text" name="student_id" class="form-custom" required>
      </div>
      <div class="row g-2 mb-3">
        <div class="col-md-6"><label class="form-label-custom">First Name <span class="req">*</span></label><input type="text" name="first_name" class="form-custom" required></div>
        <div class="col-md-6"><label class="form-label-custom">Last Name <span class="req">*</span></label><input type="text" name="last_name" class="form-custom" required></div>
      </div>
      <div class="mb-3">
        <label class="form-label-custom">University Email <span class="req">*</span></label>
        <input type="email" name="email" class="form-custom" placeholder="name<?= h(UNI_EMAIL) ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label-custom">Department <span class="req">*</span></label>
        <select name="department" class="form-custom" required>
          <option value="">Select department</option>
          <?php foreach (DEPARTMENTS as $dep): ?><option value="<?= h($dep) ?>"><?= h($dep) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label-custom">Phone</label>
        <input type="text" name="phone" class="form-custom">
      </div>
      <div class="row g-2 mb-3">
        <div class="col-md-6"><label class="form-label-custom">Password <span class="req">*</span></label><input type="password" name="password" class="form-custom" required></div>
        <div class="col-md-6"><label class="form-label-custom">Confirm Password <span class="req">*</span></label><input type="password" name="confirm_password" class="form-custom" required></div>
      </div>
      <button type="submit" class="btn btn-accent w-100">Create Account</button>
      <p class="small text-muted mt-3 mb-0">Already have an account? <a href="<?= BASE_URL ?>login.php">Login</a></p>
    </form>
  </div>
</div>
</body>
</html>
