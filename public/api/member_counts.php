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
            SUM(CASE WHEN is_approved = 1 THEN 1 ELSE 0 END) AS active,
            SUM(CASE WHEN is_approved = 0 THEN 1 ELSE 0 END) AS inactive
        FROM cz_members
        WHERE is_archived = 0
    ");

    api_ok([
        'total'    => (int) ($row->total ?? 0),
        'active'   => (int) ($row->active ?? 0),
        'inactive' => (int) ($row->inactive ?? 0),
    ], 'Member counts fetched');

} catch (Throwable $e) {
    api_error('server_error', $e->getMessage(), 500);
}
