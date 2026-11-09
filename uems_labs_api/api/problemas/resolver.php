<?php
require_once __DIR__ . '/../helpers/cors.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../config/database.php';


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Método não permitido.', 405);
}


$usuario = require_auth();


require_perfil($usuario, ['tecnico']);


$body = json_body();
if (empty($body['problema_id'])) {
    json_error('O campo "problema_id" é obrigatório.', 422);
}

$problemaId = $body['problema_id'];

$pdo = get_db_connection();

// O trigger trg_liberar_recurso_apos_resolucao ja libera o recurso sozinho.
$stmt = $pdo->prepare("UPDATE problemas_relatados SET status = 'resolvido' WHERE id = :id RETURNING id, status");
$stmt->execute(['id' => $problemaId]);
$problema = $stmt->fetch();

if (!$problema) {
    json_error('Problema não encontrado.', 404);
}

json_response($problema);