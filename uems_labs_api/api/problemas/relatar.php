<?php

require_once __DIR__ . '/../../helpers/cors.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../config/database.php';

// POST /uems-api/api/problemas/relatar.php
// RF005: reporta um problema a partir de uma reserva do próprio usuário.
// O recurso_id é derivado da reserva aqui no backend (não confia no que o
// cliente manda) — o trigger trg_indisponibilizar_recurso já marca o
// recurso como indisponível sozinho.

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Método não permitido.', 405);
}

$usuario = require_auth();
$body = json_body();

if (empty($body['reserva_id'])) {
    json_error('O campo "reserva_id" é obrigatório.', 422);
}
if (empty($body['tipo_problema'])) {
    json_error('O campo "tipo_problema" é obrigatório.', 422);
}

$pdo = get_db_connection();

$stmtReserva = $pdo->prepare('SELECT usuario_id, recurso_id FROM reservas WHERE id = :id');
$stmtReserva->execute(['id' => $body['reserva_id']]);
$reserva = $stmtReserva->fetch();

if (!$reserva) {
    json_error('Reserva não encontrada.', 404);
}
if ($reserva['usuario_id'] !== $usuario['id']) {
    json_error('Você só pode relatar problemas das suas próprias reservas.', 403);
}

$stmt = $pdo->prepare(
    "INSERT INTO problemas_relatados (reserva_id, recurso_id, tipo_problema, descricao, status)
     VALUES (:reserva_id, :recurso_id, :tipo_problema, :descricao, 'aberto')
     RETURNING id, reserva_id, recurso_id, tipo_problema, descricao, status, created_at"
);
$stmt->execute([
    'reserva_id'    => $body['reserva_id'],
    'recurso_id'    => $reserva['recurso_id'],
    'tipo_problema' => $body['tipo_problema'],
    'descricao'     => $body['descricao'] ?? null,
]);

json_response($stmt->fetch(), 201);
