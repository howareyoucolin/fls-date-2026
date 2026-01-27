<?php
declare(strict_types=1);

require_once __DIR__ . '/../api/_response.php';
require_once __DIR__ . '/../api/_clerk_jwt.php';

$path = trim($_GET['api_path'] ?? '', "/ \t\n\r\0\x0B");

/**
 * Extract Bearer token from Authorization header.
 */
function api_get_bearer_token(): ?string {
    // 1) Standard places
    $header =
        $_SERVER['HTTP_AUTHORIZATION']
        ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
        ?? $_SERVER['Authorization'] // some servers
        ?? '';

    // 2) Fallback: read from getallheaders()
    if (!$header && function_exists('getallheaders')) {
        $headers = getallheaders();
        if (is_array($headers)) {
            // case-insensitive lookup
            foreach ($headers as $k => $v) {
                if (strtolower((string)$k) === 'authorization') {
                    $header = (string)$v;
                    break;
                }
            }
        }
    }

    if (!$header) return null;

    if (preg_match('/^\s*Bearer\s+(.+)\s*$/i', $header, $m)) {
        return trim($m[1]);
    }
    return null;
}


try {
    // ✅ Global auth gate for ALL API routes
    $token = api_get_bearer_token();
    if (!$token) {
        api_error('unauthenticated', 'Missing Authorization: Bearer <token>', 401);
    }

    // Your token's `iss` was: https://trusted-albacore-0.clerk.accounts.dev
    // Use the exact issuer for verification.
    $issuer = 'https://trusted-albacore-0.clerk.accounts.dev';

    try {
        $claims = clerk_verify_jwt($token, $issuer);
        $GLOBALS['api_claims'] = $claims; // handlers can use this if needed
    } catch (Throwable $e) {
        api_error('unauthenticated', 'Invalid token: ' . $e->getMessage(), 401);
    }

    // Route map
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

    // If handler didn't exit, that's a bug
    api_error('no_response', 'API handler did not return a response', 500);
} catch (Throwable $e) {
    api_error('server_error', $e->getMessage(), 500);
}
