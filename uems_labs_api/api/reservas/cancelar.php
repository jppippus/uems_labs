<?php

require_once __DIR__ . '/../../helpers/cors.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Método não permitido.', 405);
}

$usuario = require_auth();
$body = json_body();

if (empty($body['reserva_id'])) {
    json_error('O campo "reserva_id" é obrigatório.', 422);
}

try {
    $pdo = get_db_connection();

    $stmt = $pdo->prepare("
        SELECT usuario_id, status,
               ((data_reserva + hora_fim) > (now() AT TIME ZONE 'America/Campo_Grande')) AS futura
        FROM reservas
        WHERE id = :id
    ");
    $stmt->execute([':id' => $body['reserva_id']]);
    $reserva = $stmt->fetch();

    if (!$reserva) {
        json_error('Reserva não encontrada.', 404);
    }
    if ($reserva['usuario_id'] !== $usuario['id']) {
        json_error('Você só pode cancelar as suas próprias reservas.', 403);
    }
    if ($reserva['status'] !== 'ativa') {
        json_error('Esta reserva não está ativa.', 409);
    }
    if (!$reserva['futura']) {
        json_error('Esta reserva já foi encerrada e não pode ser cancelada.', 409);
    }

    $stmt = $pdo->prepare("
        UPDATE reservas SET status = 'cancelada'
        WHERE id = :id AND usuario_id = :usuario_id AND status = 'ativa'
        RETURNING id, status
    ");
    $stmt->execute([':id' => $body['reserva_id'], ':usuario_id' => $usuario['id']]);

    json_response($stmt->fetch());
} catch (PDOException $e) {
    json_error('Erro de SQL: ' . $e->getMessage(), 500);
}
