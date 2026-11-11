<?php
ini_set('display_errors', 0); // Evita que erros do PHP sujem o JSON

require_once __DIR__ . '/../../helpers/cors.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_error('Método não permitido.', 405);
}

$laboratorio_id = $_GET['laboratorio_id'] ?? null;

if (!$laboratorio_id) {
    json_error('O ID do laboratório é obrigatório.', 400);
}

try {
    $pdo = get_db_connection();

    // Traz todas as reservas ativas + o tipo de recurso (sala, computador ou mesa)
    $stmt = $pdo->prepare("
        SELECT 
            res.id, 
            res.recurso_id, 
            res.tipo, 
            res.data_reserva, 
            res.hora_inicio,
            rec.tipo as recurso_tipo
        FROM reservas res
        INNER JOIN recursos rec ON res.recurso_id = rec.id
        WHERE rec.laboratorio_id = :lab_id 
          AND res.status = 'ativa'
    ");
    
    $stmt->execute([':lab_id' => $laboratorio_id]);
    $reservas = $stmt->fetchAll();

    echo json_encode($reservas);
    exit;
    
} catch (PDOException $e) {
    json_error("Erro de SQL: " . $e->getMessage(), 500);
}