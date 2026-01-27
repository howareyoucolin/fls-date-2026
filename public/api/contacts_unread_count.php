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

    $unread = (int) $db->get_var("
        SELECT COUNT(*)
        FROM cz_contacts
        WHERE is_read = 0
    ");

    api_ok(['unread' => $unread], 'Unread count fetched');
} catch (Throwable $e) {
    api_error('server_error', $e->getMessage(), 500);
}
