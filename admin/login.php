<?php
require_once __DIR__ . '/init.php';

if (is_admin_logged_in()) {
    redirect('index.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $st = db()->prepare('SELECT * FROM admins WHERE email = ?');
    $st->execute([$email]);
    $admin = $st->fetch();
    if ($admin && password_verify($password, $admin['password'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int) $admin['id'];
        $_SESSION['admin_name'] = $admin['name'];
        redirect('index.php');
    }
    $error = 'The email or password is not correct.';
}

$pageTitle = 'Admin login';
$hideNav = true;
include __DIR__ . '/../includes/admin_header.php';
?>
<div class="row justify-content-center py-4">
  <div class="col-md-7 col-lg-5">
    <h1 class="section-title">Admin login</h1>
    <div class="accent-line mb-4"></div>
    <?php if ($error): ?><div class="alert alert-danger" role="alert"><?= e($error) ?></div><?php endif; ?>
    <form class="auth-box p-4" method="post" autocomplete="on">
      <?= csrf_field() ?>
      <label class="form-label" for="email">Email</label>
      <input class="form-control" id="email" type="email" name="email" required autofocus>
      <label class="form-label mt-3" for="password">Password</label>
      <input class="form-control" id="password" type="password" name="password" required>
      <button class="btn btn-primary mt-4 w-100" type="submit">Log in</button>
    </form>
    <p class="mt-3"><a href="../index.php">Back to the website</a></p>
  </div>
</div>
<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
