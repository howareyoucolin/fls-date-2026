<?php
// blog.php - Posts listing page with pagination (modeled after members.php)

$per_page = 10;
$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($current_page - 1) * $per_page;

// Helpers (avoid redeclare)
if (!function_exists('wp_excerpt')) {
	function wp_excerpt($html, $maxLen = 160){
		$text = trim(html_entity_decode(strip_tags($html), ENT_QUOTES, 'UTF-8'));
		$text = preg_replace('/\s+/u', ' ', $text);

		if (mb_strlen($text, 'UTF-8') <= $maxLen) return $text;
		return mb_substr($text, 0, $maxLen, 'UTF-8') . '...';
	}
}

/**
 * Count published posts
 */
function get_posts_count() {
	global $db;
	return (int)$db->get_var("
		SELECT COUNT(*)
		FROM wp_posts
		WHERE post_status = 'publish'
		  AND post_type = 'post'
	");
}

/**
 * Get posts list
 */
function get_posts($limit = 10, $offset = 0) {
	global $db;

	$limit = (int)$limit;
	$offset = (int)$offset;

	return $db->get_results("
		SELECT ID, post_title, post_date, post_content, post_name
		FROM wp_posts
		WHERE post_status = 'publish'
		  AND post_type = 'post'
		ORDER BY post_date DESC
		LIMIT {$limit} OFFSET {$offset}
	");
}

$total_posts = get_posts_count();
$total_pages = (int)ceil($total_posts / $per_page);
$posts = get_posts($per_page, $offset);

$meta_title = '博客 - 纽约同城交友';
$meta_description = '浏览最新文章，分享纽约相亲交友经验与技巧。';
$meta_keywords = '纽约相亲, 法拉盛相亲, 纽约交友, 博客, 约会技巧';

include ROOT_PATH . '/templates/header.php';
?>

<div class="container">
	<div class="members-section">
		<div class="section-header">
			<h2 class="section-title">博客</h2>
			<?php if ($total_posts > 0): ?>
				<span class="member-count">共 <?php echo $total_posts; ?> 篇文章</span>
			<?php endif; ?>
		</div>

		<?php if (empty($posts)): ?>
			<div class="empty-state">
				<p>暂无文章</p>
			</div>
		<?php else: ?>
			<div class="members-grid">
				<?php foreach ($posts as $post): ?>
					<?php
						$post_id = (int)$post->ID;
						$title   = $post->post_title ?: '（无标题）';
						$date    = date('Y-m-d', strtotime($post->post_date));
						$excerpt = wp_excerpt($post->post_content, 150);

						$slug = isset($post->post_name) ? trim($post->post_name) : '';
						$url  = SITE_URL . '/blog/' . rawurlencode($slug);
					?>
					<article class="member-card">
						<!-- Use same flat layout as member card, but no image -->
						<div class="member-card-content" style="margin-left:0;">
							<h3 class="member-name">
								<a href="<?php echo htmlspecialchars($url); ?>">
									<?php echo htmlspecialchars($title); ?>
								</a>
							</h3>
							<div class="member-info">
								<span class="age">发表于 <?php echo htmlspecialchars($date); ?></span>
							</div>

							<?php if (!empty($excerpt)): ?>
								<p class="member-intro">
									<?php echo htmlspecialchars($excerpt); ?>
								</p>
							<?php endif; ?>

							<div class="clear"></div>
						</div>
					</article>
				<?php endforeach; ?>
				<div class="clear"></div>
			</div>

			<?php if ($total_pages > 1): ?>
				<div class="pagination">
					<?php if ($current_page > 1): ?>
						<?php if ($current_page == 2): ?>
							<a href="<?php echo SITE_URL; ?>/blog" class="pagination-link prev">上一页</a>
						<?php else: ?>
							<a href="<?php echo SITE_URL; ?>/blog/page/<?php echo $current_page - 1; ?>" class="pagination-link prev">上一页</a>
						<?php endif; ?>
					<?php endif; ?>

					<?php
					$start_page = max(1, $current_page - 2);
					$end_page = min($total_pages, $current_page + 2);

					if ($start_page > 1): ?>
						<a href="<?php echo SITE_URL; ?>/blog" class="pagination-link">1</a>
						<?php if ($start_page > 2): ?>
							<span class="pagination-ellipsis">...</span>
						<?php endif; ?>
					<?php endif; ?>

					<?php for ($i = $start_page; $i <= $end_page; $i++): ?>
						<?php if ($i == $current_page): ?>
							<span class="pagination-link current"><?php echo $i; ?></span>
						<?php elseif ($i == 1): ?>
							<a href="<?php echo SITE_URL; ?>/blog" class="pagination-link"><?php echo $i; ?></a>
						<?php else: ?>
							<a href="<?php echo SITE_URL; ?>/blog/page/<?php echo $i; ?>" class="pagination-link"><?php echo $i; ?></a>
						<?php endif; ?>
					<?php endfor; ?>

					<?php if ($end_page < $total_pages): ?>
						<?php if ($end_page < $total_pages - 1): ?>
							<span class="pagination-ellipsis">...</span>
						<?php endif; ?>
						<a href="<?php echo SITE_URL; ?>/blog/page/<?php echo $total_pages; ?>" class="pagination-link"><?php echo $total_pages; ?></a>
					<?php endif; ?>

					<?php if ($current_page < $total_pages): ?>
						<a href="<?php echo SITE_URL; ?>/blog/page/<?php echo $current_page + 1; ?>" class="pagination-link next">下一页</a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		<?php endif; ?>
	</div>
</div>

<?php include ROOT_PATH . '/templates/footer.php'; ?>
