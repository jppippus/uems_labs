<?php

require_once __DIR__ . '/../../helpers/cors.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../config/database.php';

// GET /uems-api/api/reservas/listar.php
// Sem laboratorio_id: RF008, lista as reservas do usuário logado,
// separadas em "ativas" e "historico".
// Com laboratorio_id: usado pela grade de horários da ReservaPage,
// traz as reservas ativas desse laboratório (de qualquer usuário).

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_error('Método não permitido.', 405);
}

$usuario = require_auth();
$pdo = get_db_connection();
$laboratorioId = $_GET['laboratorio_id'] ?? null;

if (!$laboratorioId) {
    $stmt = $pdo->prepare(
        "SELECT
            res.id,
            res.tipo,
            res.data_reserva,
            res.hora_inicio,
            res.hora_fim,
            res.proposito,
            res.status,
            rec.tipo AS recurso_tipo,
            rec.identificacao AS recurso_identificacao,
            lab.nome AS laboratorio_nome,
            (res.status = 'ativa'
             AND (res.data_reserva + res.hora_fim) > (now() AT TIME ZONE 'America/Campo_Grande')
            ) AS cancelavel
         FROM reservas res
         JOIN recursos rec ON res.recurso_id = rec.id
         JOIN laboratorios lab ON rec.laboratorio_id = lab.id
         WHERE res.usuario_id = :usuario_id
         ORDER BY res.data_reserva, res.hora_inicio"
    );
    $stmt->execute([':usuario_id' => $usuario['id']]);

    $ativas = [];
    $historico = [];
    foreach ($stmt->fetchAll() as $reserva) {
        if ($reserva['cancelavel']) {
            $ativas[] = $reserva;
        } else {
            $historico[] = $reserva;
        }
    }
    $historico = array_reverse($historico); // mais recente primeiro

    json_response(['ativas' => $ativas, 'historico' => $historico]);
}

$stmt = $pdo->prepare(
    "SELECT
        res.id,
        res.recurso_id,
        res.tipo,
        res.data_reserva,
        res.hora_inicio,
        rec.tipo AS recurso_tipo
     FROM reservas res
     JOIN recursos rec ON res.recurso_id = rec.id
     WHERE rec.laboratorio_id = :lab_id
       AND res.status = 'ativa'"
);
$stmt->execute([':lab_id' => $laboratorioId]);

json_response($stmt->fetchAll());
