<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';

$pageTitle = '분실물/습득물 목록';
$type = query_string('type', 10);
$items = [];
$errorMessage = null;

try {
    if (is_valid_item_type($type)) {
        $stmt = get_pdo()->prepare(
            'SELECT id, item_type, title, location, created_at
             FROM lost_items
             WHERE item_type = :type
             ORDER BY created_at DESC'
        );
        $stmt->execute(['type' => $type]);
    } else {
        $stmt = get_pdo()->query(
            'SELECT id, item_type, title, location, created_at
             FROM lost_items
             ORDER BY created_at DESC'
        );
    }
    $items = $stmt->fetchAll();
} catch (Throwable $e) {
    $errorMessage = page_error_message($e);
}

require_once __DIR__ . '/../includes/header.php';
?>

<section class="page-heading">
    <div>
        <p class="eyebrow">Board</p>
        <h1>분실물/습득물 목록</h1>
    </div>
    <a class="button button-primary" href="/pages/item_write.php">등록</a>
</section>

<form class="toolbar" action="/pages/search.php" method="get">
    <div class="filter-links">
        <a class="<?= $type === '' ? 'active' : '' ?>" href="/pages/item_list.php">전체</a>
        <a class="<?= $type === 'lost' ? 'active' : '' ?>" href="/pages/item_list.php?type=lost">분실물</a>
        <a class="<?= $type === 'found' ? 'active' : '' ?>" href="/pages/item_list.php?type=found">습득물</a>
    </div>
    <div class="search-row compact">
        <input name="keyword" type="search" placeholder="제목, 장소, 내용 검색" required>
        <button class="button button-dark" type="submit">검색</button>
    </div>
</form>

<?php
render_flash();
render_alert($_GET['created'] ?? null ? '게시글이 등록되었습니다.' : null, 'success');
render_alert($_GET['updated'] ?? null ? '게시글이 수정되었습니다.' : null, 'success');
render_alert($_GET['deleted'] ?? null ? '게시글이 삭제되었습니다.' : null, 'success');
render_alert($errorMessage, 'error');
?>

<?php if (!$errorMessage && count($items) === 0): ?>
    <div class="empty-state">등록된 게시글이 없습니다.</div>
<?php endif; ?>

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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
