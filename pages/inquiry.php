<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';

$pageTitle = '문의/제보';
$old = pull_old_input();
require_once __DIR__ . '/../includes/header.php';
?>

<section class="page-heading">
    <div>
        <p class="eyebrow">Contact</p>
        <h1>문의/제보</h1>
    </div>
</section>

<?php render_flash(); ?>

<form class="form" action="/api/inquiry_action.php" method="post">
    <label for="name">이름</label>
    <input id="name" name="name" type="text" maxlength="100" value="<?= h(old_value($old, 'name')) ?>" required>

    <label for="contact">연락처</label>
    <input id="contact" name="contact" type="text" maxlength="100" value="<?= h(old_value($old, 'contact')) ?>" required>

    <label for="message">메시지</label>
    <textarea id="message" name="message" rows="8" required><?= h(old_value($old, 'message')) ?></textarea>

    <div class="actions">
        <a class="button button-secondary" href="/">취소</a>
        <button class="button button-primary" type="submit">저장</button>
    </div>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
