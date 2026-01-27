<?php
declare(strict_types=1);

function api_ok($data = null, string $message = 'OK', int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');

    echo json_encode([
        'success' => true,
        'message' => $message,
        'data' => $data,
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

function api_error(string $code, string $message, int $status = 400, $details = null): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');

    $payload = [
        'success' => false,
        'error' => [
            'code' => $code,
            'message' => $message,
        ],
    ];

    if ($details !== null) {
        $payload['error']['details'] = $details;
    }

    echo json_encode($payload, JSON_UNESCAPED_UNICODE);

    exit;
}
