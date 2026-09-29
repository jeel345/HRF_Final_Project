<?php
require_once 'config.php';
$pageTitle = 'President Profile - ' . APP_NAME;
$activePage = 'president';
$profiles = $pdo ? db()->query('SELECT * FROM president_profiles ORDER BY id')->fetchAll() : [];
include 'includes/header.php';
?>
<section class="py-5 band"><div class="container py-4">
  <h1 class="section-title">President Profile</h1><div class="accent-line mb-4"></div>
  <p class="lead">Meet the leaders of the federation's national and state bureaus.</p>
</div></section>
<section class="py-5"><div class="container"><div class="row g-4">
  <?php foreach ($profiles as $p): ?>
    <div class="col-md-6"><div class="profile-card h-100 overflow-hidden">
      <?php if ($p['image_path']): ?><img src="<?= e($p['image_path']) ?>" alt="<?= e($p['name']) ?>"><?php endif; ?>
      <div class="p-4"><h3><?= e($p['name']) ?></h3><p class="text-danger fw-semibold"><?= e($p['designation']) ?></p><p><?= nl2br(e($p['bio'])) ?></p></div>
    </div></div>
  <?php endforeach; ?>
  <?php if (!$profiles): ?><div class="col-12"><div class="admin-card p-4">No profiles have been added yet.</div></div><?php endif; ?>
</div></div></section>
<?php include 'includes/footer.php'; ?>
