<?php

/**
 * MODELO DE ENDPOINT — copie este arquivo como ponto de partida para o seu.
 * Não é chamado pelo frontend, serve só de referência do padrão a seguir.
 *
 * Padrão de pastas: api/<dominio>/<acao>.php
 *   ex: api/reservas/criar.php, api/problemas/relatar.php
 */

require_once __DIR__ . '/../helpers/cors.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Método não permitido.', 405);
}

$usuario = require_auth();


 require_perfil($usuario, ['tecnico']);


$body = json_body();
if (empty($body['campo_obrigatorio'])) {
    json_error('O campo "campo_obrigatorio" é obrigatório.', 422);
}


$pdo = get_db_connection();
$stmt = $pdo->prepare('SELECT r.*, l.nome as laboratorio_nome, u.nome as usuario_nome 
                       FROM reservas r 
                       JOIN laboratorios l ON r.laboratorio_id = l.id 
                       JOIN usuarios u ON r.usuario_id = u.id 
                       ORDER BY r.created_at DESC');
$stmt->execute();
$reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);


json_response(['ok' => true, 'reservas' => $reservas]);
