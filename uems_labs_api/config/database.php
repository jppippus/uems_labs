<?php

require_once __DIR__ . '/env.php';

function get_db_connection(): PDO
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $connectionString = env('CONNECTION_STRING');
    if (!$connectionString) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'CONNECTION_STRING não configurada no .env.']);
        exit;
    }

    $parts = parse_url($connectionString);
    $host = $parts['host'] ?? 'localhost';
    $port = $parts['port'] ?? 5432;
    $dbname = isset($parts['path']) ? ltrim($parts['path'], '/') : 'postgres';
    $user = $parts['user'] ?? '';
    $pass = $parts['pass'] ?? '';

    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";

    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'Falha ao conectar ao banco de dados.']);
        exit;
    }

    return $pdo;
}
