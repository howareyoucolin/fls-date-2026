<?php
http_response_code(404);
$meta_title = '页面未找到 - 404';
$meta_description = '抱歉，您访问的页面不存在。';

include ROOT_PATH . '/templates/header.php';
?>

<div class="container" style="text-align: center; padding: 4rem 0;">
	<h2 style="font-size: 24px; margin-bottom: 20px;">您要找的网页不存在</h2>
	<div style="margin: 30px 0;">
		<img src="<?php echo SITE_URL; ?>/assets/images/404.png" alt="网页不存在" style="width: 100%; max-width: 400px;" />
	</div>
	<p style="margin-top: 30px;">
		<a href="<?php echo SITE_URL; ?>" style="display: inline-block; padding: 0.75rem 2rem; background: #D72171; color: #fff; text-decoration: none; border-radius: 4px; font-weight: bold;">点击这儿返回首页</a>
	</p>
</div>

<?php include ROOT_PATH . '/templates/footer.php'; ?>
