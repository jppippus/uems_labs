<?php

/**
 * Libera CORS para o frontend Quasar (que roda em outra porta/origem)
 * e responde de imediato o preflight OPTIONS.
 *
 * Incluir no topo de TODO endpoint, antes de qualquer outra lógica.
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}
