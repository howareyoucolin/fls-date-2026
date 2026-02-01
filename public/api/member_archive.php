<?php
declare(strict_types=1);

require_once __DIR__ . '/_response.php';

global $db;

try {
    if (!$db) api_error('db_not_initialized', 'DB not initialized', 500);

    if (($_SERVER['REQUEST_METHOD'] ?? 'POST') !== 'POST') {
        api_error('method_not_allowed', 'Only POST is allowed', 405);
    }

    $raw = file_get_contents('php://input');
    $body = json_decode($raw ?: '', true);

    if (!is_array($body)) {
        api_error('bad_request', 'Invalid JSON body', 400);
    }

    $id = (int)($body['id'] ?? 0);

    if ($id <= 0) {
        api_error('bad_request', 'Missing/invalid id', 400);
    }

    $affected = $db->query("
        UPDATE cz_members
        SET
            is_archived = 1,
            updated_at = CURRENT_TIMESTAMP
        WHERE id = {$id}
        LIMIT 1
    ");

    if ($affected === false) {
        api_error('db_error', 'Archive failed', 500);
    }

    if ($affected === 0) {
        api_error('not_found', 'Member not found or already archived', 404);
    }

    api_ok([
        'id' => $id,
        'is_archived' => 1,
    ], 'Member archived');

} catch (Throwable $e) {
    api_error('server_error', $e->getMessage(), 500);
}
