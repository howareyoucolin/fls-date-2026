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

    // Get verified JWT claims (set by router before including this file)
    $claims = $GLOBALS['api_claims'] ?? [];
    if (!is_array($claims)) {
        api_error('unauthenticated', 'Missing JWT claims', 401);
    }

    // Extract Clerk User ID (JWT `sub`)
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

    // Lookup whitelist by clerk_user_id
    $sql = "
        SELECT role
        FROM cz_whitelist
        WHERE clerk_user_id = '{$userIdSql}'
        LIMIT 1
    ";

    $role = $db->get_var($sql);

    if (!$role) {
        api_error('not_whitelisted', 'User not whitelisted', 403, [
            'user_id' => $userId, // helpful for debugging; remove later if you want
        ]);
    }

    api_ok([
        'allowed' => true,
        'role' => (string)$role,
        'user_id' => $userId,
    ], 'Whitelisted');
} catch (Throwable $e) {
    api_error('server_error', $e->getMessage(), 500);
}
