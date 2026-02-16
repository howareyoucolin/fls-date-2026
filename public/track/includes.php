<?php

/**
 * Simple pixel logger
 * - Appends "[timestamp] message"
 * - Keeps only the latest 200 lines
 */
function pixel_log(string $message) {
    $file = __DIR__ . '/pixel.log';
    $maxLines = 1000;

    $line = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;

    // Append new line
    file_put_contents($file, $line, FILE_APPEND | LOCK_EX);

    // Trim old lines if file grows too large
    $lines = @file($file, FILE_IGNORE_NEW_LINES);
    if ($lines !== false && count($lines) > $maxLines) {
        $lines = array_slice($lines, -$maxLines);
        file_put_contents($file, implode(PHP_EOL, $lines) . PHP_EOL, LOCK_EX);
    }
}

/**
 * Summary of upstash_post
 * @param string $baseUrl
 * @param string $token
 * @param string $path
 * @return void
 */
function upstash_post(string $baseUrl, string $token, string $path) {
    $url = rtrim($baseUrl, '/') . $path;
  
    $ch = curl_init($url);
    curl_setopt_array($ch, [
      CURLOPT_POST => true,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $token,
      ],
      CURLOPT_TIMEOUT => 2,
    ]);
  
    curl_exec($ch);
    curl_close($ch);
}

// ----------------------------
// Helpers (Upstash REST read)
// ----------------------------
function upstash_get_json(string $baseUrl, string $token, string $path): array
{
    $url = rtrim($baseUrl, '/') . $path;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $token,
        ],
        CURLOPT_TIMEOUT => 5,
    ]);

    $resp = curl_exec($ch);
    $err  = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'code' => $code,
        'resp' => $resp,
        'err'  => $err,
    ];
}