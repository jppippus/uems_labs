<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../../helpers/cors.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Método não permitido.', 405);
}

$usuario = require_auth();
$body = json_body();

if (empty($body['data_reserva']) || empty($body['hora_inicio']) || empty($body['hora_fim'])) {
    json_error('Dados de horário incompletos.', 422);
}

$pdo = get_db_connection();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    $pdo->beginTransaction();

    // Se o usuário selecionou "Sala Completa", o recurso_id vem vazio. 
    // Precisamos de usar o laboratorio_id para buscar o ID genérico da sala no banco.
    // Se o usuário selecionou "Sala Completa", o recurso_id vem vazio. 
    // Precisamos buscar o ID genérico da sala no banco.
    if (empty($body['recurso_id'])) {
        
        // Vamos forçar a string que o banco de dados provavelmente espera.
        // É comum, em projetos Supabase, a sala inteira ser cadastrada simplesmente como "sala"
        $tipoRecursoBanco = 'sala'; 
        
        // Verifica se é computador ou mesa, para outras traduções seguras
        if ($body['tipo_recurso'] === 'Computador') {
            $tipoRecursoBanco = 'computador';
        } elseif ($body['tipo_recurso'] === 'Apenas Mesa') {
            $tipoRecursoBanco = 'mesa';
        }

        $stmtRecurso = $pdo->prepare("SELECT id FROM recursos WHERE laboratorio_id = :lab_id AND tipo = :tipo AND status = 'disponivel' LIMIT 1");
        
        $stmtRecurso->execute([
            ':lab_id' => $body['laboratorio_id'],
            ':tipo'   => $tipoRecursoBanco
        ]);
        
        $rec = $stmtRecurso->fetch(PDO::FETCH_ASSOC);
        
        if (!$rec) {
            // Se falhou, vamos tentar uma última vez usando 'sala_completa' 
            // mas agora passando a string como se estivesse no banco
             $stmtRecurso->execute([
                ':lab_id' => $body['laboratorio_id'],
                ':tipo'   => 'sala_completa' // Último recurso se o anterior falhar
            ]);
            $rec = $stmtRecurso->fetch(PDO::FETCH_ASSOC);
            
            if (!$rec) {
               json_error("Não foi possível encontrar a '{$body['tipo_recurso']}' na base de dados. O tipo de recurso ($tipoRecursoBanco) não foi localizado.", 404);
            }
        }
        $body['recurso_id'] = $rec['id'];
    }

    $data_fim = ($body['tipo'] === 'fixa') ? '2026-12-20' : $body['data_reserva'];

    if ($body['tipo'] === 'fixa') {
        $stmt = $pdo->prepare('SELECT * FROM criar_reserva_fixa(:usuario_id, :recurso_id, :hora_inicio, :hora_fim, :proposito, :data_inicio, :data_fim)');
        $stmt->execute([
            ':usuario_id'  => $usuario['id'],
            ':recurso_id'  => $body['recurso_id'], 
            ':hora_inicio' => $body['hora_inicio'],
            ':hora_fim'    => $body['hora_fim'],
            ':proposito'   => $body['proposito'],
            ':data_inicio' => $body['data_reserva'],
            ':data_fim'    => $data_fim
        ]);
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO reservas (usuario_id, recurso_id, data_reserva, hora_inicio, hora_fim, proposito, status) 
            VALUES (:usuario_id, :recurso_id, :data_reserva, :hora_inicio, :hora_fim, :proposito, 'ativa')
        ");
        $stmt->execute([
            ':usuario_id'   => $usuario['id'],
            ':recurso_id'   => $body['recurso_id'],
            ':data_reserva' => $body['data_reserva'],
            ':hora_inicio'  => $body['hora_inicio'],
            ':hora_fim'     => $body['hora_fim'],
            ':proposito'    => $body['proposito']
        ]);
    }

    $pdo->commit();

    json_response([
        'status' => 'sucesso',
        'mensagem' => 'Reserva efetuada com sucesso!'
    ], 201);

} catch (PDOException $e) {
    $pdo->rollBack();
    if ($e->getCode() === '23P01') {
        json_error('Esse horário já está reservado. Escolha outro.', 409);
    }
    json_error('Erro de SQL: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    $pdo->rollBack();
    json_error($e->getMessage(), 400);
}