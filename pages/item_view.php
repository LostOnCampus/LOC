<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';

$pageTitle = '게시글 상세';
$id = int_param('id');
$item = null;
$comments = [];
$commentOld = pull_old_input();
$errorMessage = null;

try {
    if ($id <= 0) {
        throw new InvalidArgumentException('Invalid item id.');
    }
    $item = fetch_item($id);
    if (!$item) {
        throw new RuntimeException('Item not found.');
    }
    $comments = fetch_comments($id);
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

        <?php if (!empty($item['image_path'])): ?>
            <figure class="detail-image">
                <img src="<?= h($item['image_path']) ?>" alt="<?= h($item['title']) ?> 이미지">
            </figure>
        <?php endif; ?>

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

    <section class="comments-section">
        <div class="section-header">
            <h2>댓글</h2>
            <span><?= count($comments) ?>개</span>
        </div>

        <?php if (count($comments) === 0): ?>
            <div class="empty-state">아직 댓글이 없습니다.</div>
        <?php else: ?>
            <div class="comment-list">
                <?php foreach ($comments as $comment): ?>
                    <article class="comment">
                        <div class="comment-meta">
                            <strong><?= h($comment['author_name']) ?></strong>
                            <time><?= h(format_date($comment['created_at'])) ?></time>
                        </div>
                        <p><?= nl2br(h($comment['content'])) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form class="form comment-form" action="/api/comment_action.php" method="post">
            <input type="hidden" name="item_id" value="<?= (int)$item['id'] ?>">

            <label for="author_name">이름</label>
            <input id="author_name" name="author_name" type="text" maxlength="100" value="<?= h(old_value($commentOld, 'author_name')) ?>" required>

            <label for="comment_content">댓글 내용</label>
            <textarea id="comment_content" name="content" rows="4" maxlength="1000" required><?= h(old_value($commentOld, 'content')) ?></textarea>

            <div class="actions">
                <button class="button button-primary" type="submit">댓글 등록</button>
            </div>
        </form>
    </section>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
