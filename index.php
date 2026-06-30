<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$pageTitle = '메인';
$recentItems = [];
$errorMessage = null;

try {
    $stmt = get_pdo()->query(
        'SELECT id, item_type, title, location, created_at
         FROM lost_items
         ORDER BY created_at DESC
         LIMIT 5'
    );
    $recentItems = $stmt->fetchAll();
} catch (Throwable $e) {
    $errorMessage = page_error_message($e);
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <div>
        <p class="eyebrow">Campus Lost and Found</p>
        <h1>캠퍼스에서 잃어버린 물건을 빠르게 찾습니다.</h1>
        <p class="hero-copy">분실물과 습득물을 등록하고, 장소와 키워드로 필요한 게시글을 검색할 수 있습니다.</p>
        <div class="hero-actions">
            <a class="button button-primary" href="/pages/item_write.php">게시글 등록</a>
            <a class="button button-secondary" href="/pages/item_list.php">목록 보기</a>
        </div>
    </div>
    <form class="search-panel" action="/pages/search.php" method="get">
        <label for="keyword">검색어</label>
        <div class="search-row">
            <input id="keyword" name="keyword" type="search" placeholder="예: 학생증, 비전타워, 지갑" required>
            <button class="button button-dark" type="submit">검색</button>
        </div>
    </form>
</section>

<section class="section">
    <div class="section-header">
        <h2>최근 게시글</h2>
        <a href="/pages/item_list.php">전체 보기</a>
    </div>

    <?php render_alert($errorMessage, 'error'); ?>

    <?php if (!$errorMessage && count($recentItems) === 0): ?>
        <div class="empty-state">아직 등록된 게시글이 없습니다.</div>
    <?php endif; ?>

    <div class="item-grid">
        <?php foreach ($recentItems as $item): ?>
            <article class="item-card">
                <span class="<?= h(item_type_class($item['item_type'])) ?>">
                    <?= h(item_type_label($item['item_type'])) ?>
                </span>
                <h3><a href="/pages/item_view.php?id=<?= (int)$item['id'] ?>"><?= h($item['title']) ?></a></h3>
                <p><?= h($item['location']) ?></p>
                <time><?= h(format_date($item['created_at'])) ?></time>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
