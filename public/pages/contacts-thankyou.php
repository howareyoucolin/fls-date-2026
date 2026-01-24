<?php
// Contacts thank you page

$meta_title = '留言已提交 - 纽约同城交友';
$meta_description = '感谢您的留言';
$meta_keywords = '联系我们, 留言成功, 纽约同城交友';

include ROOT_PATH . '/templates/header.php';
?>

<div class="container">
	<div class="thankyou-page" style="max-width: 600px; margin: 60px auto; text-align: center; background: #fff; padding: 40px; border: 1px solid #e0e0e0; border-radius: 8px;">
		<h2 style="font-size: 28px; color: #4CAF50; margin-bottom: 20px;">✓ 留言已提交！</h2>
		<p style="font-size: 16px; color: #666; margin-bottom: 30px; line-height: 1.8;">
			感谢你的留言！我们已经收到你的信息，会尽快回复你。一般情况下会在 <b>1 至 24 小时</b> 内回复。
		</p>

		<div style="margin-top: 30px;">
			<a href="<?php echo SITE_URL; ?>" style="display: inline-block; background: #D72171; color: #fff; padding: 12px 30px; border-radius: 5px; text-decoration: none; font-weight: bold; margin-right: 15px;">返回首页</a>
			<a href="<?php echo SITE_URL; ?>/members" style="display: inline-block; background: #666; color: #fff; padding: 12px 30px; border-radius: 5px; text-decoration: none; font-weight: bold;">查看所有会员</a>
		</div>
	</div>
</div>

<?php include ROOT_PATH . '/templates/footer.php'; ?>
