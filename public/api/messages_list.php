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

    $page = (int)($_GET['page'] ?? 1);
    $pageSize = (int)($_GET['pageSize'] ?? 20);
    $status = (string)($_GET['status'] ?? 'all');

    if ($page < 1) $page = 1;
    if ($pageSize < 1) $pageSize = 20;
    if ($pageSize > 100) $pageSize = 100;

    // Build WHERE safely (no placeholders, since this DB wrapper has no prepare())
    $where = '1=1';

    if ($status === 'read') {
        $where .= ' AND is_read = 1';
    } elseif ($status === 'unread') {
        $where .= ' AND is_read = 0';
    } else {
        $status = 'all';
    }

    // Total count
    $sqlTotal = "SELECT COUNT(*) FROM cz_contacts WHERE {$where}";
    $total = (int)$db->get_var($sqlTotal);

    $totalPages = (int)ceil($total / $pageSize);
    if ($totalPages < 1) $totalPages = 1;
    if ($page > $totalPages) $page = $totalPages;

    $offset = ($page - 1) * $pageSize;

    // Items
    $sqlItems = "
        SELECT id, name, wechat, email, message, is_read, created_at
        FROM cz_contacts
        WHERE {$where}
        ORDER BY created_at DESC
        LIMIT %d OFFSET %d
    ";

    // This DB wrapper doesn't have prepare(), so we safely inject ints only
    $rowsObj = $db->get_results(sprintf($sqlItems, (int)$pageSize, (int)$offset));
    if (!is_array($rowsObj)) $rowsObj = [];

    // Normalize to assoc arrays regardless of wrapper return type
    $rows = array_map(static function ($r): array {
        if (is_array($r)) return $r;
        if (is_object($r)) return get_object_vars($r);
        return [];
    }, $rowsObj);

    $previewLen = 120;

    $items = array_map(static function (array $r) use ($previewLen): array {
        $msg = (string)($r['message'] ?? '');

        // Build 1-line preview
        if (function_exists('mb_substr')) {
            $preview = mb_substr($msg, 0, $previewLen);
        } else {
            $preview = substr($msg, 0, $previewLen);
        }

        // collapse whitespace to single spaces (avoid /u to reduce encoding issues)
        $preview = trim(preg_replace('/\s+/', ' ', $preview) ?? $preview);

        $isTruncated = function_exists('mb_strlen')
            ? (mb_strlen($msg) > mb_strlen($preview))
            : (strlen($msg) > strlen($preview));

        if ($isTruncated) $preview .= '…';

        return [
            'id' => (int)($r['id'] ?? 0),
            'name' => (string)($r['name'] ?? ''),
            'wechat' => array_key_exists('wechat', $r) && $r['wechat'] !== null ? (string)$r['wechat'] : null,
            'email' => array_key_exists('email', $r) && $r['email'] !== null ? (string)$r['email'] : null,
            'message' => $msg,                 // full message for modal
            'message_preview' => $preview,      // one-liner for list
            'is_read' => (int)($r['is_read'] ?? 0),
            'created_at' => (string)($r['created_at'] ?? ''),
        ];
    }, $rows);

    api_ok([
        'page' => $page,
        'pageSize' => $pageSize,
        'total' => $total,
        'totalPages' => $totalPages,
        'status' => $status,
        'items' => $items,
    ], 'Messages fetched');
} catch (Throwable $e) {
    api_error('server_error', $e->getMessage(), 500);
}
