<?php
// Permite acessos de qualquer origem (ou coloque 'http://localhost:9000' se quiser restringir)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Interceta a requisição de pré-verificação (Preflight) do navegador e responde com Sucesso (200) imediatamente
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}