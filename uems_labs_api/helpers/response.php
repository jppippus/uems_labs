<?php

/**
 * Qualquer exceção não tratada (erro de SQL inesperado, etc.) vira um
 * 500 limpo em JSON, em vez de uma página de erro em HTML com stack
 * trace. O detalhe real vai pro log do PHP/Apache, não pro cliente.
 */
set_exception_handler(function (Throwable $e): void {
    error_log('[uems-api] Exceção não tratada: ' . $e);
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode(['error' => 'Erro interno. Tente novamente.'], JSON_UNESCAPED_UNICODE);
});

function json_response($data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function json_error(string $message, int $status = 400): void
{
    json_response(['error' => $message], $status);
}

/**
 * Lê e decodifica o corpo JSON da requisição.
 * Em caso de JSON inválido, já responde 400 e encerra.
 */
function json_body(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === '' || $raw === false) {
        return [];
    }

    $data = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        json_error('JSON inválido no corpo da requisição.', 400);
    }

    return $data ?? [];
}
