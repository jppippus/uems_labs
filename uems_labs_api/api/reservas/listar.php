<?php
ini_set('display_errors', 0); // Evita que erros do PHP sujem o JSON

require_once __DIR__ . '/../../helpers/cors.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../middleware/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_error('Método não permitido.', 405);
}

$laboratorio_id = $_GET['laboratorio_id'] ?? null;

// RF008 - Minhas Reservas: sem laboratorio_id, lista as reservas do usuário
// logado, separadas em "ativas" (ainda não terminaram) e "historico".
// Com laboratorio_id, mantém o comportamento original (usado pela ReservaPage).
if (!$laboratorio_id) {
    $usuario = require_auth();

    try {
        $pdo = get_db_connection();

        $stmt = $pdo->prepare("
            SELECT
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
            INNER JOIN recursos rec ON res.recurso_id = rec.id
            INNER JOIN laboratorios lab ON rec.laboratorio_id = lab.id
            WHERE res.usuario_id = :usuario_id
            ORDER BY res.data_reserva, res.hora_inicio
        ");
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
        // Histórico: mais recente primeiro
        $historico = array_reverse($historico);

        json_response(['ativas' => $ativas, 'historico' => $historico]);
    } catch (PDOException $e) {
        json_error('Erro de SQL: ' . $e->getMessage(), 500);
    }
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