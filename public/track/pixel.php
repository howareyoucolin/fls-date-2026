<?php
// /track/pixel.php
// Logs a page impression to Redis (Upstash REST) then returns a 1x1 transparent GIF.

require_once(__DIR__ . '/includes.php');

// ----------------------------
// Visitor cookie (identify same client)
// ----------------------------
$COOKIE_NAME = 'fd_vid';
$COOKIE_TTL  = 60 * 60 * 24 * 365; // 1 year

if (empty($_COOKIE[$COOKIE_NAME])) {
    // Generate a short, random visitor id
    $visitorId = bin2hex(random_bytes(8)); // 16 chars
    pixel_log('New visitor, visitorId ' . $visitorId . ' generated');
    setcookie(
        $COOKIE_NAME,
        $visitorId,
        time() + $COOKIE_TTL,
        '/',        // path
        '',         // domain (current)
        false,      // secure (set true if HTTPS-only)
        true        // httponly
    );
} else {
    $visitorId = $_COOKIE[$COOKIE_NAME];
    pixel_log('Returning existing visitor, visitorId ' . $visitorId . ' found in cookie');
}

pixel_log('pixel received');

// ----------------------------
// 1) Config (from config.php)
// ----------------------------
$UPSTASH_REDIS_REST_URL = defined('UPSTASH_REDIS_REST_URL') ? UPSTASH_REDIS_REST_URL : '';
$UPSTASH_REDIS_REST_TOKEN = defined('UPSTASH_REDIS_REST_TOKEN') ? UPSTASH_REDIS_REST_TOKEN : '';

$LIST_KEY = 'impressions'; 
$LIST_TTL_SECONDS = 604800; // 1 week

// ----------------------------
// 2) Build event payload
// ----------------------------
$event = [
  'uri' => $_GET['path'] ?? '',
  'ip'  => $_SERVER['REMOTE_ADDR'] ?? '',
  'ua'  => $_SERVER['HTTP_USER_AGENT'] ?? '',
  'ts'  => time(),
  'vid' => $visitorId,
  'ref' => $_SERVER['HTTP_REFERER'] ?? '',
];

$payload = json_encode($event, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
pixel_log($payload);

// ----------------------------
// 3) Push to Upstash (LPUSH + EXPIRE)
// ----------------------------
if ($payload !== false) {
  // LPUSH fd:impressions "<json>"
  upstash_post(
    $UPSTASH_REDIS_REST_URL,
    $UPSTASH_REDIS_REST_TOKEN,
    '/LPUSH/' . rawurlencode($LIST_KEY) . '/' . rawurlencode($payload)
  );

  // Optional: set TTL each time (simple + fine for v1)
  if ($LIST_TTL_SECONDS > 0) {
    upstash_post(
      $UPSTASH_REDIS_REST_URL,
      $UPSTASH_REDIS_REST_TOKEN,
      '/EXPIRE/' . rawurlencode($LIST_KEY) . '/' . (int) $LIST_TTL_SECONDS
    );
  }
}

pixel_log('pixel sent');

// ----------------------------
// 4) Return 1x1 transparent GIF
// ----------------------------
header("Content-Type: image/gif");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

// 1x1 transparent GIF bytes
echo base64_decode("R0lGODlhAQABAPAAAAAAAAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==");
exit;
