<?php

require_once __DIR__ . '/../../helpers/cors.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../config/database.php';

// POST /uems-api/api/reservas/criar.php
// RF002 (normal) e RF003 (fixa, exclusivo de professor).

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Método não permitido.', 405);
}

$usuario = require_auth();
$body = json_body();

if (empty($body['data_reserva']) || empty($body['hora_inicio']) || empty($body['hora_fim'])) {
    json_error('Dados de horário incompletos.', 422);
}
if (empty($body['proposito'])) {
    json_error('Informe o propósito da reserva.', 422);
}
if (empty($body['tipo_recurso'])) {
    json_error('Informe o tipo de recurso (Sala Completa, Computador ou Apenas Mesa).', 422);
}
// Quando não vem um recurso específico (ex: "Sala Completa"), o
// laboratorio_id é obrigatório para localizar o recurso certo.
if (empty($body['recurso_id']) && empty($body['laboratorio_id'])) {
    json_error('Informe o laboratório.', 422);
}

$tipo = $body['tipo'] ?? 'normal';

// RF003: reserva fixa é exclusiva de professores — a UI já esconde essa
// opção pra aluno/técnico, mas o backend precisa garantir isso também.
if ($tipo === 'fixa') {
    require_perfil($usuario, ['professor']);
}

$pdo = get_db_connection();

try {
    $pdo->beginTransaction();

    $recursoId = $body['recurso_id'] ?? null;

    // "Sala Completa" não tem um recurso_id vindo do frontend — localizamos
    // o recurso tipo "sala" desse laboratório (cada laboratório tem só um).
    if (empty($recursoId)) {
        $tipoRecursoBanco = match ($body['tipo_recurso']) {
            'Computador' => 'computador',
            'Apenas Mesa' => 'mesa',
            default => 'sala',
        };

        $stmtRecurso = $pdo->prepare(
            "SELECT id FROM recursos WHERE laboratorio_id = :lab_id AND tipo = :tipo AND status = 'disponivel' LIMIT 1"
        );
        $stmtRecurso->execute([
            ':lab_id' => $body['laboratorio_id'],
            ':tipo'   => $tipoRecursoBanco,
        ]);
        $rec = $stmtRecurso->fetch();

        if (!$rec) {
            $pdo->rollBack();
            json_error("Nenhum recurso do tipo \"{$body['tipo_recurso']}\" disponível nesse laboratório no momento.", 404);
        }
        $recursoId = $rec['id'];
    }

    if ($tipo === 'fixa') {
        // Reserva fixa semanal até o fim do semestre.
        $dataFimSemestre = '2026-12-20';
        $stmt = $pdo->prepare(
            'SELECT * FROM criar_reserva_fixa(:usuario_id, :recurso_id, :hora_inicio, :hora_fim, :proposito, :data_inicio, :data_fim)'
        );
        $stmt->execute([
            ':usuario_id'  => $usuario['id'],
            ':recurso_id'  => $recursoId,
            ':hora_inicio' => $body['hora_inicio'],
            ':hora_fim'    => $body['hora_fim'],
            ':proposito'   => $body['proposito'],
            ':data_inicio' => $body['data_reserva'],
            ':data_fim'    => $dataFimSemestre,
        ]);
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO reservas (usuario_id, recurso_id, tipo, data_reserva, hora_inicio, hora_fim, proposito, status)
             VALUES (:usuario_id, :recurso_id, 'normal', :data_reserva, :hora_inicio, :hora_fim, :proposito, 'ativa')"
        );
        $stmt->execute([
            ':usuario_id'   => $usuario['id'],
            ':recurso_id'   => $recursoId,
            ':data_reserva' => $body['data_reserva'],
            ':hora_inicio'  => $body['hora_inicio'],
            ':hora_fim'     => $body['hora_fim'],
            ':proposito'    => $body['proposito'],
        ]);
    }

    $pdo->commit();

    json_response(['status' => 'sucesso', 'mensagem' => 'Reserva efetuada com sucesso!'], 201);
} catch (PDOException $e) {
    $pdo->rollBack();
    if ($e->getCode() === '23P01') {
        json_error('Esse horário já está reservado. Escolha outro.', 409);
    }
    json_error('Não foi possível concluir a reserva. Tente novamente.', 500);
}
