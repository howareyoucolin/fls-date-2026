<?php
declare(strict_types=1);

require_once __DIR__ . '/_response.php';

global $db;

try {
    if (!$db) {
        api_error('db_not_initialized', 'DB not initialized', 500);
    }

    if (($_SERVER['REQUEST_METHOD'] ?? 'POST') !== 'POST') {
        api_error('method_not_allowed', 'Only POST is allowed', 405);
    }

    $raw = file_get_contents('php://input') ?: '';
    $body = json_decode($raw, true);
    if (!is_array($body)) $body = [];

    $id = (int)($body['id'] ?? 0);
    if ($id <= 0) {
        api_error('bad_request', 'Missing or invalid id', 400);
    }

    $affected = $db->query("UPDATE cz_contacts SET is_read = 1 WHERE id = " . (int)$id);

    if ($affected === false) {
        api_error('db_error', 'Failed to update message', 500);
    }

    api_ok(['id' => $id, 'is_read' => 1], 'Marked as read');
} catch (Throwable $e) {
    api_error('server_error', $e->getMessage(), 500);
}