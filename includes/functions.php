<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

function ensure_session(): bool
{
    static $sessionFailed = false;

    if ($sessionFailed) {
        return false;
    }

    if (session_status() === PHP_SESSION_NONE) {
        if (headers_sent()) {
            $sessionFailed = true;
            return false;
        }

        $savePath = session_save_path();
        if ($savePath === '' || !is_dir($savePath) || !is_writable($savePath)) {
            session_save_path(sys_get_temp_dir());
        }

        if (!@session_start()) {
            $sessionFailed = true;
            return false;
        }
    }

    return true;
}

function h(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect_to(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function item_type_label(string $type): string
{
    return $type === 'found' ? '습득물' : '분실물';
}

function item_type_class(string $type): string
{
    return $type === 'found' ? 'badge badge-found' : 'badge badge-lost';
}

function is_valid_item_type(string $type): bool
{
    return in_array($type, ['lost', 'found'], true);
}

function input_string(string $key, int $maxLength = 1000): string
{
    return trim((string)($_POST[$key] ?? ''));
}

function query_string(string $key, int $maxLength = 200): string
{
    $value = trim((string)($_GET[$key] ?? ''));
    if (mb_strlen($value) > $maxLength) {
        $value = mb_substr($value, 0, $maxLength);
    }

    return $value;
}

function int_param(string $key): int
{
    $value = $_GET[$key] ?? $_POST[$key] ?? null;

    if (!is_numeric($value)) {
        return 0;
    }

    return max(0, (int)$value);
}

function fetch_item(int $id): ?array
{
    $stmt = get_pdo()->prepare('SELECT * FROM lost_items WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $item = $stmt->fetch();

    return $item ?: null;
}

function fetch_comments(int $itemId): array
{
    $stmt = get_pdo()->prepare(
        'SELECT id, author_name, content, created_at
         FROM item_comments
         WHERE item_id = :item_id
         ORDER BY created_at ASC, id ASC'
    );
    $stmt->execute(['item_id' => $itemId]);

    return $stmt->fetchAll();
}

function has_length(string $value, int $maxLength): bool
{
    return mb_strlen($value) <= $maxLength;
}

function validate_comment_payload(array $data): array
{
    $errors = [];

    if ($data['author_name'] === '') {
        $errors[] = '댓글 작성자 이름을 입력해 주세요.';
    } elseif (!has_length($data['author_name'], 100)) {
        $errors[] = '댓글 작성자 이름은 100자 이하로 입력해 주세요.';
    }

    if ($data['content'] === '') {
        $errors[] = '댓글 내용을 입력해 주세요.';
    } elseif (!has_length($data['content'], 1000)) {
        $errors[] = '댓글 내용은 1000자 이하로 입력해 주세요.';
    }

    return $errors;
}

function validate_item_payload(array $data): array
{
    $errors = [];

    if (!is_valid_item_type($data['item_type'])) {
        $errors[] = '유형을 선택해 주세요.';
    }
    if ($data['title'] === '') {
        $errors[] = '제목을 입력해 주세요.';
    } elseif (!has_length($data['title'], 200)) {
        $errors[] = '제목은 200자 이하로 입력해 주세요.';
    }
    if ($data['location'] === '') {
        $errors[] = '장소를 입력해 주세요.';
    } elseif (!has_length($data['location'], 100)) {
        $errors[] = '장소는 100자 이하로 입력해 주세요.';
    }
    if ($data['content'] === '') {
        $errors[] = '상세 내용을 입력해 주세요.';
    } elseif (!has_length($data['content'], 5000)) {
        $errors[] = '상세 내용은 5000자 이하로 입력해 주세요.';
    }
    if ($data['contact'] === '') {
        $errors[] = '연락처를 입력해 주세요.';
    } elseif (!has_length($data['contact'], 100)) {
        $errors[] = '연락처는 100자 이하로 입력해 주세요.';
    }

    return $errors;
}

function upload_item_image(array $file): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('이미지 업로드 중 오류가 발생했습니다.');
    }

    $maxBytes = 3 * 1024 * 1024;
    if (($file['size'] ?? 0) > $maxBytes) {
        throw new RuntimeException('이미지는 3MB 이하로 업로드해 주세요.');
    }

    $tmpName = (string)($file['tmp_name'] ?? '');
    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
        throw new RuntimeException('업로드된 이미지 파일을 확인할 수 없습니다.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmpName) ?: '';
    $extensions = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    if (!array_key_exists($mime, $extensions)) {
        throw new RuntimeException('이미지는 JPG, PNG, WEBP, GIF 형식만 업로드할 수 있습니다.');
    }

    $uploadDir = __DIR__ . '/../uploads/items';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
        throw new RuntimeException('이미지 저장 폴더를 만들 수 없습니다.');
    }

    $fileName = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
    $targetPath = $uploadDir . '/' . $fileName;

    if (!move_uploaded_file($tmpName, $targetPath)) {
        throw new RuntimeException('이미지 파일을 저장하지 못했습니다.');
    }

    chmod($targetPath, 0644);

    return '/uploads/items/' . $fileName;
}

function delete_item_image(?string $imagePath): void
{
    if (!$imagePath || !str_starts_with($imagePath, '/uploads/items/')) {
        return;
    }

    $fullPath = __DIR__ . '/..' . $imagePath;
    if (is_file($fullPath)) {
        unlink($fullPath);
    }
}

function validate_password(?string $password): array
{
    $password = $password ?? '';
    $errors = [];

    if ($password === '') {
        $errors[] = '비밀번호를 입력해 주세요.';
    } elseif (mb_strlen($password) < 4) {
        $errors[] = '비밀번호는 4자 이상 입력해 주세요.';
    } elseif (mb_strlen($password) > 255) {
        $errors[] = '비밀번호는 255자 이하로 입력해 주세요.';
    }

    return $errors;
}

function validate_inquiry_payload(array $data): array
{
    $errors = [];

    if ($data['name'] === '') {
        $errors[] = '이름을 입력해 주세요.';
    } elseif (!has_length($data['name'], 100)) {
        $errors[] = '이름은 100자 이하로 입력해 주세요.';
    }
    if ($data['contact'] === '') {
        $errors[] = '연락처를 입력해 주세요.';
    } elseif (!has_length($data['contact'], 100)) {
        $errors[] = '연락처는 100자 이하로 입력해 주세요.';
    }
    if ($data['message'] === '') {
        $errors[] = '메시지를 입력해 주세요.';
    } elseif (!has_length($data['message'], 5000)) {
        $errors[] = '메시지는 5000자 이하로 입력해 주세요.';
    }

    return $errors;
}

function format_date(?string $date): string
{
    if (!$date) {
        return '-';
    }

    return date('Y-m-d H:i', strtotime($date));
}

function set_flash(string $type, array $messages): void
{
    if (!ensure_session()) {
        return;
    }
    $_SESSION['flash'] = [
        'type' => $type,
        'messages' => array_values($messages),
    ];
}

function pull_flash(): ?array
{
    if (!ensure_session()) {
        return null;
    }
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);

    return is_array($flash) ? $flash : null;
}

