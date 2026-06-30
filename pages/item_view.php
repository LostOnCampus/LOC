<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';

$pageTitle = '게시글 상세';
$id = int_param('id');
$item = null;
$errorMessage = null;

try {
    if ($id <= 0) {
        throw new InvalidArgumentException('Invalid item id.');
    }
    $item = fetch_item($id);
    if (!$item) {
        throw new RuntimeException('Item not found.');
    }
} catch (InvalidArgumentException | RuntimeException $e) {
    error_log($e->getMessage());
    $errorMessage = '존재하지 않는 게시글입니다. 목록에서 다시 선택해 주세요.';
} catch (Throwable $e) {
    $errorMessage = page_error_message($e);
}

require_once __DIR__ . '/../includes/header.php';
?>

<?php if ($errorMessage): ?>
    <section class="page-heading">
        <h1>게시글을 찾을 수 없습니다.</h1>
    </section>
    <?php render_alert($errorMessage, 'error'); ?>
    <a class="button button-secondary" href="/pages/item_list.php">목록으로</a>
<?php else: ?>
    <article class="detail">
        <div class="detail-header">
            <span class="<?= h(item_type_class($item['item_type'])) ?>"><?= h(item_type_label($item['item_type'])) ?></span>
            <h1><?= h($item['title']) ?></h1>
            <p><?= h($item['location']) ?></p>
        </div>

        <?php render_flash(); ?>

        <dl class="meta-list">
            <div>
                <dt>연락처</dt>
                <dd><?= h($item['contact']) ?></dd>
            </div>
            <div>
                <dt>등록일</dt>
                <dd><?= h(format_date($item['created_at'])) ?></dd>
            </div>
            <div>
                <dt>수정일</dt>
                <dd><?= h(format_date($item['updated_at'])) ?></dd>
            </div>
        </dl>

        <div class="content-box"><?= nl2br(h($item['content'])) ?></div>

        <div class="actions">
            <a class="button button-secondary" href="/pages/item_list.php">목록</a>
            <a class="button button-primary" href="/pages/item_edit.php?id=<?= (int)$item['id'] ?>">수정</a>
        </div>

        <form class="delete-form" action="/api/item_action.php?action=delete" method="post">
            <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
            <label for="delete_password">삭제 비밀번호</label>
            <div class="search-row compact">
                <input id="delete_password" name="password" type="password" required>
                <button class="button button-danger" type="submit" data-confirm="정말 삭제하시겠습니까?">삭제</button>
            </div>
        </form>
    </article>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
