<?php
require_once 'config.php';
$pageTitle = 'Contact Us - ' . APP_NAME;
$activePage = 'contact';
$message = '';
if (!is_user_logged_in()) {
    header('Location: login.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject'] ?? '');
    $body = trim($_POST['message'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    if ($subject && $body) {
        $u = db()->prepare('SELECT name, email FROM users WHERE id = ?');
        $u->execute([$_SESSION['user_id']]);
        $user = $u->fetch();
        db()->prepare('INSERT INTO contact_messages (user_id, name, email, phone, subject, message) VALUES (?,?,?,?,?,?)')
            ->execute([$_SESSION['user_id'], $user['name'], $user['email'], $phone, $subject, $body]);
        $message = 'Your message has been sent. Replies will appear below.';
    } else {
        $message = 'Please enter a subject and a message.';
    }
}
$mine = db()->prepare('SELECT * FROM contact_messages WHERE user_id = ? ORDER BY created_at DESC');
$mine->execute([$_SESSION['user_id']]);
$mine = $mine->fetchAll();
include 'includes/header.php';
?>
<section class="py-5 band"><div class="container py-4">
  <h1 class="section-title">Contact Us</h1><div class="accent-line mb-4"></div>
  <p class="lead">Send us a message. Our team replies here and by email.</p>
</div></section>
<section class="py-5"><div class="container">
  <?php if ($message): ?><div class="alert alert-info"><?= e($message) ?></div><?php endif; ?>
  <div class="row g-4">
    <div class="col-lg-5">
      <form class="auth-box p-4" method="post">
        <label class="form-label">Phone (optional)</label><input class="form-control" name="phone">
        <label class="form-label mt-3">Subject</label><input class="form-control" name="subject" required>
        <label class="form-label mt-3">Message</label><textarea class="form-control" name="message" rows="5" required></textarea>
        <button class="btn btn-danger mt-4" type="submit">Send message</button>
      </form>
    </div>
    <div class="col-lg-7">
      <h4>Your messages</h4>
      <?php foreach ($mine as $m): ?>
        <div class="admin-card p-3 mb-3">
          <small class="text-secondary"><?= e(date('d M Y, h:i A', strtotime($m['created_at']))) ?></small>
          <h5 class="mb-1"><?= e($m['subject']) ?></h5>
          <p><?= nl2br(e($m['message'])) ?></p>
          <?php if ($m['admin_reply']): ?><div class="reply-box"><strong>Admin reply</strong><br><?= nl2br(e($m['admin_reply'])) ?></div>
          <?php else: ?><small class="text-secondary">Waiting for a reply.</small><?php endif; ?>
        </div>
      <?php endforeach; ?>
      <?php if (!$mine): ?><div class="admin-card p-4">You have not sent any messages yet.</div><?php endif; ?>
    </div>
  </div>
</div></section>
<?php include 'includes/footer.php'; ?>
