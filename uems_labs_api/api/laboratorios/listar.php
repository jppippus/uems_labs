<?php

require_once __DIR__ . '/../../helpers/cors.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../config/database.php';

// GET /uems-api/api/laboratorios/listar.php
// Retorna todos os laboratórios com seus recursos (salas, mesas, computadores).

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_error('Método não permitido.', 405);
}

require_auth();

$pdo = get_db_connection();

$laboratorios = $pdo->query('SELECT id, nome, localizacao FROM laboratorios ORDER BY nome')->fetchAll();

$recursosStmt = $pdo->prepare(
    'SELECT id, laboratorio_id, tipo, identificacao, status FROM recursos WHERE laboratorio_id = :lab_id ORDER BY identificacao'
);

foreach ($laboratorios as &$laboratorio) {
    $recursosStmt->execute(['lab_id' => $laboratorio['id']]);
    $laboratorio['recursos'] = $recursosStmt->fetchAll();
}
unset($laboratorio);

json_response($laboratorios);
