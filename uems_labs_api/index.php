<?php

require_once __DIR__ . '/helpers/cors.php';
require_once __DIR__ . '/helpers/response.php';
require_once __DIR__ . '/config/database.php';

// Health-check simples: confirma que o Apache está servindo o PHP
// e que a conexão com o Postgres do Supabase está funcionando.

try {
    $pdo = get_db_connection();
    $pdo->query('SELECT 1');
    json_response(['status' => 'ok', 'message' => 'API UEMS Labs no ar, conexão com o banco OK.']);
} catch (Throwable $e) {
    json_response(['status' => 'erro', 'message' => $e->getMessage()], 500);
}
