<?php
declare(strict_types=1);

/**
 * Minimal RS256 JWT verifier using Clerk JWKS (no Composer).
 * - Fetches and caches JWKS
 * - Verifies signature
 * - Validates iss/exp/nbf
 *
 * Usage:
 *   $claims = clerk_verify_jwt($jwt, 'https://trusted-albacore-0.clerk.accounts.dev');
 */

function base64url_decode_str(string $data): string {
    $remainder = strlen($data) % 4;
    if ($remainder) $data .= str_repeat('=', 4 - $remainder);
    $data = strtr($data, '-_', '+/');
    $out = base64_decode($data, true);
    if ($out === false) throw new RuntimeException('Invalid base64url');
    return $out;
}

function json_decode_assoc(string $json): array {
    $data = json_decode($json, true);
    if (!is_array($data)) throw new RuntimeException('Invalid JSON');
    return $data;
}

function http_get(string $url, int $timeoutSeconds = 3): string {
    $ctx = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => $timeoutSeconds,
            'header' => "Accept: application/json\r\n",
        ],
    ]);
    $body = @file_get_contents($url, false, $ctx);
    if ($body === false) throw new RuntimeException("Failed to fetch: {$url}");
    return $body;
}

function jwks_cache_path(string $issuer): string {
    $safe = preg_replace('/[^a-zA-Z0-9._-]/', '_', $issuer);
    return sys_get_temp_dir() . "/clerk_jwks_cache_{$safe}.json";
}

function fetch_jwks(string $issuer, int $cacheTtlSeconds = 3600): array {
    $jwksUrl = rtrim($issuer, '/') . '/.well-known/jwks.json';
    $cacheFile = jwks_cache_path($issuer);

    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTtlSeconds)) {
        $cached = file_get_contents($cacheFile);
        if ($cached !== false) {
            return json_decode_assoc($cached);
        }
    }

    $jwksJson = http_get($jwksUrl);
    @file_put_contents($cacheFile, $jwksJson);
    return json_decode_assoc($jwksJson);
}

/**
 * ASN.1 / DER helpers to build an RSA public key PEM from JWK (n,e).
 */
function der_len(int $len): string {
    if ($len < 0x80) return chr($len);
    $bin = ltrim(pack('N', $len), "\x00");
    return chr(0x80 | strlen($bin)) . $bin;
}
function der_int(string $bin): string {
    // Ensure positive INTEGER
    if ($bin === '' || (ord($bin[0]) & 0x80)) $bin = "\x00" . $bin;
    return "\x02" . der_len(strlen($bin)) . $bin;
}
function der_seq(string $bin): string {
    return "\x30" . der_len(strlen($bin)) . $bin;
}
function der_bitstr(string $bin): string {
    // 0 unused bits
    return "\x03" . der_len(strlen($bin) + 1) . "\x00" . $bin;
}
function der_oid_rsa_encryption(): string {
    // 1.2.840.113549.1.1.1
    return "\x06\x09\x2A\x86\x48\x86\xF7\x0D\x01\x01\x01";
}
function der_null(): string {
    return "\x05\x00";
}
function jwk_rsa_to_pem(array $jwk): string {
    if (($jwk['kty'] ?? '') !== 'RSA') throw new RuntimeException('Unsupported kty');
    $n = base64url_decode_str((string)($jwk['n'] ?? ''));
    $e = base64url_decode_str((string)($jwk['e'] ?? ''));

    $rsaPubKey = der_seq(
        der_int($n) .
        der_int($e)
    );

    $algoId = der_seq(
        der_oid_rsa_encryption() .
        der_null()
    );

    // SubjectPublicKeyInfo
    $spki = der_seq(
        $algoId .
        der_bitstr($rsaPubKey)
    );

    $pem = "-----BEGIN PUBLIC KEY-----\n" .
        chunk_split(base64_encode($spki), 64, "\n") .
        "-----END PUBLIC KEY-----\n";

    return $pem;
}

function clerk_verify_jwt(string $jwt, string $issuer, ?string $expectedAzp = null): array {
    $parts = explode('.', $jwt);
    if (count($parts) !== 3) throw new RuntimeException('Invalid JWT format');

    [$h64, $p64, $s64] = $parts;

    $header = json_decode_assoc(base64url_decode_str($h64));
    $payload = json_decode_assoc(base64url_decode_str($p64));
    $sig = base64url_decode_str($s64);

    // Basic header checks
    if (($header['typ'] ?? '') !== 'JWT') throw new RuntimeException('Invalid typ');
    if (($header['alg'] ?? '') !== 'RS256') throw new RuntimeException('Invalid alg');
    $kid = $header['kid'] ?? null;
    if (!$kid) throw new RuntimeException('Missing kid');

    // Claim checks
    $iss = $payload['iss'] ?? '';
    if ($iss !== $issuer) throw new RuntimeException('Invalid issuer');

    $now = time();
    $exp = (int)($payload['exp'] ?? 0);
    $nbf = (int)($payload['nbf'] ?? 0);

    if ($exp && $now >= $exp) throw new RuntimeException('Token expired');
    if ($nbf && $now < $nbf) throw new RuntimeException('Token not active yet');

    if ($expectedAzp !== null) {
        $azp = (string)($payload['azp'] ?? '');
        if ($azp !== $expectedAzp) throw new RuntimeException('Invalid azp');
    }

    // Fetch JWKS and find the correct key by kid
    $jwks = fetch_jwks($issuer);
    $keys = $jwks['keys'] ?? null;
    if (!is_array($keys)) throw new RuntimeException('Invalid JWKS');

    $jwk = null;
    foreach ($keys as $k) {
        if (($k['kid'] ?? '') === $kid) { $jwk = $k; break; }
    }
    if (!$jwk) throw new RuntimeException('No matching JWK for kid');

    $pem = jwk_rsa_to_pem($jwk);

    // Verify signature: RS256 means SHA256withRSA on "header.payload"
    $data = $h64 . '.' . $p64;

    $pub = openssl_pkey_get_public($pem);
    if ($pub === false) throw new RuntimeException('Invalid public key');

    $ok = openssl_verify($data, $sig, $pub, OPENSSL_ALGO_SHA256);
    if ($ok !== 1) throw new RuntimeException('Invalid signature');

    return $payload; // trusted claims
}
