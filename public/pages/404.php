<?php
http_response_code(404);
$meta_title = '页面未找到 - 404';
$meta_description = '抱歉，您访问的页面不存在。';

include ROOT_PATH . '/templates/header.php';
?>

<div class="container" style="text-align: center; padding: 4rem 0;">
	<h1 style="font-size: 4rem; color: #d32f2f; margin-bottom: 1rem;">404</h1>
	<p style="font-size: 1.2rem; color: #666; margin-bottom: 2rem;">抱歉，您访问的页面不存在</p>
	<a href="<?php echo SITE_URL; ?>" style="display: inline-block; padding: 0.75rem 2rem; background: #d32f2f; color: #fff; text-decoration: none; border-radius: 4px;">返回首页</a>
</div>

<?php include ROOT_PATH . '/templates/footer.php'; ?>
