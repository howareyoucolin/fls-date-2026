<?php
declare(strict_types=1);

require_once __DIR__ . '/../api/_response.php';
require_once __DIR__ . '/../api/_clerk_jwt.php';

$path = trim($_GET['api_path'] ?? '', "/ \t\n\r\0\x0B");

/**
 * Enforce whitelist using Clerk user ID (JWT `sub`).
 * Throws API errors and exits on failure.
 */
function api_require_whitelisted(string $path): void {
    global $db;
    if (!$db) {
        api_error('db_not_initialized', 'DB not initialized', 500);
    }

    // Get verified JWT claims
    $claims = $GLOBALS['api_claims'] ?? [];
    if (!is_array($claims)) {
        api_error('unauthenticated', 'Missing JWT claims', 401);
    }

    // Extract Clerk User ID from token
    $userId = trim((string)($claims['sub'] ?? ''));
    if ($userId === '') {
        api_error('unauthenticated', 'Missing user id (sub) in token', 401);
    }

    // Escape safely (DB wrapper has no prepare())
    if (function_exists('esc_sql')) {
        $userIdSql = esc_sql($userId);
    } else {
        $userIdSql = addslashes($userId);
    }

    // Check whitelist by clerk_user_id
    $sql = "
        SELECT role
        FROM cz_whitelist
        WHERE clerk_user_id = '{$userIdSql}'
        LIMIT 1
    ";

    $role = $db->get_var($sql);

    if (!$role) {
        api_error('not_whitelisted', 'User not whitelisted', 403, [
            'user_id' => $userId,
        ]);
    }
}


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

    // Enforce whitelist for all API routes except /whitelist
    // We intentionally SKIP the whitelist check for the `/whitelist` endpoint so the frontend
    // can determine access and route the user to `/403` if needed.
    // All other routes MUST pass the whitelist check to prevent direct API access
    // by non-whitelisted users (even if they are authenticated).
    if ($path !== 'whitelist') {
        api_require_whitelisted($path);
    }

    // Route map
    $routes = [
        'message_counts' => 'message_counts.php',
        'member_counts'  => 'member_counts.php',

        'members_list' => 'members_list.php',
        'member_set_approved' => 'member_set_approved.php',
        'member_archive' => 'member_archive.php',

        // messages page
        'messages_list' => 'messages_list.php',
        'message_mark_read' => 'message_mark_read.php',

        'whitelist' => 'auth_whitelist.php',
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
