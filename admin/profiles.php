<?php
require_once __DIR__ . '/init.php';
require_admin();
$pageTitle = 'Leadership profiles';
$adminPage = 'profiles';

$errors = [];
$form = ['id' => 0, 'name' => '', 'designation' => '', 'bio' => '', 'image_path' => null];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? 'save';
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'delete' && $id) {
        $st = db()->prepare('SELECT image_path FROM president_profiles WHERE id = ?');
        $st->execute([$id]);
        if ($row = $st->fetch()) {
            db()->prepare('DELETE FROM president_profiles WHERE id = ?')->execute([$id]);
            delete_upload($row['image_path']);
            flash('Profile deleted.');
        }
        redirect('profiles.php');
    }

    $name = trim($_POST['name'] ?? '');
    $designation = trim($_POST['designation'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    if ($name === '' || mb_strlen($name) > 140) { $errors[] = 'Enter a name of up to 140 characters.'; }
    if ($designation === '' || mb_strlen($designation) > 160) { $errors[] = 'Enter a designation of up to 160 characters.'; }
    if ($bio === '') { $errors[] = 'Enter a short bio.'; }

    $existing = null;
    if ($id) {
        $st = db()->prepare('SELECT * FROM president_profiles WHERE id = ?');
        $st->execute([$id]);
        $existing = $st->fetch() ?: null;
        if (!$existing) { $errors[] = 'That profile no longer exists.'; }
    }

    $newImage = null;
    if (!$errors) {
        $imgError = null;
        $newImage = save_image_field('image', 'profiles', $imgError);
        if ($imgError) { $errors[] = $imgError; }
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
            db()->prepare('UPDATE president_profiles SET name = ?, designation = ?, bio = ?, image_path = ? WHERE id = ?')
                ->execute([$name, $designation, $bio, $path, $id]);
            flash('Profile updated.');
        } else {
            db()->prepare('INSERT INTO president_profiles (name, designation, bio, image_path) VALUES (?,?,?,?)')
                ->execute([$name, $designation, $bio, $newImage]);
            flash('Profile added.');
        }
        redirect('profiles.php');
    }
    $form = ['id' => $id, 'name' => $name, 'designation' => $designation, 'bio' => $bio, 'image_path' => $existing['image_path'] ?? null];
} elseif (isset($_GET['edit'])) {
    $st = db()->prepare('SELECT * FROM president_profiles WHERE id = ?');
    $st->execute([(int) $_GET['edit']]);
    if ($row = $st->fetch()) { $form = $row; }
}

$profiles = db()->query('SELECT * FROM president_profiles ORDER BY id')->fetchAll();
$editing = (int) $form['id'] > 0;

include __DIR__ . '/../includes/admin_header.php';
?>
<div class="admin-head mb-4">
  <h1>Leadership profiles</h1>
  <p>These appear on the public Leadership page, in the order they were added.</p>
</div>

<div class="row g-4">
  <div class="col-lg-5">
    <form class="auth-box p-4" method="post" enctype="multipart/form-data">
      <h3 class="mb-3"><?= $editing ? 'Edit profile' : 'New profile' ?></h3>
      <?php foreach ($errors as $err): ?><div class="alert alert-danger py-2" role="alert"><?= e($err) ?></div><?php endforeach; ?>
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= (int) $form['id'] ?>">

      <label class="form-label" for="name">Name</label>
      <input class="form-control" id="name" name="name" maxlength="140" value="<?= e($form['name']) ?>" required>

      <label class="form-label mt-3" for="designation">Designation</label>
      <input class="form-control" id="designation" name="designation" maxlength="160" value="<?= e($form['designation']) ?>" required>

      <label class="form-label mt-3" for="bio">Bio</label>
      <textarea class="form-control" id="bio" name="bio" rows="6" required><?= e($form['bio']) ?></textarea>

      <label class="form-label mt-3" for="image">Photo (optional)</label>
      <?php if ($form['image_path']): ?>
        <div class="mb-2">
          <img class="thumb-lg" src="../<?= e($form['image_path']) ?>" alt="Current photo">
          <div class="form-check mt-2">
            <input class="form-check-input" type="checkbox" name="remove_image" value="1" id="remove_image">
            <label class="form-check-label" for="remove_image">Remove this photo</label>
          </div>
        </div>
      <?php endif; ?>
      <input class="form-control" id="image" type="file" name="image" accept="image/jpeg,image/png,image/webp">
      <div class="form-text">JPG, PNG or WebP, up to 3 MB.</div>

      <div class="d-flex gap-2 mt-4">
        <button class="btn btn-danger" type="submit"><?= $editing ? 'Save changes' : 'Add profile' ?></button>
        <?php if ($editing): ?><a class="btn btn-outline-navy" href="profiles.php">Cancel</a><?php endif; ?>
      </div>
    </form>
  </div>

  <div class="col-lg-7">
    <?php foreach ($profiles as $p): ?>
      <div class="admin-card p-3 mb-3 d-flex gap-3 align-items-start">
        <?php if ($p['image_path']): ?><img class="thumb-lg" src="../<?= e($p['image_path']) ?>" alt=""><?php endif; ?>
        <div class="flex-grow-1">
          <h5 class="mb-0"><?= e($p['name']) ?></h5>
          <div class="text-danger fw-semibold mb-2"><?= e($p['designation']) ?></div>
          <p class="text-secondary mb-3"><?= e(mb_strimwidth($p['bio'], 0, 160, '...')) ?></p>
          <a class="btn btn-sm btn-outline-navy" href="profiles.php?edit=<?= (int) $p['id'] ?>">Edit</a>
          <form class="d-inline" method="post" onsubmit="return confirm('Delete this profile?');">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
            <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
    <?php if (!$profiles): ?><div class="admin-card p-4">No profiles yet. Use the form to add the first one.</div><?php endif; ?>
  </div>
</div>
<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
