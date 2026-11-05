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


try {
    $pdo = get_db_connection();
    
   
    $stmt = $pdo->prepare('UPDATE problemas_relatados SET status = \'resolvido\' WHERE id = ?');
    $stmt->execute([$problemaId]);

    json_response(['ok' => true, 'mensagem' => 'Problema resolvido com sucesso!']);
} catch (Exception $e) {
    json_error('Erro ao resolver o problema: ' . $e->getMessage(), 500);
}