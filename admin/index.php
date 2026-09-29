<?php
require_once __DIR__ . '/init.php';
require_admin();
$pageTitle = 'Dashboard';
$adminPage = 'dashboard';

$count = fn(string $sql): int => (int) db()->query($sql)->fetchColumn();
$stats = [
    ['Registered users', $count('SELECT COUNT(*) FROM users'), 'users.php', false],
    ['Articles', $count('SELECT COUNT(*) FROM articles'), 'articles.php', false],
    ['Messages', $count('SELECT COUNT(*) FROM contact_messages'), 'messages.php', false],
    ['Waiting for a reply', $count("SELECT COUNT(*) FROM contact_messages WHERE admin_reply IS NULL OR admin_reply = ''"), 'messages.php?show=unreplied', true],
];
$recent = db()->query('SELECT id, name, subject, created_at, admin_reply FROM contact_messages ORDER BY created_at DESC LIMIT 5')->fetchAll();

$me = db()->prepare('SELECT password FROM admins WHERE id = ?');
$me->execute([$_SESSION['admin_id']]);
$row = $me->fetch();
$defaultPassword = $row && password_verify('admin123', $row['password']);

include __DIR__ . '/../includes/admin_header.php';
?>
<div class="admin-head mb-4">
  <h1>Dashboard</h1>
  <p>Welcome, <?= e($_SESSION['admin_name'] ?? 'Administrator') ?>.</p>
</div>

<?php if ($defaultPassword): ?>
  <div class="alert alert-warning" role="alert">You are still using the default password. <a href="admins.php">Change it now</a> before the site goes online.</div>
<?php endif; ?>

<div class="row g-3 mb-4">
  <?php foreach ($stats as [$label, $num, $href, $alert]): ?>
    <div class="col-6 col-lg-3">
      <a class="stat d-block text-decoration-none<?= $alert && $num ? ' alert-stat' : '' ?>" href="<?= e($href) ?>">
        <div class="num"><?= $num ?></div>
        <div class="label"><?= e($label) ?></div>
      </a>
    </div>
  <?php endforeach; ?>
</div>

<div class="row g-4">
  <div class="col-lg-8">
    <div class="admin-card p-4">
      <h4 class="mb-3">Latest messages</h4>
      <?php if (!$recent): ?>
        <p class="text-secondary mb-0">No messages yet. They will appear here when registered users write to the federation.</p>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table align-middle mb-0">
            <thead><tr><th>From</th><th>Subject</th><th>Date</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($recent as $m): ?>
              <tr>
                <td><?= e($m['name']) ?></td>
                <td><a href="messages.php#m<?= (int) $m['id'] ?>"><?= e($m['subject']) ?></a></td>
                <td class="text-nowrap"><?= e(date('d M Y', strtotime($m['created_at']))) ?></td>
                <td><?= $m['admin_reply'] ? '<span class="badge text-bg-success">Replied</span>' : '<span class="badge text-bg-danger">Waiting</span>' ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="admin-card p-4">
      <h4 class="mb-3">Quick actions</h4>
      <div class="d-grid gap-2">
        <a class="btn btn-crimson" href="articles.php">Publish an article</a>
        <a class="btn btn-outline-navy" href="messages.php?show=unreplied">Reply to messages</a>
        <a class="btn btn-outline-navy" href="profiles.php">Edit leadership profiles</a>
        <a class="btn btn-outline-navy" href="articles_by_date.php">Browse articles by date</a>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
