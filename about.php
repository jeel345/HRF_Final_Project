<?php
require_once 'config.php';
$pageTitle = 'About Us - ' . APP_NAME;
$activePage = 'about';
include 'includes/header.php';
?>
<section class="py-5 band"><div class="container py-4">
  <h1 class="section-title">About Us</h1><div class="accent-line mb-4"></div>
  <p class="lead">The Human Rights Federation works for the protection of fundamental rights through awareness, documentation, and community representation.</p>
</div></section>
<section class="py-5"><div class="container"><div class="row g-4">
  <div class="col-md-4"><div class="admin-card p-4 h-100"><h4>Our mission</h4><p>To make sure every person knows their rights and can seek justice without fear.</p></div></div>
  <div class="col-md-4"><div class="admin-card p-4 h-100"><h4>What we do</h4><p>Rights education, complaint documentation, legal awareness campaigns, and coordination between national and state bureaus.</p></div></div>
  <div class="col-md-4"><div class="admin-card p-4 h-100"><h4>Get involved</h4><p><a href="login.php">Register</a> to contact the federation and receive replies from our team.</p></div></div>
</div></div></section>
<?php include 'includes/footer.php'; ?>
