<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';

$pageTitle = '검색 결과';
$keyword = query_string('keyword', 100);
$items = [];
$errorMessage = null;

try {
    if ($keyword !== '') {
        $stmt = get_pdo()->prepare(
            'SELECT id, item_type, title, location, created_at
             FROM lost_items
             WHERE title LIKE :keyword
                OR content LIKE :keyword
                OR location LIKE :keyword
             ORDER BY created_at DESC'
        );
        $stmt->execute(['keyword' => '%' . $keyword . '%']);
        $items = $stmt->fetchAll();
    }
} catch (Throwable $e) {
    $errorMessage = page_error_message($e);
}

require_once __DIR__ . '/../includes/header.php';
?>

<section class="page-heading">
    <div>
        <p class="eyebrow">Search</p>
        <h1>검색 결과</h1>
    </div>
</section>

<form class="toolbar" action="/pages/search.php" method="get">
    <div class="search-row">
        <input name="keyword" type="search" value="<?= h($keyword) ?>" placeholder="제목, 장소, 내용 검색" required>
        <button class="button button-dark" type="submit">검색</button>
    </div>
</form>

<?php render_alert($errorMessage, 'error'); ?>

<?php if ($keyword === ''): ?>
    <div class="empty-state">검색어를 입력해 주세요.</div>
<?php elseif (!$errorMessage && count($items) === 0): ?>
    <div class="empty-state">검색 결과가 없습니다.</div>
<?php else: ?>
    <p class="result-count">"<?= h($keyword) ?>" 검색 결과 <?= count($items) ?>건</p>
    <div class="list">
        <?php foreach ($items as $item): ?>
            <a class="list-row" href="/pages/item_view.php?id=<?= (int)$item['id'] ?>">
                <span class="<?= h(item_type_class($item['item_type'])) ?>"><?= h(item_type_label($item['item_type'])) ?></span>
                <strong><?= h($item['title']) ?></strong>
                <span><?= h($item['location']) ?></span>
                <time><?= h(format_date($item['created_at'])) ?></time>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
