<?php
// config/db.php

/**
 * .env 파일을 파싱하여 환경 변수를 로드하고,
 * 데이터베이스 연결(PDO) 인스턴스를 반환하는 함수.
 *
 * @return PDO|null 데이터베이스 연결 객체 또는 실패 시 null
 */
function get_db_connection(): ?PDO {
    // 이전에 연결된 인스턴스가 있으면 재사용 (Singleton Pattern)
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    // --- 1. 환경 변수 로드 ---
    // 컨테이너 내부의 .env 파일 경로
    $env_path = '/var/www/.env'; 

    if (is_readable($env_path)) {
        $lines = file($env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // 주석(#)으로 시작하는 라인 건너뛰기
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            // PHP의 putenv, getenv, $_ENV, $_SERVER에 변수 설정
            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }

    // --- 2. 환경 변수를 사용하여 DB 접속 정보 설정 ---
    $db_host = getenv('LOC_DB_HOST');
    $db_port = getenv('LOC_DB_PORT');
    $db_name = getenv('LOC_DB_NAME');
    $db_user = getenv('LOC_DB_USER');
    $db_pass = getenv('LOC_DB_PASS');

    // 필수 환경 변수 누락 시 처리
    if (!$db_host || !$db_name || !$db_user || !$db_pass) {
        error_log("Database connection failed: Required environment variables are not set.");
        // 실제 운영 환경에서는 사용자에게 상세 오류를 노출하지 않음
        // die("Database configuration error."); 
        return null;
    }
    
    $dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4";
    
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // 오류 발생 시 Exception을 발생시킴
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // 기본 fetch 모드를 연관 배열로 설정
        PDO::ATTR_EMULATE_PREPARES   => false,                  // SQL Injection 방지를 위해 PreparedStatement 에뮬레이션 비활성화
    ];

    // --- 3. PDO를 사용한 데이터베이스 연결 ---
    try {
        $pdo = new PDO($dsn, $db_user, $db_pass, $options);
        return $pdo;
    } catch (PDOException $e) {
        // 실제 운영 환경에서는 아래와 같이 상세 오류를 화면에 출력하면 안 됨.
        // 대신 오류를 로그 파일에 기록하고, 사용자에게는 일반적인 오류 메시지를 보여줘야 함.
        error_log("Database connection failed: " . $e->getMessage());
        
        // 사용자에게 노출될 메시지 (예시)
        // http_response_code(500);
        // die("데이터베이스에 연결할 수 없습니다. 관리자에게 문의하세요.");
        return null;
    }
}

// 애플리케이션 전역에서 이 함수를 호출하여 DB 연결 객체를 얻음
// 예시: $pdo = get_db_connection();
