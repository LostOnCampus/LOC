<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to('/pages/item_list.php');
}

$action = query_string('action', 20);
$data = [];
$id = 0;

try {
    if ($action === 'create') {
        $data = [
            'item_type' => input_string('item_type', 10),
            'title' => input_string('title', 200),
            'location' => input_string('location', 100),
            'content' => input_string('content', 5000),
            'contact' => input_string('contact', 100),
            'password' => input_string('password', 255),
        ];

        $errors = validate_item_payload($data);
        $errors = array_merge($errors, validate_password($data['password']));
        if ($errors) {
            redirect_with_errors('/pages/item_write.php', $errors, $data);
        }

        $stmt = get_pdo()->prepare(
            'INSERT INTO lost_items (item_type, title, location, content, contact, edit_password)
             VALUES (:item_type, :title, :location, :content, :contact, :edit_password)'
        );
        $stmt->execute([
            'item_type' => $data['item_type'],
            'title' => $data['title'],
            'location' => $data['location'],
            'content' => $data['content'],
            'contact' => $data['contact'],
            'edit_password' => password_hash($data['password'], PASSWORD_DEFAULT),
        ]);

        redirect_with_success('/pages/item_list.php', '게시글이 등록되었습니다.');
    }

    if ($action === 'update') {
        $id = int_param('id');
        $item = $id > 0 ? fetch_item($id) : null;
        if (!$item) {
            redirect_with_errors('/pages/item_list.php', ['존재하지 않는 게시글입니다.']);
        }

        $data = [
            'item_type' => input_string('item_type', 10),
            'title' => input_string('title', 200),
            'location' => input_string('location', 100),
            'content' => input_string('content', 5000),
            'contact' => input_string('contact', 100),
            'password' => input_string('password', 255),
        ];

        $errors = validate_item_payload($data);
        $errors = array_merge($errors, validate_password($data['password']));
        if ($data['password'] !== '' && !password_verify($data['password'], $item['edit_password'])) {
            $errors[] = '비밀번호가 일치하지 않습니다.';
        }
        if ($errors) {
            redirect_with_errors('/pages/item_edit.php?id=' . $id, $errors, $data);
        }

        $stmt = get_pdo()->prepare(
            'UPDATE lost_items
             SET item_type = :item_type,
                 title = :title,
                 location = :location,
                 content = :content,
                 contact = :contact
             WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'item_type' => $data['item_type'],
            'title' => $data['title'],
            'location' => $data['location'],
            'content' => $data['content'],
            'contact' => $data['contact'],
        ]);

        redirect_with_success('/pages/item_view.php?id=' . $id, '게시글이 수정되었습니다.');
    }

    if ($action === 'delete') {
        $id = int_param('id');
        $password = input_string('password', 255);
        $item = $id > 0 ? fetch_item($id) : null;

        if (!$item) {
            redirect_with_errors('/pages/item_list.php', ['존재하지 않는 게시글입니다.']);
        }

        $errors = validate_password($password);
        if (!$errors && !password_verify($password, $item['edit_password'])) {
            $errors[] = '비밀번호가 일치하지 않습니다.';
        }
        if ($errors) {
            redirect_with_errors('/pages/item_view.php?id=' . $id, $errors);
        }

        $stmt = get_pdo()->prepare('DELETE FROM lost_items WHERE id = :id');
        $stmt->execute(['id' => $id]);

        redirect_with_success('/pages/item_list.php', '게시글이 삭제되었습니다.');
    }

    redirect_with_errors('/pages/item_list.php', ['잘못된 요청입니다.']);
} catch (Throwable $e) {
    error_log($e->getMessage());
    if ($action === 'create') {
        redirect_with_errors('/pages/item_write.php', ['서버 오류로 게시글을 저장하지 못했습니다. 잠시 후 다시 시도해 주세요.'], $data);
    }
    if ($action === 'update' && $id > 0) {
        redirect_with_errors('/pages/item_edit.php?id=' . $id, ['서버 오류로 게시글을 수정하지 못했습니다. 잠시 후 다시 시도해 주세요.'], $data);
    }
    if ($action === 'delete' && $id > 0) {
        redirect_with_errors('/pages/item_view.php?id=' . $id, ['서버 오류로 게시글을 삭제하지 못했습니다. 잠시 후 다시 시도해 주세요.']);
    }
    redirect_with_errors('/pages/item_list.php', ['서버 오류로 요청을 처리하지 못했습니다. 잠시 후 다시 시도해 주세요.']);
}
