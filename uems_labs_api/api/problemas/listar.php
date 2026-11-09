<?php

require_once __DIR__ . '/../../helpers/cors.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../config/database.php';

// GET /uems-api/api/problemas/listar.php
// RF010: lista de problemas relatados, pro tecnico gerenciar.

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_error('Método não permitido.', 405);
}

$usuario = require_auth();
require_perfil($usuario, ['tecnico']);

$pdo = get_db_connection();
$stmt = $pdo->query(
    "SELECT
        p.id,
        p.recurso_id,
        rec.identificacao AS recurso_identificacao,
        l.nome AS laboratorio_nome,
        p.tipo_problema,
        p.descricao,
        p.status,
        p.created_at
     FROM problemas_relatados p
     JOIN recursos rec ON rec.id = p.recurso_id
     JOIN laboratorios l ON l.id = rec.laboratorio_id
     ORDER BY p.status = 'aberto' DESC, p.created_at DESC"
);

json_response($stmt->fetchAll());
