<?php
// Signup thank you page

$meta_title = '注册成功 - 纽约同城交友';
$meta_description = '感谢您的注册';
$meta_keywords = '注册成功, 纽约同城交友';

include ROOT_PATH . '/templates/header.php';
?>

<div class="container">
	<div class="thankyou-page" style="max-width: 600px; margin: 60px auto; text-align: center; background: #fff; padding: 40px; border: 1px solid #e0e0e0; border-radius: 8px;">
		<h2 style="font-size: 28px; color: #4CAF50; margin-bottom: 20px;">✓ 注册成功！</h2>
		<p style="font-size: 16px; color: #666; margin-bottom: 30px; line-height: 1.8;">
			十分感谢你注册做本网站的会员, 您的资料已成功提交到我们的后台, 您的宝贵资料将会被我们审核，审核一般会在1至24小时内完成，请您耐心等候，一旦通过审核之后将会出现在我们的网站, 谢谢!
		</p>
		<p style="margin: 30px 0;">
			<img src="<?php echo SITE_URL; ?>/assets/images/thankyou.gif" alt="Thank you" style="width: 70%; max-width: 280px;" />
		</p>
		<div style="margin-top: 30px;">
			<a href="<?php echo SITE_URL; ?>" style="display: inline-block; background: #D72171; color: #fff; padding: 12px 30px; border-radius: 5px; text-decoration: none; font-weight: bold; margin-right: 15px;">返回首页</a>
			<a href="<?php echo SITE_URL; ?>/members" style="display: inline-block; background: #666; color: #fff; padding: 12px 30px; border-radius: 5px; text-decoration: none; font-weight: bold;">查看所有会员</a>
		</div>
	</div>
</div>

<?php include ROOT_PATH . '/templates/footer.php'; ?>
