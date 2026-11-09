<?php

require_once __DIR__ . '/../../helpers/cors.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../config/database.php';

// GET /uems-api/api/tecnico/dashboard.php
// RF009: reservas ativas programadas para hoje, pro painel do tecnico.

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_error('Método não permitido.', 405);
}

$usuario = require_auth();
require_perfil($usuario, ['tecnico']);

$pdo = get_db_connection();
$stmt = $pdo->query(
    "SELECT
        r.id,
        l.nome AS laboratorio_nome,
        u.nome AS usuario_nome,
        r.data_reserva AS data,
        r.hora_inicio,
        r.hora_fim,
        r.status
     FROM reservas r
     JOIN recursos rec ON rec.id = r.recurso_id
     JOIN laboratorios l ON l.id = rec.laboratorio_id
     JOIN usuarios u ON u.id = r.usuario_id
     WHERE r.data_reserva = CURRENT_DATE
       AND r.status = 'ativa'
     ORDER BY r.hora_inicio"
);

json_response($stmt->fetchAll());
