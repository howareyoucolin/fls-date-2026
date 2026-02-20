<?php
// Stats page - Page views statistics

$per_page = 100;
$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($current_page - 1) * $per_page;

// URI to Chinese name mapping
$uri_map = [
	'/' => '首页',
	'/home' => '首页',
	'/members' => '会员列表',
	'/member' => '会员详情',
	'/blog' => '博客',
	'/signup' => '注册',
	'/contacts' => '联系我们',
	'/search' => '搜索',
	'/sitemap' => '网站地图',
	'/api' => 'API',
];

// Function to create readable display name for URI
function get_readable_uri_name($uri) {
	global $uri_map, $db;
	
	// Check for exact match in map
	if (isset($uri_map[$uri])) {
		return $uri_map[$uri];
	}
	
	// Check for pattern matches (e.g., /member/123, /blog/xxx)
	foreach ($uri_map as $pattern => $name) {
		if ($pattern !== '/' && strpos($uri, $pattern) === 0) {
			// Extract the ID or slug part
			$suffix = substr($uri, strlen($pattern));
			$suffix = ltrim($suffix, '/');
			
			if ($suffix) {
				// Special handling for member pages
				if ($pattern === '/member') {
					$member_id = (int)$suffix;
					$member = get_member($member_id);
					
					if ($member) {
						$age = calculate_age($member->birthday);
						return $name . ' - ' . htmlspecialchars($member->title) . ($age ? " ($age)" : '');
					}
					
					return $name . ' - ID: ' . $member_id;
				}
				
				// Special handling for blog posts
				if ($pattern === '/blog') {
					// URL decode the slug
					$post_slug = urldecode($suffix);
					
					// Try to find the post in database
					$post = $db->get_row("
						SELECT post_title, post_name
						FROM wp_posts
						WHERE post_status = 'publish'
							AND post_type = 'post'
							AND post_name = '" . $db->escape($post_slug) . "'
						LIMIT 1
					");
					
					if ($post) {
						return $name . ' - ' . htmlspecialchars($post->post_title);
					}
				}
				
				// URL decode it
				$suffix = urldecode($suffix);
				
				// Truncate if too long
				if (strlen($suffix) > 40) {
					$suffix = substr($suffix, 0, 40) . '...';
				}
				
				return $name . ' - ' . $suffix;
			}
			
			return $name;
		}
	}
	
	// Fallback: URL decode and truncate
	$name = ltrim($uri, '/');
	$name = urldecode($name);
	
	if (strlen($name) > 50) {
		$name = substr($name, 0, 50) . '...';
	}
	
	return $name ?: 'Root';
}

// Get total count
$total_count = (int)$db->get_var("SELECT COUNT(*) as count FROM cz_page_views");
$total_pages = ceil($total_count / $per_page);

// Get page views sorted by impressions (descending)
$page_views = $db->get_results("
	SELECT * FROM cz_page_views 
	ORDER BY impressions DESC 
	LIMIT " . (int)$per_page . " OFFSET " . (int)$offset
);

$meta_title = '纽约华人交友网页面浏览统计 - 页面访问数据分析';
$meta_description = '查看纽约华人交友平台的页面浏览统计和访问数据。了解用户最喜欢的页面、会员详情、博客文章等内容的访问情况，帮助我们不断改进服务。';
$meta_keywords = '页面浏览统计, 访问数据, 网站分析, 纽约交友, 法拉盛交友, 页面访问量';

include ROOT_PATH . '/templates/header.php';
?>

<style>
.stats-section {
	max-width: 1200px;
	margin: 20px auto;
	padding: 20px;
}

.section-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 30px;
	padding-bottom: 15px;
	border-bottom: 2px solid #e0e0e0;
}

.section-title {
	font-size: 28px;
	font-weight: 600;
	color: #333;
	margin: 0;
}

.stats-count {
	font-size: 14px;
	color: #666;
}

.stats-table {
	width: 100%;
	border-collapse: collapse;
	background: white;
	box-shadow: 0 1px 3px rgba(0,0,0,0.1);
	border-radius: 4px;
	overflow: hidden;
}

.stats-table thead {
	background-color: #f8f9fa;
	font-weight: 600;
	color: #333;
}

.stats-table th {
	padding: 15px;
	text-align: left;
	border-bottom: 2px solid #e0e0e0;
	font-size: 14px;
}

