<?php
// public/pages/sitemap.php
// URL: /sitemap.xml

header('Content-Type: application/xml; charset=UTF-8');

$site = rtrim(SITE_URL, '/');
global $db;

// helpers
function esc_xml($s){
    return htmlspecialchars($s, ENT_QUOTES | ENT_XML1, 'UTF-8');
}
function mysql_to_iso8601($s){
    // expects 'YYYY-mm-dd HH:ii:ss'
    if (!$s || $s === '0000-00-00 00:00:00') return '';
    return str_replace(' ', 'T', $s) . 'Z';
}

$urls = [];

// static pages
$urls[] = ['loc' => $site . '/',         'lastmod' => '', 'changefreq' => 'daily',  'priority' => '1.0'];
$urls[] = ['loc' => $site . '/members', 'lastmod' => '', 'changefreq' => 'daily',  'priority' => '0.9'];
$urls[] = ['loc' => $site . '/blog',    'lastmod' => '', 'changefreq' => 'weekly', 'priority' => '0.8'];

// members (ONLY approved)
$members = $db->get_results("
    SELECT id, updated_at
    FROM cz_members
    WHERE is_approved = 1
    ORDER BY updated_at DESC
");
foreach ($members as $m) {
    $urls[] = [
        'loc' => $site . '/member/' . (int)$m->id,
        'lastmod' => mysql_to_iso8601($m->updated_at),
        'changefreq' => 'weekly',
        'priority' => '0.8',
    ];
}

// blog posts
$posts = $db->get_results("
    SELECT post_name, post_modified_gmt
    FROM wp_posts
    WHERE post_status='publish' AND post_type='post'
    ORDER BY post_modified_gmt DESC
");
foreach ($posts as $p) {
    $slug = (string)$p->post_name;
    $slugEnc = rawurlencode($slug);
    $urls[] = [
        'loc' => $site . '/blog/' . $slugEnc,
        'lastmod' => mysql_to_iso8601($p->post_modified_gmt),
        'changefreq' => 'monthly',
        'priority' => '0.7',
    ];
}

// build xml
$out = [];
$out[] = '<?xml version="1.0" encoding="UTF-8"?>';
$out[] = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

foreach ($urls as $u) {
    $out[] = '  <url>';
    $out[] = '    <loc>' . esc_xml($u['loc']) . '</loc>';
    if (!empty($u['lastmod']))     $out[] = '    <lastmod>' . esc_xml($u['lastmod']) . '</lastmod>';
    if (!empty($u['changefreq'])) $out[] = '    <changefreq>' . esc_xml($u['changefreq']) . '</changefreq>';
    if (!empty($u['priority']))   $out[] = '    <priority>' . esc_xml($u['priority']) . '</priority>';
    $out[] = '  </url>';
}

$out[] = '</urlset>';
echo implode("\n", $out) . "\n";
exit;
