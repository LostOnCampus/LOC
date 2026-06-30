<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';

$pageTitle = '게시글 수정';
$id = int_param('id');
$item = null;
$errorMessage = null;
$old = pull_old_input();

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
    <section class="page-heading">
        <div>
            <p class="eyebrow">Edit Post</p>
            <h1>게시글 수정</h1>
        </div>
    </section>

    <?php render_flash(); ?>

    <form class="form" action="/api/item_action.php?action=update" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">

        <fieldset class="segmented">
            <legend>유형</legend>
            <?php $currentType = old_value($old, 'item_type', $item['item_type']); ?>
            <label>
                <input type="radio" name="item_type" value="lost" <?= $currentType !== 'found' ? 'checked' : '' ?>>
                분실물
            </label>
            <label>
                <input type="radio" name="item_type" value="found" <?= $currentType === 'found' ? 'checked' : '' ?>>
                습득물
            </label>
        </fieldset>

        <label for="title">제목</label>
        <input id="title" name="title" type="text" maxlength="200" value="<?= h(old_value($old, 'title', $item['title'])) ?>" required>

        <label for="location">장소</label>
        <input id="location" name="location" type="text" maxlength="100" value="<?= h(old_value($old, 'location', $item['location'])) ?>" required>

        <label for="content">상세 내용</label>
        <textarea id="content" name="content" rows="8" required><?= h(old_value($old, 'content', $item['content'])) ?></textarea>

        <label for="contact">연락처</label>
        <input id="contact" name="contact" type="text" maxlength="100" value="<?= h(old_value($old, 'contact', $item['contact'])) ?>" required>

        <?php if (!empty($item['image_path'])): ?>
            <div class="current-image">
                <span>현재 이미지</span>
                <img src="<?= h($item['image_path']) ?>" alt="현재 등록된 물품 이미지">
            </div>
        <?php endif; ?>

        <label for="image">새 이미지</label>
        <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp,image/gif">
        <p class="field-help">새 이미지를 선택하면 기존 이미지가 교체됩니다. 3MB 이하 파일만 가능합니다.</p>

        <label for="password">수정 비밀번호</label>
        <input id="password" name="password" type="password" required>

        <div class="actions">
            <a class="button button-secondary" href="/pages/item_view.php?id=<?= (int)$item['id'] ?>">취소</a>
            <button class="button button-primary" type="submit">수정</button>
        </div>
    </form>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
