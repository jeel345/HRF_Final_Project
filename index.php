<?php
require_once 'config.php';
$pageTitle = 'Home - ' . APP_NAME;
$activePage = 'home';
$latest = $pdo ? db()->query('SELECT * FROM articles ORDER BY created_at DESC LIMIT 3')->fetchAll() : [];
include 'includes/header.php';
?>
<section class="hero py-5">
  <div class="container py-5">
    <h1>Standing for justice, dignity and equal rights.</h1>
    <p class="lead mt-3">We document violations, educate communities about their rights, and support people who need a voice.</p>
    <a class="btn btn-crimson mt-2" href="contact.php">Contact the federation</a>
    <a class="btn btn-ghost mt-2 ms-2" href="about.php">About us</a>
  </div>
</section>
<section class="pillars"><div class="container"><div class="row">
  <div class="col-md-4"><div class="pillar"><h5>Awareness</h5><p>Rights education for every community.</p></div></div>
  <div class="col-md-4"><div class="pillar"><h5>Documentation</h5><p>Careful records of complaints and cases.</p></div></div>
  <div class="col-md-4"><div class="pillar"><h5>Representation</h5><p>National and state bureaus working together.</p></div></div>
</div></div></section>
<section class="py-5">
  <div class="container">
    <h2 class="section-title">Latest articles</h2><div class="accent-line mb-4"></div>
    <div class="row g-4">
      <?php foreach ($latest as $a): ?>
        <div class="col-md-4"><article class="article-card h-100 overflow-hidden">
          <?php if ($a['image_path']): ?><img src="<?= e($a['image_path']) ?>" alt="<?= e($a['title']) ?>"><?php endif; ?>
          <div class="p-4"><small class="text-secondary"><?= e(date('d M Y', strtotime($a['created_at']))) ?></small>
          <h4 class="mt-2"><?= e($a['title']) ?></h4>
          <p class="text-secondary"><?= e(mb_strimwidth($a['description'], 0, 140, '...')) ?></p></div>
        </article></div>
      <?php endforeach; ?>
      <?php if (!$latest): ?><div class="col-12"><div class="admin-card p-4">No articles have been published yet.</div></div><?php endif; ?>
    </div>
  </div>
</section>
<?php include 'includes/footer.php'; ?>
