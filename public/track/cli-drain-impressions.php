<?php
// /track/cli-drain-impressions.php
// Drains page impressions from Redis (Upstash REST). CLI only.
// Aggregates per-uri totals and upserts into cz_page_views:
// - impressions = total events
// - visits = unique vids per run (assumes job runs daily)

if (PHP_SAPI !== 'cli') {
    exit("This script can only be run via CLI.\n");
}

require_once(__DIR__ . '/includes.php');

pixel_log('cli drain impressions: start');

// Ensure DB exists (index.php should have bootstrapped $db)
$db = $GLOBALS['db'] ?? null;
if (!$db) {
    pixel_log('cli drain impressions: missing $db');
    fwrite(STDERR, "DB not initialized. Run via index.php so DB bootstrap happens.\n");
    exit(1);
}

// Create table if it doesn't exist
$db->query("
    CREATE TABLE IF NOT EXISTS cz_page_views (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        uri VARCHAR(1024) NOT NULL,
        visits INT UNSIGNED NOT NULL DEFAULT 0,
        impressions INT UNSIGNED NOT NULL DEFAULT 0,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uniq_uri (uri(255))
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

// ----------------------------
// Config (from config.php)
// ----------------------------
$UPSTASH_REDIS_REST_URL   = defined('UPSTASH_REDIS_REST_URL') ? UPSTASH_REDIS_REST_URL : '';
$UPSTASH_REDIS_REST_TOKEN = defined('UPSTASH_REDIS_REST_TOKEN') ? UPSTASH_REDIS_REST_TOKEN : '';

$LIST_KEY = 'impressions';

if ($UPSTASH_REDIS_REST_URL === '' || $UPSTASH_REDIS_REST_TOKEN === '') {
    pixel_log('cli drain impressions: missing UPSTASH config');
    fwrite(STDERR, "Missing UPSTASH_REDIS_REST_URL / UPSTASH_REDIS_REST_TOKEN in config.php\n");
    exit(1);
}

// ----------------------------
// 1) LRANGE impressions
// ----------------------------
echo "Reading Redis list '{$LIST_KEY}'...\n";

$res = upstash_get_json(
    $UPSTASH_REDIS_REST_URL,
    $UPSTASH_REDIS_REST_TOKEN,
    '/LRANGE/' . rawurlencode($LIST_KEY) . '/0/-1'
);

if ($res['code'] !== 200) {
    pixel_log('cli drain impressions: LRANGE failed code=' . $res['code'] . ' err=' . $res['err']);
    fwrite(STDERR, "LRANGE failed. HTTP {$res['code']} {$res['err']}\n");
    exit(1);
}

$decoded = json_decode((string)$res['resp'], true);
$rows = $decoded['result'] ?? [];
if (!is_array($rows)) $rows = [];

echo "Fetched " . count($rows) . " raw item(s)\n";

// Decode each JSON row
$events = [];
foreach ($rows as $row) {
    $e = json_decode((string)$row, true);
    if (is_array($e)) $events[] = $e;
}

echo "Decoded " . count($events) . " event(s)\n";

// ----------------------------
// 2) Aggregate + Upsert into DB
// ----------------------------
$stats = []; // uri => ['impressions'=>int, 'vids'=>set, 'anon_visits'=>int]

foreach ($events as $e) {
    $uri = trim((string)($e['uri'] ?? ''));
    $vid = trim((string)($e['vid'] ?? ''));

    if ($uri === '') continue;

    if (!isset($stats[$uri])) {
        $stats[$uri] = [
            'impressions' => 0,
            'vids' => [],
            'anon_visits' => 0,
        ];
    }

    $stats[$uri]['impressions']++;

    if ($vid !== '') {
        $stats[$uri]['vids'][$vid] = true; // dedupe known vids
    } else {
        $stats[$uri]['anon_visits']++;     // count empty vid as 1 unique visit
    }
}

$totalImpressions = 0;
$totalVisits = 0;
$pagesUpdated = 0;

foreach ($stats as $uri => $row) {
    $impressions = (int)$row['impressions'];
    $visits = (int)count($row['vids']) + (int)$row['anon_visits'];

    $totalImpressions += $impressions;
    $totalVisits += $visits;

    $uriEsc = $db->escape($uri);

    $db->query("
        INSERT INTO cz_page_views (uri, visits, impressions)
        VALUES ('{$uriEsc}', {$visits}, {$impressions})
        ON DUPLICATE KEY UPDATE
            visits = visits + VALUES(visits),
            impressions = impressions + VALUES(impressions),
            updated_at = CURRENT_TIMESTAMP
    ");

    $pagesUpdated++;
}

echo "DB updated: pages={$pagesUpdated}, visits={$totalVisits}, impressions={$totalImpressions}\n";
pixel_log("cli drain impressions: db pages={$pagesUpdated} visits={$totalVisits} impressions={$totalImpressions}");

// ----------------------------
// 3) Delete list (after successful DB update)
// ----------------------------
if (count($rows) > 0) {
    upstash_post(
        $UPSTASH_REDIS_REST_URL,
        $UPSTASH_REDIS_REST_TOKEN,
        '/DEL/' . rawurlencode($LIST_KEY)
    );

    pixel_log('cli drain impressions: DEL sent');
    echo "Deleted Redis key '{$LIST_KEY}'\n";
} else {
    echo "No rows to delete.\n";
}

// ----------------------------
// 4) Done
// ----------------------------
echo "Done.\n";
pixel_log('cli drain impressions: end');
exit(0);
