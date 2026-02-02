<?php
declare(strict_types=1);

require_once __DIR__ . '/_response.php';

global $db;

try {
    if (!$db) {
        api_error('db_not_initialized', 'DB not initialized', 500);
    }

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        api_error('method_not_allowed', 'Only POST is allowed', 405);
    }

    $raw = file_get_contents('php://input');
    $payload = json_decode($raw ?: '[]', true);

    if (!is_array($payload)) {
        api_error('bad_request', 'Invalid JSON body', 400);
    }

    $id = (int)($payload['id'] ?? 0);
    if ($id <= 0) {
        api_error('bad_request', 'Missing or invalid "id"', 400);
    }

    // Use int-cast to keep SQL safe (no string injection risk)
    $row = $db->get_row("
        SELECT id, is_archived
        FROM cz_members
        WHERE id = {$id}
        LIMIT 1
    ");

    if (!$row) {
        api_error('not_found', 'Member not found', 404);
    }

    $isArchived = (int)($row->is_archived ?? 0);

    // Idempotent success
    if ($isArchived === 0) {
        api_ok([
            'id' => $id,
            'is_archived' => 0,
        ], 'Member already unarchived');
    }

    $ok = $db->query("
        UPDATE cz_members
        SET
            is_archived = 0,
            updated_at = NOW()
        WHERE id = {$id}
        LIMIT 1
    ");

    if ($ok === false) {
        api_error('db_error', 'Failed to unarchive member', 500);
    }

    api_ok([
        'id' => $id,
        'is_archived' => 0,
    ], 'Member unarchived');
} catch (Throwable $e) {
    api_error('server_error', $e->getMessage(), 500);
}
