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

    $row = $db->get_row("
        SELECT
            COUNT(*) AS total,
            SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) AS unread
        FROM cz_contacts
    ");

    api_ok([
        'total'  => (int) ($row->total ?? 0),
        'unread' => (int) ($row->unread ?? 0),
    ], 'Message counts fetched');

} catch (Throwable $e) {
    api_error('server_error', $e->getMessage(), 500);
}