.stats-table td {
	padding: 12px 15px;
	border-bottom: 1px solid #f0f0f0;
	font-size: 14px;
}

.stats-table tbody tr:hover {
	background-color: #f9f9f9;
}

.stats-table a {
	color: #333;
	text-decoration: none;
}

.stats-table a:hover {
	text-decoration: none;
	color: #dc3545;
}

.empty-state {
	text-align: center;
	padding: 40px;
	color: #999;
	font-size: 16px;
}

.pagination {
	margin-top: 30px;
	text-align: center;
	display: flex;
	justify-content: center;
	align-items: center;
	gap: 5px;
	flex-wrap: wrap;
}

.pagination a, .pagination span {
	padding: 8px 12px;
	border: 1px solid #ddd;
	border-radius: 4px;
	text-decoration: none;
	font-size: 14px;
	background: white;
	color: #333;
	transition: all 0.2s;
}

.pagination a:hover {
	background-color: #f0f0f0;
	border-color: #999;
}

.pagination span {
	background-color: #007bff;
	color: white;
	border-color: #007bff;
	font-weight: 600;
}

.pagination-info {
	text-align: center;
	margin-top: 15px;
	color: #666;
	font-size: 14px;
}
</style>

<div class="container">
	<div class="stats-section">
		<div class="section-header">
			<h2 class="section-title">页面浏览统计</h2>
			<?php if( $total_count > 0 ): ?>
				<span class="stats-count">共计: <?php echo $total_count; ?> 个页面</span>
			<?php endif; ?>
		</div>

		<?php if( empty($page_views) ): ?>
			<div class="empty-state">
				<p>暂无页面浏览数据</p>
			</div>
		<?php else: ?>
			<table class="stats-table">
				<thead>
					<tr>
						<th>网址</th>
						<th style="text-align: center; width: 100px;">访问次数</th>
						<th style="text-align: center; width: 120px;">展示次数</th>
						<th style="width: 180px;">更新时间</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach( $page_views as $row ): ?>
						<tr>
							<td>
								<a href="<?php echo htmlspecialchars($row->uri); ?>" target="_blank" title="<?php echo htmlspecialchars($row->uri); ?>">
									<?php echo htmlspecialchars(get_readable_uri_name($row->uri)); ?>
								</a>
							</td>
							<td style="text-align: center;">
								<?php echo (int)$row->visits; ?>
							</td>
							<td style="text-align: center;">
								<?php echo (int)$row->impressions; ?>
							</td>
							<td>
								<?php echo htmlspecialchars($row->updated_at); ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<!-- Pagination -->
			<?php if( $total_pages > 1 ): ?>
				<div class="pagination">
					<?php
					$prev_page = $current_page - 1;
					$next_page = $current_page + 1;
					?>
					
					<?php if( $current_page > 1 ): ?>
						<a href="<?php echo ROOT_PATH; ?>?uri=stat&page=1">首页</a>
						<a href="<?php echo ROOT_PATH; ?>?uri=stat&page=<?php echo $prev_page; ?>">上一页</a>
					<?php endif; ?>

					<?php 
					// Show page numbers
					$start_page = max(1, $current_page - 2);
					$end_page = min($total_pages, $current_page + 2);
					
					for( $i = $start_page; $i <= $end_page; $i++ ):
					?>
						<?php if( $i === $current_page ): ?>
							<span><?php echo $i; ?></span>
						<?php else: ?>
							<a href="<?php echo ROOT_PATH; ?>?uri=stat&page=<?php echo $i; ?>"><?php echo $i; ?></a>
						<?php endif; ?>
					<?php endfor; ?>

					<?php if( $current_page < $total_pages ): ?>
						<a href="<?php echo ROOT_PATH; ?>?uri=stat&page=<?php echo $next_page; ?>">下一页</a>
						<a href="<?php echo ROOT_PATH; ?>?uri=stat&page=<?php echo $total_pages; ?>">末页</a>
					<?php endif; ?>
				</div>
				<p class="pagination-info">第 <?php echo $current_page; ?> 页，共 <?php echo $total_pages; ?> 页</p>
			<?php endif; ?>
		<?php endif; ?>
	</div>
</div>

<?php
include ROOT_PATH . '/templates/footer.php';
