<?php
// single.php
// Handles URLs like:
// /blog/%e9%95%bf%e5%be%97%e6%bc%82%e4%ba%ae...
// Also supports fallback: /?p=123

function get_blog_slug_from_request() {
	$uri = $_SERVER['REQUEST_URI'] ?? '';
	$path = parse_url($uri, PHP_URL_PATH);
	if (!$path) return '';

	$prefix = '/blog/';
	$pos = strpos($path, $prefix);
	if ($pos === false) return '';

	$slug = trim(substr($path, $pos + strlen($prefix)), "/");
	if ($slug === '') return '';

	return rtrim(urldecode($slug), "？?");
}

function get_post_by_slug($slug) {
	global $db;
	if ($slug === '') return false;
	$slug_esc = $db->escape($slug);

	return $db->get_row("
		SELECT ID, post_title, post_date, post_content, post_name
		FROM wp_posts
		WHERE post_status='publish'
		AND post_type='post'
		AND post_name='{$slug_esc}'
		LIMIT 1
	");
}

function get_post_by_id($id) {
	global $db;
	$id = (int)$id;
	if ($id <= 0) return false;

	return $db->get_row("
		SELECT ID, post_title, post_date, post_content, post_name
		FROM wp_posts
		WHERE post_status='publish'
		AND post_type='post'
		AND ID={$id}
		LIMIT 1
	");
}

function get_random_posts($exclude_id, $limit = 4){
	global $db;
	$exclude_id = (int)$exclude_id;
	$limit = (int)$limit;

	return $db->get_results("
		SELECT ID, post_title, post_date, post_content, post_name
		FROM wp_posts
		WHERE post_status='publish'
		AND post_type='post'
		AND ID != {$exclude_id}
		ORDER BY RAND()
		LIMIT {$limit}
	");
}

/* ---------- Resolve post ---------- */
$post = isset($_GET['p'])
	? get_post_by_id((int)$_GET['p'])
	: get_post_by_slug(get_blog_slug_from_request());

/* ---------- 404 ---------- */
if (!$post) {
	http_response_code(404);
	$meta_title = '文章不存在';
	$meta_description = '';
	include ROOT_PATH . '/templates/header.php';
	echo '<div class="container"><h3>文章不存在</h3></div>';
	include ROOT_PATH . '/templates/footer.php';
	exit;
}

/* ---------- SEO ---------- */
$meta_title = '纽约同城交友 - ' . $post->post_title;
$desc = trim(strip_tags(html_entity_decode($post->post_content)));
$desc = preg_replace('/\s+/u', ' ', $desc);
$meta_description = '纽约同城交友 - ' . mb_substr($desc, 0, 155);

include ROOT_PATH . '/templates/header.php';
$recommends = get_random_posts($post->ID, 4);
?>

<style>
/* =========================
   Blog Post Content ONLY
   ========================= */
.post-content{
	font-size:15px;
	line-height:1.9;
	color:#222;
}

.post-content p{
	margin:18px 0;
}

.post-content h3{
	margin:40px 0 16px;
	font-size:20px;
	font-weight:700;
	line-height:1.4;
	position:relative;
	padding-left:12px;
	color:#000;
}

.post-content h3::before{
	content:"";
	position:absolute;
	left:0;
	top:4px;
	width:4px;
	height:70%;
	background:#D72171;
	border-radius:2px;
}

.post-content hr{
	border:none;
	height:1px;
	margin:44px 0;
	background:linear-gradient(
		to right,
		transparent,
		#c7c7c7,
		transparent
	);
}

.post-content strong{
	font-weight:600;
	color:#000;
	background:linear-gradient(
		transparent 62%,
		rgba(215,33,113,0.14) 0
	);
	padding:0 2px;
	border-radius:2px;
}

.post-content ul,
.post-content ol{
	list-style:none;
	padding-left:0;
	margin:20px 0;
}

.post-content li{
	position:relative;
	padding-left:22px;
	margin:10px 0;
	line-height:1.85;
	color:#222;
}

.post-content ul li::before{
	content:"•";
	position:absolute;
	left:0;
	top:0;
	color:#D72171;
	font-size:18px;
	line-height:1.2;
}

.post-content blockquote{
	margin:22px 0;
	padding:14px 16px;
	background:#fafafa;
	border-left:4px solid rgba(215,33,113,0.45);
	color:#333;
}

/* =========================
   Other Recommends (ORIGINAL)
   ========================= */
.other-recommends{
	margin-top:50px;
	margin-bottom:10px;
}
.other-recommends-inner{
	max-width:760px;
	margin:0 auto;
}
.other-recommends-title{
	font-size:28px;
	font-weight:bold;
	color:#000;
	margin:0 0 14px 0;
}

.recommend-grid{display:block;}

.recommend-card{
	background:#fff;
	border:1px solid #e0e0e0;
	overflow:hidden;
	width:48%;
	float:left;
	margin-right:4%;
	margin-bottom:20px;
}
.recommend-card:nth-child(2n){margin-right:0;}
.recommend-card-inner{padding:15px;}

.recommend-title{
	font-size:16px;
	font-weight:bold;
	margin:0 0 8px 0;
	line-height:1.4;
}
.recommend-title a{
	color:#000;
	text-decoration:none;
}
.recommend-title a:hover{color:#D72171;}

.recommend-meta{
	font-size:13px;
	color:#666;
	margin-bottom:8px;
}
.recommend-excerpt{
	margin:0;
	font-size:13px;
	color:#555;
	line-height:1.6;
}

@media(max-width:680px){
	.recommend-card{
		width:100%;
		float:none;
		margin-right:0;
	}
}
</style>

<div class="main">
<div class="container">

<div style="max-width:760px; margin-left:auto; margin-right:auto; margin-bottom:20px;">
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

	<div class="content" style="margin-top:20px;max-width:760px;margin:auto;">
		<h1 style="font-size:28px;font-weight:700;line-height:1.3;margin-bottom:10px;">
			<?=htmlspecialchars($post->post_title)?>
		</h1>

		<div style="font-size:13px;color:#666;margin-bottom:18px;">
			发表于 <?=date('Y-m-d', strtotime($post->post_date))?>
		</div>

		<!-- ONLY styled area -->
		<div class="post-content">
			<?=$post->post_content?>
		</div>
	</div>

	<?php if ($recommends): ?>
	<div class="other-recommends">
		<div class="other-recommends-inner">
			<h2 class="other-recommends-title">相关推荐</h2>

			<div class="recommend-grid">
			<?php foreach ($recommends as $rp): ?>
				<article class="recommend-card">
					<div class="recommend-card-inner">
						<h3 class="recommend-title">
							<a href="<?=SITE_URL.'/blog/'.rawurlencode($rp->post_name)?>">
								<?=htmlspecialchars($rp->post_title)?>
							</a>
						</h3>
						<div class="recommend-meta">
							<?=date('Y-m-d', strtotime($rp->post_date))?>
						</div>
						<p class="recommend-excerpt">
							<?=htmlspecialchars(wp_excerpt($rp->post_content, 90))?>
						</p>
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
