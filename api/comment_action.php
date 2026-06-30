<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to('/pages/item_list.php');
}

$itemId = int_param('item_id');
$data = [
    'author_name' => input_string('author_name', 100),
    'content' => input_string('content', 1000),
];

try {
    $item = $itemId > 0 ? fetch_item($itemId) : null;
    if (!$item) {
        redirect_with_errors('/pages/item_list.php', ['존재하지 않는 게시글입니다.']);
    }

    $errors = validate_comment_payload($data);
    if ($errors) {
        redirect_with_errors('/pages/item_view.php?id=' . $itemId, $errors, $data);
    }

    $stmt = get_pdo()->prepare(
        'INSERT INTO item_comments (item_id, author_name, content)
         VALUES (:item_id, :author_name, :content)'
    );
    $stmt->execute([
        'item_id' => $itemId,
        'author_name' => $data['author_name'],
        'content' => $data['content'],
    ]);

    redirect_with_success('/pages/item_view.php?id=' . $itemId, '댓글이 등록되었습니다.');
} catch (Throwable $e) {
    error_log($e->getMessage());
    redirect_with_errors('/pages/item_view.php?id=' . $itemId, ['서버 오류로 댓글을 저장하지 못했습니다. 잠시 후 다시 시도해 주세요.'], $data);
}
