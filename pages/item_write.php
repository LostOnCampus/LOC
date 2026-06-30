<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';

$pageTitle = '게시글 등록';
$old = pull_old_input();
$oldType = old_value($old, 'item_type', 'lost');
require_once __DIR__ . '/../includes/header.php';
?>

<section class="page-heading">
    <div>
        <p class="eyebrow">New Post</p>
        <h1>분실물/습득물 등록</h1>
    </div>
</section>

<?php render_flash(); ?>

<form class="form" action="/api/item_action.php?action=create" method="post">
    <fieldset class="segmented">
        <legend>유형</legend>
        <label><input type="radio" name="item_type" value="lost" <?= $oldType !== 'found' ? 'checked' : '' ?>> 분실물</label>
        <label><input type="radio" name="item_type" value="found" <?= $oldType === 'found' ? 'checked' : '' ?>> 습득물</label>
    </fieldset>

    <label for="title">제목</label>
    <input id="title" name="title" type="text" maxlength="200" value="<?= h(old_value($old, 'title')) ?>" required>

    <label for="location">장소</label>
    <input id="location" name="location" type="text" maxlength="100" value="<?= h(old_value($old, 'location')) ?>" required>

    <label for="content">상세 내용</label>
    <textarea id="content" name="content" rows="8" required><?= h(old_value($old, 'content')) ?></textarea>

    <label for="contact">연락처</label>
    <input id="contact" name="contact" type="text" maxlength="100" value="<?= h(old_value($old, 'contact')) ?>" required>

    <label for="password">수정·삭제 비밀번호</label>
    <input id="password" name="password" type="password" required>

    <div class="actions">
        <a class="button button-secondary" href="/pages/item_list.php">취소</a>
        <button class="button button-primary" type="submit">등록</button>
    </div>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
