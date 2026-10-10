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

// 1) Trava o método HTTP esperado.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Método não permitido.', 405);
}

// 2) Valida o token do Supabase Auth e recupera o perfil do usuário.
//    $usuario = ['id' => ..., 'rgm' => ..., 'nome' => ..., 'perfil' => 'aluno'|'professor'|'tecnico']
$usuario = require_auth();

// 2.1) Se o endpoint for exclusivo de um perfil (ex: área do técnico):
// require_perfil($usuario, ['tecnico']);

// 3) Lê e valida o corpo da requisição (se for POST/PUT).
$body = json_body();
if (empty($body['campo_obrigatorio'])) {
    json_error('O campo "campo_obrigatorio" é obrigatório.', 422);
}

// 4) Executa a query com PDO (sempre com prepared statements).
$pdo = get_db_connection();
$stmt = $pdo->prepare('SELECT 1');
$stmt->execute();

// 5) Responde em JSON. json_response() já encerra a execução.
json_response(['ok' => true, 'usuario_id' => $usuario['id']]);
