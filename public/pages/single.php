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

// Build current URL for WeChat QR
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

			<!-- WeChat Share -->
			<div class="wechat-share">
				<button class="wechat-share-btn" onclick="toggleWeChatQR()">
					分享到微信
				</button>

				<div id="wechat-qr-box" class="wechat-qr-box">
					<div class="wechat-qr-title">用微信扫一扫分享</div>
					<img
						src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=<?php echo urlencode($current_url); ?>"
						alt="WeChat Share QR"
					/>
				</div>
			</div>

			<div style="line-height:1.8; font-size:15px; margin-top:20px;">
				<?php
				// WP post_content contains HTML.
				// If you trust your own WP content, echo it raw:
				echo $post->post_content;
				?>
			</div>
		</div>

        <button class="wechat-share-btn" onclick="toggleWeChatQR()" style="margin:20px auto; display:block;">
            分享到微信
        </button>
	</div>
</div>

<!-- WeChat Share Script -->
<script>
function toggleWeChatQR() {
	const box = document.getElementById('wechat-qr-box');
	if (!box) return;
	box.style.display = (box.style.display === 'block') ? 'none' : 'block';
}
</script>

<!-- WeChat Share Styles -->
<style>
.wechat-share {
	margin: 10px 0 20px;
	position: relative;
}

.wechat-share-btn {
	background: #07C160; /* WeChat green */
	color: #fff;
	border: none;
	padding: 8px 16px;
	border-radius: 4px;
	font-size: 14px;
	cursor: pointer;
}

.wechat-share-btn:hover {
	background: #06ad56;
}

.wechat-qr-box {
	display: none;
	position: absolute;
	top: 40px;
	left: 0;
	background: #fff;
	border: 1px solid #ddd;
	padding: 12px;
	border-radius: 6px;
	box-shadow: 0 6px 16px rgba(0,0,0,0.15);
	z-index: 10;
	text-align: center;
}

.wechat-qr-title {
	font-size: 13px;
	color: #666;
	margin-bottom: 8px;
}
</style>

<?php include ROOT_PATH . '/templates/footer.php'; ?>
