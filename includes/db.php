<?php
require_once __DIR__ . '/../config/config.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    $conn->set_charset('utf8mb4');
    $conn->query("SET time_zone = '+00:00'");
} catch (Throwable $error) {
    error_log($error->getMessage());
    http_response_code(503);
    exit('Database unavailable. Check config/local.php and import the supplied SQL.');
}

function db_query(string $sql, array $params = []): mysqli_stmt {
    $statement = $GLOBALS['conn']->prepare($sql);
    if ($params) {
        $types = '';
        foreach ($params as $value) $types .= is_int($value) ? 'i' : (is_float($value) ? 'd' : 's');
        $statement->bind_param($types, ...$params);
    }
    $statement->execute();
    return $statement;
}
function db_all(string $sql, array $params = []): array {
    return db_query($sql, $params)->get_result()->fetch_all(MYSQLI_ASSOC);
}
function db_one(string $sql, array $params = []): ?array {
    return db_query($sql, $params)->get_result()->fetch_assoc();
}
