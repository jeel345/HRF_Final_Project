<?php
require_once __DIR__ . '/init.php';
require_admin();
$pageTitle = 'Admins';
$adminPage = 'admins';
$myId = (int) $_SESSION['admin_id'];
$errors = [];
$addForm = ['name' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $addForm = ['name' => $name, 'email' => $email];
        if ($name === '') { $errors[] = 'Enter the new admin\'s name.'; }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Enter a valid email address.'; }
        if (strlen($password) < 8) { $errors[] = 'The password needs at least 8 characters.'; }
        if (!$errors) {
            try {
                db()->prepare('INSERT INTO admins (name, email, password) VALUES (?,?,?)')
                    ->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
                flash('Admin added.');
                redirect('admins.php');
            } catch (PDOException $e) {
                $errors[] = 'An admin with that email already exists.';
            }
        }
    } elseif ($action === 'password') {
        $current = $_POST['current'] ?? '';
        $new = $_POST['new'] ?? '';
        $confirm = $_POST['confirm'] ?? '';
        $st = db()->prepare('SELECT password FROM admins WHERE id = ?');
        $st->execute([$myId]);
        $row = $st->fetch();
        if (!$row || !password_verify($current, $row['password'])) {
            $errors[] = 'Your current password is not correct.';
        } elseif (strlen($new) < 8) {
            $errors[] = 'The new password needs at least 8 characters.';
        } elseif ($new !== $confirm) {
            $errors[] = 'The new password and confirmation do not match.';
        } else {
            db()->prepare('UPDATE admins SET password = ? WHERE id = ?')->execute([password_hash($new, PASSWORD_DEFAULT), $myId]);
            flash('Your password has been changed.');
            redirect('admins.php');
        }
    } elseif ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $total = (int) db()->query('SELECT COUNT(*) FROM admins')->fetchColumn();
        if ($id === $myId) {
            flash('You cannot delete the account you are logged in with.', 'error');
        } elseif ($total < 2) {
            flash('At least one admin must remain.', 'error');
        } else {
            db()->prepare('DELETE FROM admins WHERE id = ?')->execute([$id]);
            flash('Admin deleted.');
        }
        redirect('admins.php');
    }
}

$admins = db()->query('SELECT id, name, email, created_at FROM admins ORDER BY id')->fetchAll();

include __DIR__ . '/../includes/admin_header.php';
?>
<div class="admin-head mb-4">
  <h1>Admins</h1>
  <p>Manage who can sign in to this admin area.</p>
</div>

<?php foreach ($errors as $err): ?><div class="alert alert-danger" role="alert"><?= e($err) ?></div><?php endforeach; ?>

<div class="row g-4">
  <div class="col-lg-7">
    <div class="admin-card">
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead><tr><th>Name</th><th>Email</th><th>Added</th><th class="text-end">Action</th></tr></thead>
          <tbody>
          <?php foreach ($admins as $a): ?>
            <tr>
              <td><?= e($a['name']) ?><?= (int) $a['id'] === $myId ? ' <span class="badge text-bg-secondary">You</span>' : '' ?></td>
              <td><?= e($a['email']) ?></td>
              <td class="text-nowrap"><?= e(date('d M Y', strtotime($a['created_at']))) ?></td>
              <td class="text-end">
                <?php if ((int) $a['id'] !== $myId): ?>
                  <form method="post" onsubmit="return confirm('Delete this admin?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                    <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <form class="auth-box p-4 mt-4" method="post">
      <h3 class="mb-3">Add an admin</h3>
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="add">
      <label class="form-label" for="a_name">Name</label>
      <input class="form-control" id="a_name" name="name" value="<?= e($addForm['name']) ?>" required>
      <label class="form-label mt-3" for="a_email">Email</label>
      <input class="form-control" id="a_email" type="email" name="email" value="<?= e($addForm['email']) ?>" required>
      <label class="form-label mt-3" for="a_password">Password</label>
      <input class="form-control" id="a_password" type="password" name="password" minlength="8" required autocomplete="new-password">
      <div class="form-text">At least 8 characters.</div>
      <button class="btn btn-danger mt-4" type="submit">Add admin</button>
    </form>
  </div>

  <div class="col-lg-5">
    <form class="auth-box p-4" method="post">
      <h3 class="mb-3">Change your password</h3>
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="password">
      <label class="form-label" for="current">Current password</label>
      <input class="form-control" id="current" type="password" name="current" required autocomplete="current-password">
      <label class="form-label mt-3" for="new">New password</label>
      <input class="form-control" id="new" type="password" name="new" minlength="8" required autocomplete="new-password">
      <label class="form-label mt-3" for="confirm">Confirm new password</label>
      <input class="form-control" id="confirm" type="password" name="confirm" minlength="8" required autocomplete="new-password">
      <button class="btn btn-primary mt-4" type="submit">Change password</button>
    </form>
  </div>
</div>
<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
