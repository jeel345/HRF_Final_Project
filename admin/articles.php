<?php
require_once __DIR__ . '/init.php';
require_admin();
$pageTitle = 'Articles';
$adminPage = 'articles';

$errors = [];
$form = ['id' => 0, 'title' => '', 'description' => '', 'image_path' => null];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? 'save';
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'delete' && $id) {
        $st = db()->prepare('SELECT image_path FROM articles WHERE id = ?');
        $st->execute([$id]);
        if ($row = $st->fetch()) {
            db()->prepare('DELETE FROM articles WHERE id = ?')->execute([$id]);
            delete_upload($row['image_path']);
            flash('Article deleted.');
        }
        redirect('articles.php');
    }

    $title = trim($_POST['title'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    if ($title === '') {
        $errors[] = 'Enter a title.';
    } elseif (mb_strlen($title) > 220) {
        $errors[] = 'The title must be 220 characters or fewer.';
    }
    if ($desc === '') {
        $errors[] = 'Enter the article text.';
    }

    $existing = null;
    if ($id) {
        $st = db()->prepare('SELECT * FROM articles WHERE id = ?');
        $st->execute([$id]);
        $existing = $st->fetch() ?: null;
        if (!$existing) {
            $errors[] = 'That article no longer exists.';
        }
    }

    $newImage = null;
    if (!$errors) {
        $imgError = null;
        $newImage = save_image_field('image', 'articles', $imgError);
        if ($imgError) {
            $errors[] = $imgError;
        }
    }

    if (!$errors) {
        if ($existing) {
            $path = $existing['image_path'];
            if ($newImage) {
                delete_upload($path);
                $path = $newImage;
            } elseif (!empty($_POST['remove_image'])) {
                delete_upload($path);
                $path = null;
            }
            db()->prepare('UPDATE articles SET title = ?, description = ?, image_path = ? WHERE id = ?')
                ->execute([$title, $desc, $path, $id]);
            flash('Article updated.');
        } else {
            db()->prepare('INSERT INTO articles (title, image_path, description, admin_id) VALUES (?,?,?,?)')
                ->execute([$title, $newImage, $desc, $_SESSION['admin_id']]);
            flash('Article published.');
        }
        redirect('articles.php');
    }
    $form = ['id' => $id, 'title' => $title, 'description' => $desc, 'image_path' => $existing['image_path'] ?? null];
} elseif (isset($_GET['edit'])) {
    $st = db()->prepare('SELECT * FROM articles WHERE id = ?');
    $st->execute([(int) $_GET['edit']]);
    if ($row = $st->fetch()) {
        $form = $row;
    }
}

$articles = db()->query('SELECT id, title, image_path, created_at FROM articles ORDER BY created_at DESC')->fetchAll();
$editing = (int) $form['id'] > 0;

include __DIR__ . '/../includes/admin_header.php';
?>
<div class="admin-head mb-4">
  <h1>Articles</h1>
  <p>Articles appear on the public Articles page and the latest three on the home page.</p>
</div>

<div class="row g-4">
  <div class="col-lg-5">
    <form class="auth-box p-4" method="post" enctype="multipart/form-data">
      <h3 class="mb-3"><?= $editing ? 'Edit article' : 'New article' ?></h3>
      <?php foreach ($errors as $err): ?><div class="alert alert-danger py-2" role="alert"><?= e($err) ?></div><?php endforeach; ?>
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= (int) $form['id'] ?>">

      <label class="form-label" for="title">Title</label>
      <input class="form-control" id="title" name="title" maxlength="220" value="<?= e($form['title']) ?>" required>

      <label class="form-label mt-3" for="description">Article text</label>
      <textarea class="form-control" id="description" name="description" rows="8" required><?= e($form['description']) ?></textarea>

      <label class="form-label mt-3" for="image">Image (optional)</label>
      <?php if ($form['image_path']): ?>
        <div class="mb-2">
          <img class="thumb-lg" src="../<?= e($form['image_path']) ?>" alt="Current article image">
          <div class="form-check mt-2">
            <input class="form-check-input" type="checkbox" name="remove_image" value="1" id="remove_image">
            <label class="form-check-label" for="remove_image">Remove this image</label>
          </div>
        </div>
      <?php endif; ?>
      <input class="form-control" id="image" type="file" name="image" accept="image/jpeg,image/png,image/webp">
      <div class="form-text">JPG, PNG or WebP, up to 3 MB.<?= $form['image_path'] ? ' Choose a file to replace the current image.' : '' ?></div>

      <div class="d-flex gap-2 mt-4">
        <button class="btn btn-danger" type="submit"><?= $editing ? 'Save changes' : 'Publish article' ?></button>
        <?php if ($editing): ?><a class="btn btn-outline-navy" href="articles.php">Cancel</a><?php endif; ?>
      </div>
    </form>
  </div>

  <div class="col-lg-7">
    <div class="admin-card">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead><tr><th>Image</th><th>Title</th><th>Published</th><th class="text-end">Actions</th></tr></thead>
          <tbody>
          <?php foreach ($articles as $a): ?>
            <tr>
              <td><?php if ($a['image_path']): ?><img class="thumb" src="../<?= e($a['image_path']) ?>" alt=""><?php else: ?><span class="text-secondary">None</span><?php endif; ?></td>
              <td><?= e($a['title']) ?></td>
              <td class="text-nowrap"><?= e(date('d M Y', strtotime($a['created_at']))) ?></td>
              <td class="text-end text-nowrap">
                <a class="btn btn-sm btn-outline-navy" href="articles.php?edit=<?= (int) $a['id'] ?>">Edit</a>
                <form class="d-inline" method="post" onsubmit="return confirm('Delete this article? This cannot be undone.');">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                  <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$articles): ?>
            <tr><td colspan="4" class="text-center text-secondary py-4">No articles yet. Use the form to publish the first one.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
