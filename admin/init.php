<?php
require_once __DIR__ . '/../config.php';

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_verify(): void {
    $sent = $_POST['csrf'] ?? '';
    if (!is_string($sent) || !hash_equals($_SESSION['csrf'] ?? '', $sent)) {
        http_response_code(400);
        die('This form has expired. Go back, refresh the page and try again.');
    }
}

function flash(string $message, string $type = 'success'): void {
    $_SESSION['flash'] = [$type, $message];
}

function pull_flash(): ?array {
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function redirect(string $to): void {
    header('Location: ' . $to);
    exit;
}

function delete_upload(?string $path): void {
    if (!$path || strpos($path, 'uploads/') !== 0 || strpos($path, '..') !== false) {
        return;
    }
    $full = __DIR__ . '/../' . $path;
    if (is_file($full)) {
        @unlink($full);
    }
}

/** Returns the saved path, or null. Sets $error when a file was chosen but rejected. */
function save_image_field(string $field, string $folder, ?string &$error = null): ?string {
    $file = $_FILES[$field] ?? null;
    if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $msg = 'The image must be a JPG, PNG or WebP file under 3 MB.';
    if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 3 * 1024 * 1024) {
        $error = $msg;
        return null;
    }
    $path = upload_image($file, $folder);
    if ($path === null) {
        $error = $msg;
    }
    return $path;
}

/** @return array{0:int,1:int,2:int} page, total pages, offset */
function pager(int $total, int $perPage): array {
    $pages = max(1, (int) ceil($total / $perPage));
    $page  = min($pages, max(1, (int) ($_GET['page'] ?? 1)));
    return [$page, $pages, ($page - 1) * $perPage];
}

function pager_html(int $page, int $pages): string {
    if ($pages < 2) {
        return '';
    }
    $q = $_GET;
    $out = '<nav aria-label="Pages"><ul class="pagination">';
    for ($i = 1; $i <= $pages; $i++) {
        $q['page'] = $i;
        $out .= '<li class="page-item' . ($i === $page ? ' active' : '') . '"><a class="page-link" href="?' . e(http_build_query($q)) . '">' . $i . '</a></li>';
    }
    return $out . '</ul></nav>';
}
