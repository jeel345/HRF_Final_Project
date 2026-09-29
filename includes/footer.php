</main>

<footer class="site-footer">
  <div class="container">
    <div class="row g-5">
      <div class="col-lg-5">
        <p class="footer-brand">Human Rights Federation</p>
        <p class="footer-copy">We document violations, educate communities about their rights, and support people who need a voice.</p>
      </div>
      <div class="col-6 col-lg-3 offset-lg-1">
        <h6>Explore</h6>
        <ul class="list-unstyled footer-links">
          <li><a href="<?= $root ?? '' ?>index.php">Home</a></li>
          <li><a href="<?= $root ?? '' ?>about.php">About us</a></li>
          <li><a href="<?= $root ?? '' ?>articles.php">Articles</a></li>
          <li><a href="<?= $root ?? '' ?>president.php">Leadership</a></li>
        </ul>
      </div>
      <div class="col-6 col-lg-3">
        <h6>Get in touch</h6>
        <ul class="list-unstyled footer-links">
          <li><a href="<?= $root ?? '' ?>contact.php">Send a message</a></li>
          <li><a href="mailto:<?= e(ADMIN_EMAIL) ?>"><?= e(ADMIN_EMAIL) ?></a></li>
        </ul>
      </div>
    </div>
    <div class="footer-base">&copy; <?= date('Y') ?> <?= e(APP_NAME) ?>. All rights reserved.</div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  function googleTranslateElementInit() {
    new google.translate.TranslateElement({ pageLanguage: 'en', layout: google.translate.TranslateElement.InlineLayout.SIMPLE }, 'google_translate_element');
  }
</script>
<script src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit" async></script>
</body>
</html>
