<?php
declare(strict_types=1);

require_once __DIR__ . '/../api/_response.php';
require_once __DIR__ . '/../api/_clerk_jwt.php';

$path = trim($_GET['api_path'] ?? '', "/ \t\n\r\0\x0B");

/**
 * Extract Bearer token from Authorization header.
 */
function api_get_bearer_token(): ?string {
    $header =
        $_SERVER['HTTP_AUTHORIZATION']
        ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
        ?? $_SERVER['Authorization'] // rare, but harmless
        ?? '';

    // Fallback: read from getallheaders()
    if (!$header && function_exists('getallheaders')) {
        $headers = getallheaders();
        if (is_array($headers)) {
            foreach ($headers as $k => $v) {
                if (strtolower((string) $k) === 'authorization') {
                    $header = (string) $v;
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

/**
 * Decode JWT payload WITHOUT verifying the signature.
 * Used only to read `iss` so we can choose the right Clerk issuer.
 */
function api_jwt_payload_unverified(string $jwt): array {
    $parts = explode('.', $jwt);
    if (count($parts) < 2) return [];

    $payload = strtr($parts[1], '-_', '+/');
    $payload .= str_repeat('=', (4 - (strlen($payload) % 4)) % 4);

    $json = base64_decode($payload, true);
    if (!is_string($json)) return [];

    $arr = json_decode($json, true);
    return is_array($arr) ? $arr : [];
}

/**
 * Get allowed issuers list from env:
 * CLERK_ALLOWED_ISSUERS="https://xxx.clerk.accounts.dev,https://yyy.clerk.accounts.com"
 */
function api_get_allowed_issuers(): array {
    $raw = getenv('CLERK_ALLOWED_ISSUERS') ?: '';
    if (!$raw) return [];
    $parts = array_map('trim', explode(',', $raw));
    return array_values(array_filter($parts, fn($s) => $s !== ''));
}

try {
    // ✅ Global auth gate for ALL API routes
    $token = api_get_bearer_token();
    if (!$token) {
        api_error('unauthenticated', 'Missing Authorization: Bearer <token>', 401);
    }

    // Prefer env-configured issuer; otherwise derive from token `iss`
    $issuer = trim((string) (getenv('CLERK_ISSUER') ?: ''));

    if ($issuer === '') {
        $unverified = api_jwt_payload_unverified($token);
        $issuer = trim((string) ($unverified['iss'] ?? ''));
    }

    if ($issuer === '') {
        api_error('unauthenticated', 'Token missing issuer (iss)', 401);
    }

    // Optional but recommended: whitelist issuers
    $allowedIssuers = api_get_allowed_issuers();
    if ($allowedIssuers && !in_array($issuer, $allowedIssuers, true)) {
        api_error('unauthenticated', 'Issuer not allowed', 401, ['iss' => $issuer]);
    }

    try {
        $claims = clerk_verify_jwt($token, $issuer);
        $GLOBALS['api_claims'] = $claims; // handlers can use this if needed
    } catch (Throwable $e) {
        api_error('unauthenticated', 'Invalid token: ' . $e->getMessage(), 401);
    }

    // Route map
    $routes = [
        'message_counts' => 'message_counts.php',
        'member_counts'  => 'member_counts.php',
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
