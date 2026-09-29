<?php
// Admin header. Pages set $pageTitle, $adminPage and optionally $hideNav.
$adminPage = $adminPage ?? '';
$hideNav   = $hideNav ?? false;
$fullTitle = ($pageTitle ?? 'Admin') . ' - ' . APP_NAME . ' Admin';
$links = [
    'dashboard' => ['index.php', 'Dashboard'],
    'users'     => ['users.php', 'Users'],
    'articles'  => ['articles.php', 'Articles'],
    'bydate'    => ['articles_by_date.php', 'By date'],
    'messages'  => ['messages.php', 'Messages'],
    'profiles'  => ['profiles.php', 'Leadership'],
    'admins'    => ['admins.php', 'Admins'],
];
$unreplied = 0;
if (!$hideNav && $pdo) {
    $unreplied = (int) db()->query("SELECT COUNT(*) FROM contact_messages WHERE admin_reply IS NULL OR admin_reply = ''")->fetchColumn();
}
$flash = pull_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title><?= e($fullTitle) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700&family=Source+Serif+4:opsz,wght@8..60,400;8..60,600;8..60,700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="admin-body">
<a class="skip-link" href="#main">Skip to content</a>
<?php if (!$hideNav): ?>
<nav class="navbar navbar-expand-lg navbar-dark admin-bar">
  <div class="container">
   <img src="logo.png" height="100px" width="100px" style="border-radius:50%;">
    <a class="navbar-brand" href="index.php">HRF Admin</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav" aria-controls="adminNav" aria-expanded="false" aria-label="Open menu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="adminNav">
      <ul class="navbar-nav me-auto">
        <?php foreach ($links as $key => [$href, $label]): ?>
          <li class="nav-item">
            <a class="nav-link<?= $adminPage === $key ? ' active' : '' ?>" href="<?= $href ?>"<?= $adminPage === $key ? ' aria-current="page"' : '' ?>>
              <?= e($label) ?><?php if ($key === 'messages' && $unreplied): ?> <span class="badge rounded-pill"><?= $unreplied ?></span><?php endif; ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
      <ul class="navbar-nav align-items-lg-center">
        <li class="nav-item"><a class="nav-link" href="../index.php" target="_blank" rel="noopener">View site</a></li>
        <li class="nav-item"><span class="nav-link disabled"><?= e($_SESSION['admin_name'] ?? '') ?></span></li>
        <li class="nav-item"><a class="btn btn-ghost btn-sm" href="logout.php">Log out</a></li>
      </ul>
    </div>
  </div>
</nav>
<?php endif; ?>
<main id="main" class="admin-main container py-4">
<?php if ($flash): ?>
  <div class="alert alert-<?= $flash[0] === 'error' ? 'danger' : 'success' ?>" role="status"><?= e($flash[1]) ?></div>
<?php endif; ?>
