<?php
require_once 'config.php';
$pageTitle = 'Articles - ' . APP_NAME;
$activePage = 'articles';
$articles = $pdo ? db()->query('SELECT * FROM articles ORDER BY created_at DESC')->fetchAll() : [];
include 'includes/header.php';
?>
<section class="py-5 band">
    <div class="container py-4">
        <h1 class="section-title">Articles</h1>
        <div class="accent-line mb-4"></div>
        <p class="lead">News, education, and rights awareness updates from the federation.</p>
    </div>
</section>
<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <?php foreach ($articles as $article): ?>
                <div class="col-md-6 col-lg-4">
                    <article class="article-card h-100 overflow-hidden bg-white">
                        <?php if ($article['image_path']): ?><img src="<?= e($article['image_path']) ?>" alt="<?= e($article['title']) ?>"><?php endif; ?>
                        <div class="p-4">
                            <small class="text-secondary"><?= e(date('d M Y', strtotime($article['created_at']))) ?></small>
                            <h4 class="mt-2"><?= e($article['title']) ?></h4>
                            <p class="text-secondary"><?= nl2br(e($article['description'])) ?></p>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
            <?php if (!$articles): ?>
                <div class="col-12"><div class="admin-card p-4">No articles have been published yet.</div></div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php include 'includes/footer.php'; ?>
