<?php
declare(strict_types=1);

require_once __DIR__ . '/../api/_response.php';

$path = trim($_GET['api_path'] ?? '', "/ \t\n\r\0\x0B");

try {
    $routes = [
        'contacts/unread-count' => 'contacts_unread_count.php',
    ];

    if (!isset($routes[$path])) {
        api_error('not_found', 'API route not found', 404, ['path' => $path]);
    }

    $handler = __DIR__ . '/../api/' . $routes[$path];

    if (!file_exists($handler)) {
        api_error('handler_missing', 'API handler missing', 500, ['handler' => basename($handler)]);
    }

    require $handler;
    api_error('no_response', 'API handler did not return a response', 500);
} catch (Throwable $e) {
    api_error('server_error', $e->getMessage(), 500);
}
