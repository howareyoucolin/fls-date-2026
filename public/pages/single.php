<?php
// single.php
// Handles URLs like:
// /blog/%e9%95%bf%e5%be%97%e6%bc%82%e4%ba%ae... (percent-encoded Chinese slug)
// Also supports fallback: /?p=123

function get_blog_slug_from_request() {
	$uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
	$path = parse_url($uri, PHP_URL_PATH);
	if (!$path) return '';

	$prefix = '/blog/';
	$pos = strpos($path, $prefix);
	if ($pos === false) return '';

	$slug = substr($path, $pos + strlen($prefix));
	$slug = trim($slug, "/ \t\n\r\0\x0B");
	if ($slug === '') return '';

	// Decode percent-encoding so it matches wp_posts.post_name
	$slug = urldecode($slug);

	// Optional normalization (for odd trailing punctuation)
	$slug = rtrim($slug, "？?");

	return $slug;
}

function get_post_by_slug($slug) {
	global $db;

	$slug = trim($slug);
	if ($slug === '') return false;

	$slug_esc = $db->escape($slug);

	$sql = "
		SELECT ID, post_title, post_date, post_content, post_name
		FROM wp_posts
		WHERE post_status = 'publish'
			AND post_type = 'post'
			AND post_name = '{$slug_esc}'
		LIMIT 1
	";

	return $db->get_row($sql);
}

function get_post_by_id($id) {
	global $db;

	$id = (int)$id;
	if ($id <= 0) return false;

	$sql = "
		SELECT ID, post_title, post_date, post_content, post_name
		FROM wp_posts
		WHERE post_status = 'publish'
			AND post_type = 'post'
			AND ID = {$id}
		LIMIT 1
	";

	return $db->get_row($sql);
}

function get_random_posts($exclude_id, $limit = 4){
	global $db;

	$exclude_id = (int)$exclude_id;
	$limit = (int)$limit;
	if ($limit <= 0) $limit = 4;

	$sql = "
		SELECT ID, post_title, post_date, post_content, post_name
		FROM wp_posts
		WHERE post_status = 'publish'
			AND post_type = 'post'
			AND ID != {$exclude_id}
		ORDER BY RAND()
		LIMIT {$limit}
	";

	return $db->get_results($sql);
}

// --------------------
// Resolve Post
// --------------------
$post = false;

if (isset($_GET['p']) && (int)$_GET['p'] > 0) {
	$post = get_post_by_id((int)$_GET['p']);
} else {
	$slug = get_blog_slug_from_request();
	$post = get_post_by_slug($slug);
}

// --------------------
// 404 Handling
// --------------------
if (!$post) {
	http_response_code(404);
	$meta_title = '文章不存在';
	$meta_description = '';
	include ROOT_PATH . '/templates/header.php';
	?>
	<div class="main">
		<div class="container">
			<div class="content" style="margin-top:20px; max-width:760px; margin-left:auto; margin-right:auto;">
				<h3>文章不存在</h3>
				<div>你访问的文章可能已被删除或链接不正确。</div>
			</div>
		</div>
	</div>
	<?php
	include ROOT_PATH . '/templates/footer.php';
	exit;
}

// --------------------
// SEO
// --------------------
$meta_title = $post->post_title;

// Dynamic meta description from content
$desc_text = trim(html_entity_decode(strip_tags($post->post_content), ENT_QUOTES, 'UTF-8'));
$desc_text = preg_replace('/\s+/u', ' ', $desc_text);
if (mb_strlen($desc_text, 'UTF-8') > 155) {
	$desc_text = mb_substr($desc_text, 0, 155, 'UTF-8') . '...';
}
$meta_description = $desc_text;

include ROOT_PATH . '/templates/header.php';

// Random recommends
$recommends = get_random_posts((int)$post->ID, 4);
?>

