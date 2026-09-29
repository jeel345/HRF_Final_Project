<?php
require_once __DIR__ . '/init.php';
require_admin();
$pageTitle = 'Users';
$adminPage = 'users';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $id = (int) ($_POST['id'] ?? 0);
    if (($_POST['action'] ?? '') === 'delete' && $id) {
        db()->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
        flash('User deleted. Their past messages stay in the inbox.');
    }
    redirect('users.php');
}

$q = trim($_GET['q'] ?? '');
$where = '';
$params = [];
if ($q !== '') {
    $where = 'WHERE name LIKE ? OR email LIKE ? OR phone LIKE ?';
    $params = ["%$q%", "%$q%", "%$q%"];
}
$total = db()->prepare("SELECT COUNT(*) FROM users $where");
$total->execute($params);
$total = (int) $total->fetchColumn();
[$page, $pages, $offset] = pager($total, 20);

$st = db()->prepare("SELECT u.*, (SELECT COUNT(*) FROM contact_messages m WHERE m.user_id = u.id) AS msg_count
                     FROM users u $where ORDER BY u.created_at DESC LIMIT ? OFFSET ?");
foreach ($params as $i => $p) {
    $st->bindValue($i + 1, $p);
}
$st->bindValue(count($params) + 1, 20, PDO::PARAM_INT);
$st->bindValue(count($params) + 2, $offset, PDO::PARAM_INT);
$st->execute();
$users = $st->fetchAll();

include __DIR__ . '/../includes/admin_header.php';
?>
<div class="admin-head d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
  <div>
    <h1>Registered users</h1>
    <p class="mb-0"><?= $total ?> <?= $total === 1 ? 'user' : 'users' ?><?= $q !== '' ? ' match your search' : ' registered' ?>.</p>
  </div>
  <form class="d-flex gap-2" method="get">
    <input class="form-control" type="search" name="q" value="<?= e($q) ?>" placeholder="Search name, email or phone" aria-label="Search users">
    <button class="btn btn-primary" type="submit">Search</button>
  </form>
</div>

<div class="admin-card">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Joined</th><th>Messages</th><th class="text-end">Action</th></tr></thead>
      <tbody>
      <?php foreach ($users as $u): ?>
        <tr>
          <td><?= e($u['name']) ?></td>
          <td><a href="mailto:<?= e($u['email']) ?>"><?= e($u['email']) ?></a></td>
          <td><?= e($u['phone'] ?: '-') ?></td>
          <td class="text-nowrap"><?= e(date('d M Y', strtotime($u['created_at']))) ?></td>
          <td><?= (int) $u['msg_count'] ?></td>
          <td class="text-end">
            <form method="post" onsubmit="return confirm('Delete this user? This cannot be undone.');">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
              <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$users): ?>
        <tr><td colspan="6" class="text-center text-secondary py-4"><?= $q !== '' ? 'No users match that search.' : 'No one has registered yet.' ?></td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<div class="mt-3"><?= pager_html($page, $pages) ?></div>
<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
