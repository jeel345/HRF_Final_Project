<?php
// Shared header. Expects $pageTitle and $activePage to be set by the page.
$root       = $root ?? (preg_match('#/admin$#', str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''))) ? '../' : '');
$pageTitle  = $pageTitle ?? APP_NAME;
$activePage = $activePage ?? '';
$nav = [
    'home'      => ['index.php',     'Home'],
    'about'     => ['about.php',     'About us'],
    'articles'  => ['articles.php',  'Articles'],
    'president' => ['president.php', 'Leadership'],
    'contact'   => ['contact.php',   'Contact'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700&family=Source+Serif+4:opsz,wght@8..60,400;8..60,600;8..60,700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= $root ?>assets/css/style.css" rel="stylesheet">
</head>
<body>
<a class="skip-link" href="#main">Skip to content</a>

<div class="topbar">
  <div class="container d-flex justify-content-between align-items-center gap-3">
    <a class="topbar-mail" href="mailto:<?= e(ADMIN_EMAIL) ?>"><?= e(ADMIN_EMAIL) ?></a>
    <div id="google_translate_element" aria-label="Translate this page"></div>
  </div>
</div>

<header class="site-header">
  <nav class="navbar navbar-expand-lg">
    <div class="container">
      <a class="brand" href="<?= $root ?>index.php">
        <img src="logo.png" height="100px" width="100px">
        <!-- <svg class="brand-mark" viewBox="0 0 40 40" aria-hidden="true">
          <circle cx="20" cy="20" r="19" fill="#101B3D"/>
          <path d="M20 1a19 19 0 0 1 0 38z" fill="#C8102E"/>
          <path d="M20 9v22M11 15h18" stroke="#fff" stroke-width="2.4" stroke-linecap="round" fill="none"/>
          <path d="M11 15l-3.5 8h7zM29 15l-3.5 8h7z" fill="#fff"/>
        </svg> -->
        <span class="brand-text">Human Rights<br>Federation</span>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Open menu">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="mainNav">
        <ul class="navbar-nav ms-auto align-items-lg-center">
          <?php foreach ($nav as $key => [$href, $label]): ?>
            <li class="nav-item">
              <a class="nav-link<?= $activePage === $key ? ' active' : '' ?>" href="<?= $root . $href ?>"<?= $activePage === $key ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
            </li>
          <?php endforeach; ?>
          <li class="nav-item ms-lg-3 mt-2 mt-lg-0">
            <?php if (is_user_logged_in()): ?>
              <span class="nav-user me-2"><?= e($_SESSION['user_name'] ?? 'Member') ?></span>
              <a class="btn btn-outline-navy btn-sm" href="<?= $root ?>logout.php">Log out</a>
            <?php else: ?>
              <a class="btn btn-crimson btn-sm" href="<?= $root ?>login.php">Log in or register</a>
            <?php endif; ?>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</header>
<main id="main">
