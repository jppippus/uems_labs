<?php

require_once __DIR__ . '/../../helpers/cors.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../config/database.php';

// POST /uems-api/api/problemas/relatar.php
// RF005: reporta um problema num recurso (sala/mesa/computador). O trigger
// trg_indisponibilizar_recurso ja marca o recurso como indisponivel sozinho.

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Método não permitido.', 405);
}

$usuario = require_auth();
$body = json_body();

if (empty($body['recurso_id'])) {
    json_error('O campo "recurso_id" é obrigatório.', 422);
}
if (empty($body['tipo_problema'])) {
    json_error('O campo "tipo_problema" é obrigatório.', 422);
}

$pdo = get_db_connection();

$stmt = $pdo->prepare(
    "INSERT INTO problemas_relatados (reserva_id, recurso_id, tipo_problema, descricao, status)
     VALUES (:reserva_id, :recurso_id, :tipo_problema, :descricao, 'aberto')
     RETURNING id, reserva_id, recurso_id, tipo_problema, descricao, status, created_at"
);

try {
    $stmt->execute([
        'reserva_id'    => $body['reserva_id'] ?? null,
        'recurso_id'    => $body['recurso_id'],
        'tipo_problema' => $body['tipo_problema'],
        'descricao'     => $body['descricao'] ?? null,
    ]);
} catch (PDOException $e) {
    if ($e->getCode() === '23503') {
        json_error('Recurso ou reserva informados não existem.', 404);
    }
    throw $e;
}

json_response($stmt->fetch(), 201);
