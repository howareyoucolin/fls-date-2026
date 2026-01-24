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

// --------------------
// Resolve Post
// --------------------
$post = false;

if (isset($_GET['p']) && (int)$_GET['p'] > 0) {
	// Fallback: allow /?p=123
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
$meta_description = '';
include ROOT_PATH . '/templates/header.php';

// Current URL (for sharing)
$current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
$current_url .= "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
?>

<div class="main">
	<div class="container">
		<div class="content" style="margin-top:20px; max-width:760px; margin-left:auto; margin-right:auto;">

			<h3><?php echo htmlspecialchars($post->post_title); ?></h3>

			<div style="font-size:13px; color:#666; margin-bottom:10px;">
				发表于 <?php echo htmlspecialchars(date('Y-m-d', strtotime($post->post_date))); ?>
			</div>

			<!-- WeChat Share (AddToAny) -->
			<div class="share-wechat-container">
				<div class="a2a_kit a2a_default_style">
					<a
						class="a2a_button_wechat"
						data-a2a-url="<?php echo htmlspecialchars($current_url); ?>"
						data-a2a-title="<?php echo htmlspecialchars($post->post_title); ?>"
					></a>
				</div>
				<div class="share-wechat-hint">点击按钮后，会显示二维码；用微信扫一扫即可发给朋友或分享到朋友圈</div>
			</div>

			<div style="line-height:1.8; font-size:15px; margin-top:20px;">
				<?php
				// WP post_content contains HTML.
				// If you trust your own WP content, echo it raw:
				echo $post->post_content;
				?>
			</div>

			<!-- Optional: bottom share button again -->
			<div class="share-wechat-container" style="margin-top:24px;">
				<div class="a2a_kit a2a_default_style">
					<a
						class="a2a_button_wechat"
						data-a2a-url="<?php echo htmlspecialchars($current_url); ?>"
						data-a2a-title="<?php echo htmlspecialchars($post->post_title); ?>"
					></a>
				</div>
			</div>

		</div>
	</div>
</div>

<!-- AddToAny script -->
<script async src="https://static.addtoany.com/menu/page.js"></script>

<!-- Styles (keep it in this file) -->
<style>
/* WeChat share button wrapper */
.share-wechat-container{
	margin: 10px 0 18px;
}

/* Make the WeChat icon feel like a real button */
.share-wechat-container .a2a_button_wechat{
	display: inline-block;
	width: 44px;
	height: 36px;
	border-radius: 4px;
	background: #07C160;
	position: relative;
	text-decoration: none;
}

/* White WeChat icon (uses AddToAny SVG mask if available; otherwise just shows green button) */
.share-wechat-container .a2a_button_wechat::after{
	content: "微信";
	position: absolute;
	left: 0;
	top: 0;
	width: 100%;
	height: 100%;
	display: flex;
	align-items: center;
	justify-content: center;
	color: #fff;
	font-size: 13px;
	font-weight: 600;
	letter-spacing: 1px;
}

.share-wechat-container .a2a_button_wechat:hover{
	background: #06ad56;
}

.share-wechat-hint{
	margin-top: 8px;
	font-size: 12px;
	color: #777;
	line-height: 1.5;
}
</style>

<?php include ROOT_PATH . '/templates/footer.php'; ?>
