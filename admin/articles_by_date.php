<?php
require_once __DIR__ . '/init.php';
require_admin();
$pageTitle = 'Articles by date';
$adminPage = 'bydate';

$valid = fn(string $d): bool => (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) && strtotime($d) !== false;
$from = trim($_GET['from'] ?? '');
$to = trim($_GET['to'] ?? '');
if ($from !== '' && !$valid($from)) { $from = ''; }
if ($to !== '' && !$valid($to)) { $to = ''; }

$sql = 'SELECT id, title, image_path, created_at, DATE(created_at) AS day FROM articles WHERE 1=1';
$params = [];
if ($from !== '') { $sql .= ' AND DATE(created_at) >= ?'; $params[] = $from; }
if ($to !== '')   { $sql .= ' AND DATE(created_at) <= ?'; $params[] = $to; }
$sql .= ' ORDER BY created_at DESC';
$st = db()->prepare($sql);
$st->execute($params);

$byDay = [];
foreach ($st->fetchAll() as $a) {
    $byDay[$a['day']][] = $a;
}
$total = array_sum(array_map('count', $byDay));

include __DIR__ . '/../includes/admin_header.php';
?>
<div class="admin-head mb-4">
  <h1>Articles by date</h1>
  <p>See what was published on each day. <?= $total ?> <?= $total === 1 ? 'article' : 'articles' ?> across <?= count($byDay) ?> <?= count($byDay) === 1 ? 'day' : 'days' ?>.</p>
</div>

<form class="admin-card p-3 mb-4 d-flex flex-wrap align-items-end gap-3" method="get">
  <div>
    <label class="form-label" for="from">From</label>
    <input class="form-control" id="from" type="date" name="from" value="<?= e($from) ?>">
  </div>
  <div>
    <label class="form-label" for="to">To</label>
    <input class="form-control" id="to" type="date" name="to" value="<?= e($to) ?>">
  </div>
  <button class="btn btn-primary" type="submit">Filter</button>
  <?php if ($from !== '' || $to !== ''): ?><a class="btn btn-outline-navy" href="articles_by_date.php">Clear</a><?php endif; ?>
</form>

<?php foreach ($byDay as $day => $items): ?>
  <section class="mb-4">
    <h4 class="day-head"><?= e(date('l, d F Y', strtotime($day))) ?> <span class="text-secondary fs-6">(<?= count($items) ?>)</span></h4>
    <div class="admin-card">
      <ul class="list-group list-group-flush">
        <?php foreach ($items as $a): ?>
          <li class="list-group-item d-flex align-items-center gap-3 py-3">
            <?php if ($a['image_path']): ?><img class="thumb" src="../<?= e($a['image_path']) ?>" alt=""><?php endif; ?>
            <div class="flex-grow-1">
              <div class="fw-semibold"><?= e($a['title']) ?></div>
              <small class="text-secondary">Published at <?= e(date('h:i A', strtotime($a['created_at']))) ?></small>
            </div>
            <a class="btn btn-sm btn-outline-navy" href="articles.php?edit=<?= (int) $a['id'] ?>">Edit</a>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </section>
<?php endforeach; ?>
<?php if (!$byDay): ?>
  <div class="admin-card p-4">No articles were published in this period.</div>
<?php endif; ?>
<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
