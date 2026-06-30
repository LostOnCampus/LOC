<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to('/pages/inquiry.php');
}

$name = input_string('name', 100);
$contact = input_string('contact', 100);
$message = input_string('message', 5000);
$data = [
    'name' => $name,
    'contact' => $contact,
    'message' => $message,
];

$errors = validate_inquiry_payload($data);
if ($errors) {
    redirect_with_errors('/pages/inquiry.php', $errors, $data);
}

try {
    $stmt = get_pdo()->prepare(
        'INSERT INTO inquiries (name, contact, message)
         VALUES (:name, :contact, :message)'
    );
    $stmt->execute([
        'name' => $data['name'],
        'contact' => $data['contact'],
        'message' => $data['message'],
    ]);

    redirect_with_success('/pages/inquiry.php', '문의/제보가 저장되었습니다.');
} catch (Throwable $e) {
    error_log($e->getMessage());
    redirect_with_errors('/pages/inquiry.php', ['서버 오류로 문의/제보를 저장하지 못했습니다. 잠시 후 다시 시도해 주세요.'], $data);
}