function set_old_input(array $data): void
{
    if (!ensure_session()) {
        return;
    }
    unset($data['password']);
    $_SESSION['old_input'] = $data;
}

function pull_old_input(): array
{
    if (!ensure_session()) {
        return [];
    }
    $old = $_SESSION['old_input'] ?? [];
    unset($_SESSION['old_input']);

    return is_array($old) ? $old : [];
}

function old_value(array $old, string $key, ?string $default = ''): string
{
    return (string)($old[$key] ?? $default ?? '');
}

function redirect_with_errors(string $path, array $errors, array $oldInput = []): never
{
    set_flash('error', $errors);
    if ($oldInput) {
        set_old_input($oldInput);
    }
    redirect_to($path);
}

function redirect_with_success(string $path, string $message): never
{
    set_flash('success', [$message]);
    redirect_to($path);
}

function render_alert(?string $message, string $type = 'info'): void
{
    if (!$message) {
        return;
    }

    echo '<div class="alert alert-' . h($type) . '">' . h($message) . '</div>';
}

function render_messages(array $messages, string $type = 'info'): void
{
    if (!$messages) {
        return;
    }

    echo '<div class="alert alert-' . h($type) . '">';
    if (count($messages) === 1) {
        echo h((string)$messages[0]);
    } else {
        echo '<ul>';
        foreach ($messages as $message) {
            echo '<li>' . h((string)$message) . '</li>';
        }
        echo '</ul>';
    }
    echo '</div>';
}

function render_flash(): void
{
    $flash = pull_flash();
    if (!$flash) {
        return;
    }

    render_messages($flash['messages'] ?? [], (string)($flash['type'] ?? 'info'));
}

function page_error_message(Throwable $e): string
{
    error_log($e->getMessage());
    return '요청을 처리하는 중 문제가 발생했습니다. 잠시 후 다시 시도해 주세요.';
}
