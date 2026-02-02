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

    $page = max(1, (int)($_GET['page'] ?? 1));

    // ✅ Default to 10 per page
    $pageSize = (int)($_GET['pageSize'] ?? 10);
    if ($pageSize <= 0) $pageSize = 10;
    if ($pageSize > 100) $pageSize = 100;

    // all | active | inactive
    // (still useful even in archive view, if you want "approved/unapproved" filtering)
    $status = strtolower(trim((string)($_GET['status'] ?? 'all')));
    if (!in_array($status, ['all', 'active', 'inactive'], true)) {
        $status = 'all';
    }

    // --- WHERE builder ---
    // Always include archived
    $whereParts = ['is_archived = 1'];

    if ($status === 'active') {
        $whereParts[] = 'is_approved = 1';
    } elseif ($status === 'inactive') {
        $whereParts[] = 'is_approved = 0';
    }

    $where = 'WHERE ' . implode(' AND ', $whereParts);

    $offset = ($page - 1) * $pageSize;

    $total = (int)$db->get_var("
        SELECT COUNT(*)
        FROM cz_members
        $where
    ");

    $totalPages = (int)ceil($total / $pageSize);
    if ($totalPages < 1) $totalPages = 1;

    // clamp page if out of range
    if ($page > $totalPages) {
        $page = $totalPages;
        $offset = ($page - 1) * $pageSize;
    }

    // Includes fields needed for card UI
    $rows = $db->get_results("
        SELECT
            id,
            title,
            gender,
            birthday,

            description,
            profile_image,
            profile_thumbnail,

            wechat,
            phone,
            email,

            is_approved,
            is_archived,
            created_at,
            updated_at
        FROM cz_members
        $where
        ORDER BY updated_at DESC, created_at DESC, id DESC
        LIMIT {$pageSize} OFFSET {$offset}
    ");

    $items = [];
    if (is_array($rows)) {
        foreach ($rows as $r) {
            $items[] = [
                'id' => (int)($r->id ?? 0),
                'title' => (string)($r->title ?? ''),
                'gender' => $r->gender === null ? null : (string)$r->gender,
                'birthday' => $r->birthday === null ? null : (string)$r->birthday,

                'description' => $r->description === null ? null : (string)$r->description,
                'profile_image' => $r->profile_image === null ? null : (string)$r->profile_image,
                'profile_thumbnail' => $r->profile_thumbnail === null ? null : (string)$r->profile_thumbnail,

                'wechat' => $r->wechat === null ? null : (string)$r->wechat,
                'phone' => $r->phone === null ? null : (string)$r->phone,
                'email' => $r->email === null ? null : (string)$r->email,

                'is_approved' => (int)($r->is_approved ?? 0),
                'is_archived' => (int)($r->is_archived ?? 1),
                'created_at' => (string)($r->created_at ?? ''),
                'updated_at' => (string)($r->updated_at ?? ''),
            ];
        }
    }

    api_ok([
        'page' => $page,
        'pageSize' => $pageSize,
        'status' => $status,
        'total' => $total,
        'totalPages' => $totalPages,
        'items' => $items,
    ], 'Archived members fetched');
} catch (Throwable $e) {
    api_error('server_error', $e->getMessage(), 500);
}
