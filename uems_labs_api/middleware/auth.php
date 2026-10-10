<?php

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';

/**
 * Valida o token de sessão do Supabase Auth enviado pelo frontend e
 * retorna o perfil correspondente na tabela "usuarios".
 *
 * O frontend deve mandar o header:
 *   Authorization: Bearer <access_token do supabase.auth.getSession()>
 *
 * Em caso de token ausente/inválido, já responde 401 e encerra a requisição.
 *
 * @return array{id:string, rgm:string, nome:string, perfil:string}
 */
function require_auth(): array
{
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

    if (!preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches)) {
        json_error('Token de autenticação ausente.', 401);
    }
    $token = $matches[1];

    $supabaseUrl = env('SUPABASE_URL');
    $anonKey = env('SUPABASE_ANON_KEY');

    $ch = curl_init(rtrim($supabaseUrl, '/') . '/auth/v1/user');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer $token",
            "apikey: $anonKey",
        ],
        CURLOPT_TIMEOUT => 5,
    ]);
    $body = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($body === false) {
        json_error('Não foi possível validar a sessão (' . $curlError . ').', 502);
    }
    if ($httpCode !== 200) {
        json_error('Sessão inválida ou expirada.', 401);
    }

    $authUser = json_decode($body, true);
    $userId = $authUser['id'] ?? null;
    if (!$userId) {
        json_error('Não foi possível validar o usuário.', 401);
    }

    $pdo = get_db_connection();
    $stmt = $pdo->prepare('SELECT id, rgm, nome, perfil FROM usuarios WHERE id = :id');
    $stmt->execute(['id' => $userId]);
    $usuario = $stmt->fetch();

    if (!$usuario) {
        json_error('Usuário autenticado mas sem perfil cadastrado em "usuarios".', 403);
    }

    return $usuario;
}

/**
 * Bloqueia o acesso (403) se o perfil do usuário autenticado não estiver
 * entre os perfis permitidos. Use depois de require_auth().
 *
 * Exemplo: require_perfil($usuario, ['tecnico']);
 */
function require_perfil(array $usuario, array $perfisPermitidos): void
{
    if (!in_array($usuario['perfil'], $perfisPermitidos, true)) {
        json_error('Acesso não permitido para este perfil.', 403);
    }
}
