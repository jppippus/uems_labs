<?php

require_once __DIR__ . '/../helpers/cors.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Método não permitido.', 405);
}

// Valida o login e pega os dados do utilizador
$usuario = require_auth();
$body = json_body();

if (empty($body['laboratorioId']) || empty($body['horariosSelecionados'])) {
    json_error('Dados incompletos. Selecione o laboratório e os horários.', 422);
}

if ($body['tipoReserva'] === 'fixa' && $usuario['perfil'] !== 'professor') {
    json_error('Apenas professores podem fazer reservas fixas recorrentes.', 403);
}

// 1. Prepara a tradução de dias e formatação para o banco
$diasIngles = [
    'Segunda' => 'Monday', 'Terça' => 'Tuesday', 'Quarta' => 'Wednesday',
    'Quinta' => 'Thursday', 'Sexta' => 'Friday'
];

// Transforma "Sala Completa" em "sala_completa" para bater com o ENUM tipo_recurso
$tipoRecursoBanco = strtolower(str_replace(' ', '_', $body['tipoRecurso']));
$proposito = $body['motivo'] . (!empty($body['descricao']) ? " - " . $body['descricao'] : "");

$pdo = get_db_connection();

try {
    $pdo->beginTransaction();

    foreach ($body['horariosSelecionados'] as $item) {
        // Exemplo: "Quarta|13:30 - 15:30"
        list($dia_pt, $bloco) = explode('|', $item);
        list($hora_inicio, $hora_fim) = explode(' - ', $bloco);

        // 2. Calcula as datas automaticamente
        $dia_en = $diasIngles[$dia_pt] ?? 'Monday';
        $data_inicio = date('Y-m-d', strtotime("next $dia_en"));
        
        // Se for reserva fixa, definimos o fim do semestre (ex: 20 de Dezembro)
        $data_fim = ($body['tipoReserva'] === 'fixa') ? '2026-12-20' : $data_inicio;

       // 3. Descobre o recurso_id
        $recurso_id = null;

        if (!empty($body['recursoId'])) {
            // Se o usuário selecionou um PC específico na tela
            $recurso_id = $body['recursoId'];
        } else {
            // Se escolheu "Sala Completa", procura o ID genérico da sala
            $stmtRecurso = $pdo->prepare("SELECT id FROM recursos WHERE laboratorio_id = :lab_id AND tipo = :tipo AND status = 'disponivel' LIMIT 1");
            $stmtRecurso->execute([':lab_id' => $body['laboratorioId'], ':tipo' => $tipoRecursoBanco]);
            $rec = $stmtRecurso->fetch(PDO::FETCH_ASSOC);
            
            if (!$rec) throw new Exception("Não há '{$body['tipoRecurso']}' disponível neste laboratório.");
            $recurso_id = $rec['id'];
        }

        // 4. Efetua a gravação consoante o tipo de reserva
        if ($body['tipoReserva'] === 'fixa') {
            $stmt = $pdo->prepare('SELECT * FROM criar_reserva_fixa(:usuario_id, :recurso_id, :hora_inicio, :hora_fim, :proposito, :data_inicio, :data_fim)');
            $stmt->execute([
                ':usuario_id'  => $usuario['id'],
                ':recurso_id'  => $recurso_id, 
                ':hora_inicio' => trim($hora_inicio),
                ':hora_fim'    => trim($hora_fim),
                ':proposito'   => $proposito,
                ':data_inicio' => $data_inicio,
                ':data_fim'    => $data_fim
            ]);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO reservas (usuario_id, recurso_id, data_reserva, hora_inicio, hora_fim, proposito, status) 
                VALUES (:usuario_id, :recurso_id, :data_reserva, :hora_inicio, :hora_fim, :proposito, 'ativa')
            ");
            $stmt->execute([
                ':usuario_id'   => $usuario['id'],
                ':recurso_id'   => $recurso_id, // <--- Aqui estava o erro, agora está corrigido!
                ':data_reserva' => $data_inicio,
                ':hora_inicio'  => trim($hora_inicio),
                ':hora_fim'     => trim($hora_fim),
                ':proposito'    => $proposito
            ]);
        }
    }

    $pdo->commit();

    json_response([
        'status' => 'sucesso',
        'mensagem' => 'Reserva efetuada com sucesso!'
    ], 201);

} catch (PDOException $e) {
    $pdo->rollBack();
    if ($e->getCode() === '23P01') {
        json_error('Esse horário já está reservado para o recurso. Por favor, escolha outro horário ou dia.', 409);
    }
    json_error('Erro ao guardar no banco: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    $pdo->rollBack();
    json_error($e->getMessage(), 400);
}