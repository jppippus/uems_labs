<?php

require_once __DIR__ . '/../../helpers/cors.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../config/database.php';

// POST /uems-api/api/denuncias/criar.php
// RF007: formulario com um unico campo de texto livre.

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Método não permitido.', 405);
}

$usuario = require_auth();
$body = json_body();

$descricao = trim($body['descricao'] ?? '');
if ($descricao === '') {
    json_error('O campo "descricao" é obrigatório.', 422);
}

$pdo = get_db_connection();
$stmt = $pdo->prepare(
    'INSERT INTO denuncias (usuario_id, descricao) VALUES (:usuario_id, :descricao)
     RETURNING id, created_at'
);
$stmt->execute([
    'usuario_id' => $usuario['id'],
    'descricao'  => $descricao,
]);

json_response($stmt->fetch(), 201);