<style>
/* Other Recommends section */
.other-recommends{
	margin-top: 50px;
	margin-bottom: 10px;
}
.other-recommends-inner{
	max-width: 760px;
	margin: 0 auto;
}
.other-recommends-title{
	font-size: 28px;
	font-weight: bold;
	color: #000;
	margin: 0 0 14px 0;
}

/* Flat card style like member-card */
.recommend-grid{
	display: block;
}

.recommend-card{
	background: #fff;
	border: 1px solid #e0e0e0;
	overflow: hidden;

	width: 48%;
	float: left;
	margin-right: 4%;
	margin-bottom: 20px;
}

.recommend-card:nth-child(2n){
	margin-right: 0;
}

.recommend-card-inner{
	padding: 15px;
}

.recommend-title{
	font-size: 16px;
	font-weight: bold;
	margin: 0 0 8px 0;
	line-height: 1.4;
}

.recommend-title a{
	color: #000;
	text-decoration: none;
}

.recommend-title a:hover{
	color: #D72171;
}

.recommend-meta{
	font-size: 13px;
	color: #666;
	margin-bottom: 8px;
}

.recommend-excerpt{
	margin: 0;
	font-size: 13px;
	color: #555;
	line-height: 1.6;
}

@media only screen and (max-width: 680px){
	.recommend-card{
		width: 100%;
		float: none;
		margin-right: 0;
	}
}
</style>

<div class="main">
	<div class="container">

        <div style="max-width:760px; margin-left:auto; margin-right:auto;">
            <nav class="breadcrumb">
                <a href="<?php echo SITE_URL; ?>" class="breadcrumb-item">首页</a>
                <span class="breadcrumb-separator">›</span>
                <a href="<?php echo SITE_URL; ?>/blog" class="breadcrumb-item">博客</a>
                <span class="breadcrumb-separator">›</span>
                <span class="breadcrumb-current">
                    <?php echo htmlspecialchars($post->post_title); ?>
                </span>
            </nav>
        </div>

		<!-- Main post -->
		<div class="content" style="margin-top:20px; max-width:760px; margin-left:auto; margin-right:auto;">
            <h1 style="font-size:28px; font-weight:700; line-height:1.3; margin-bottom:10px;">
                <?php echo htmlspecialchars($post->post_title); ?>
            </h1>

			<div style="font-size:13px; color:#666; margin-bottom:18px;">
				发表于 <?php echo htmlspecialchars(date('Y-m-d', strtotime($post->post_date))); ?>
			</div>

			<div style="line-height:1.8; font-size:15px;">
				<?php echo $post->post_content; ?>
			</div>
		</div>

		<!-- Other Recommends -->
		<?php if (!empty($recommends)): ?>
			<div class="other-recommends">
				<div class="other-recommends-inner">
                    <h2 class="other-recommends-title">相关推荐</h2>

					<div class="recommend-grid">
						<?php foreach ($recommends as $rp): ?>
							<?php
								$rp_title = $rp->post_title ?: '（无标题）';
								$rp_date  = date('Y-m-d', strtotime($rp->post_date));
								$rp_excerpt = wp_excerpt($rp->post_content, 90);

								$rp_slug = isset($rp->post_name) ? trim($rp->post_name) : '';
								$rp_url = SITE_URL . '/blog/' . rawurlencode($rp_slug);
							?>
							<article class="recommend-card">
								<div class="recommend-card-inner">
									<h3 class="recommend-title">
										<a href="<?php echo htmlspecialchars($rp_url); ?>">
											<?php echo htmlspecialchars($rp_title); ?>
										</a>
									</h3>
									<div class="recommend-meta"><?php echo htmlspecialchars($rp_date); ?></div>
									<p class="recommend-excerpt"><?php echo htmlspecialchars($rp_excerpt); ?></p>
								</div>
							</article>
						<?php endforeach; ?>
						<div class="clear"></div>
					</div>

				</div>
			</div>
		<?php endif; ?>

	</div>
</div>

<?php include ROOT_PATH . '/templates/footer.php'; ?>
