<?php
require_once __DIR__ . '/init.php';
require_admin();
$pageTitle = 'Messages';
$adminPage = 'messages';

function clean_header(string $s): string {
    return trim(str_replace(["\r", "\n"], ' ', $s));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $id = (int) ($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($action === 'delete' && $id) {
        db()->prepare('DELETE FROM contact_messages WHERE id = ?')->execute([$id]);
        flash('Message deleted.');
    } elseif ($action === 'reply' && $id) {
        $reply = trim($_POST['reply'] ?? '');
        $st = db()->prepare('SELECT * FROM contact_messages WHERE id = ?');
        $st->execute([$id]);
        $msg = $st->fetch();
        if (!$msg) {
            flash('That message no longer exists.', 'error');
        } elseif ($reply === '') {
            flash('Write a reply before sending.', 'error');
        } else {
            db()->prepare('UPDATE contact_messages SET admin_reply = ?, replied_at = NOW(), replied_by = ? WHERE id = ?')
                ->execute([$reply, $_SESSION['admin_id'], $id]);

            $sent = false;
            if (filter_var($msg['email'], FILTER_VALIDATE_EMAIL)) {
                $subject = 'Re: ' . clean_header($msg['subject']);
                $body = "Hello {$msg['name']},\n\n{$reply}\n\n---\nYour message: {$msg['subject']}\n{$msg['message']}\n\n" . APP_NAME;
                $headers = 'From: ' . APP_NAME . ' <' . ADMIN_EMAIL . ">\r\n"
                         . 'Reply-To: ' . ADMIN_EMAIL . "\r\n"
                         . "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\n";
                $sent = @mail($msg['email'], '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, $headers);
            }
            flash($sent
                ? 'Reply saved and emailed to ' . $msg['email'] . '.'
                : 'Reply saved and shown on the user\'s Contact page. The email was not sent because mail is not set up on this server.',
                $sent ? 'success' : 'success');
        }
    }
    redirect('messages.php' . (($_POST['show'] ?? '') === 'unreplied' ? '?show=unreplied' : ''));
}

$show = ($_GET['show'] ?? '') === 'unreplied' ? 'unreplied' : 'all';
$where = $show === 'unreplied' ? "WHERE admin_reply IS NULL OR admin_reply = ''" : '';
$total = (int) db()->query("SELECT COUNT(*) FROM contact_messages $where")->fetchColumn();
[$page, $pages, $offset] = pager($total, 10);
$st = db()->prepare("SELECT * FROM contact_messages $where ORDER BY created_at DESC LIMIT ? OFFSET ?");
$st->bindValue(1, 10, PDO::PARAM_INT);
$st->bindValue(2, $offset, PDO::PARAM_INT);
$st->execute();
$messages = $st->fetchAll();

include __DIR__ . '/../includes/admin_header.php';
?>
<div class="admin-head d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
  <div>
    <h1>Messages</h1>
    <p class="mb-0">Replies are saved on the user's Contact page and emailed to them.</p>
  </div>
  <div class="btn-group" role="group" aria-label="Filter messages">
    <a class="btn <?= $show === 'all' ? 'btn-primary' : 'btn-outline-navy' ?>" href="messages.php">All</a>
    <a class="btn <?= $show === 'unreplied' ? 'btn-primary' : 'btn-outline-navy' ?>" href="messages.php?show=unreplied">Waiting for a reply</a>
  </div>
</div>

<?php foreach ($messages as $m): $replied = !empty($m['admin_reply']); ?>
  <article class="admin-card p-4 mb-4" id="m<?= (int) $m['id'] ?>">
    <div class="d-flex flex-wrap justify-content-between gap-2">
      <div>
        <h4 class="mb-1"><?= e($m['subject']) ?></h4>
        <div class="text-secondary small">
          <?= e($m['name']) ?> &middot; <a href="mailto:<?= e($m['email']) ?>"><?= e($m['email']) ?></a><?= $m['phone'] ? ' &middot; ' . e($m['phone']) : '' ?>
        </div>
      </div>
      <div class="text-end small text-secondary">
        <?= e(date('d M Y, h:i A', strtotime($m['created_at']))) ?><br>
        <?= $replied ? '<span class="badge text-bg-success">Replied</span>' : '<span class="badge text-bg-danger">Waiting</span>' ?>
      </div>
    </div>
    <p class="mt-3 mb-3"><?= nl2br(e($m['message'])) ?></p>

    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="reply">
      <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
      <input type="hidden" name="show" value="<?= e($show) ?>">
      <label class="form-label" for="reply<?= (int) $m['id'] ?>"><?= $replied ? 'Your reply' : 'Write a reply' ?></label>
      <textarea class="form-control" id="reply<?= (int) $m['id'] ?>" name="reply" rows="3" required><?= e($m['admin_reply'] ?? '') ?></textarea>
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
        <button class="btn btn-danger" type="submit"><?= $replied ? 'Update and resend reply' : 'Send reply' ?></button>
        <?php if ($replied && $m['replied_at']): ?><small class="text-secondary">Last replied <?= e(date('d M Y, h:i A', strtotime($m['replied_at']))) ?></small><?php endif; ?>
      </div>
    </form>
    <form class="mt-3" method="post" onsubmit="return confirm('Delete this message and its reply?');">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="delete">
      <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
      <input type="hidden" name="show" value="<?= e($show) ?>">
      <button class="btn btn-sm btn-outline-danger" type="submit">Delete message</button>
    </form>
  </article>
<?php endforeach; ?>
<?php if (!$messages): ?>
  <div class="admin-card p-4"><?= $show === 'unreplied' ? 'Every message has a reply.' : 'No messages yet.' ?></div>
<?php endif; ?>
<?= pager_html($page, $pages) ?>
<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
