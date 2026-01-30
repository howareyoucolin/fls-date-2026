<?php
declare(strict_types=1);

require_once __DIR__ . '/_response.php';

global $db;

try {
    if (!$db) {
        api_error('db_not_initialized', 'DB not initialized', 500);
    }

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
        api_error('method_not_allowed', 'Only GET is allowed', 405);
    }

    // Read x-user-email header case-insensitively
    $email = null;

    // In PHP, custom headers usually come in as HTTP_X_USER_EMAIL
    foreach ($_SERVER as $k => $v) {
        if (strtoupper((string)$k) === 'HTTP_X_USER_EMAIL') {
            $email = (string)$v;
            break;
        }
    }

    // Fallback to getallheaders() (some environments)
    if (!$email && function_exists('getallheaders')) {
        $headers = getallheaders();
        foreach ($headers as $k => $v) {
            if (strtolower((string)$k) === 'x-user-email') {
                $email = (string)$v;
                break;
            }
        }
    }

    $email = $email ? trim($email) : '';

    if ($email === '') {
        api_error('missing_email_header', 'Missing x-user-email header', 401);
    }

    // Normalize for matching
    $emailNorm = strtolower($email);

    // Escape safely (since wrapper has no prepare())
    // Prefer esc_sql if available; otherwise fallback to addslashes
    if (function_exists('esc_sql')) {
        $emailSql = esc_sql($emailNorm);
    } else {
        $emailSql = addslashes($emailNorm);
    }

    // Case-insensitive match even if DB contains weird casing/spaces
    // (TRIM/LOWER handled on both sides)
    $sql = "
        SELECT role
        FROM cz_whitelist
        WHERE LOWER(TRIM(email)) = '{$emailSql}'
        LIMIT 1
    ";

    $role = $db->get_var($sql);

    if (!$role) {
        api_error('not_whitelisted', 'Email not whitelisted', 403, [
            'email' => $emailNorm, // helpful for debugging; remove later if you want
        ]);
    }

    api_ok([
        'allowed' => true,
        'role' => (string)$role,
        'email' => $emailNorm,
    ], 'Whitelisted');
} catch (Throwable $e) {
    api_error('server_error', $e->getMessage(), 500);
}
