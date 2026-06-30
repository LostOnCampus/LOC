<?php
declare(strict_types=1);

define('DB_HOST', getenv('LOC_DB_HOST') ?: 'db-47u9u6.vpc-cdb.ntruss.com');
define('DB_PORT', getenv('LOC_DB_PORT') ?: '3306');
define('DB_NAME', getenv('LOC_DB_NAME') ?: 'lost_on_campus');
define('DB_USER', getenv('LOC_DB_USER') ?: 'user');
define('DB_PASS', getenv('LOC_DB_PASS') ?: 'CHANGE_ME');

function get_pdo(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    if (DB_PASS === 'CHANGE_ME') {
        throw new RuntimeException('DB password is not configured.');
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        DB_HOST,
        DB_PORT,
        DB_NAME
    );

    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}
