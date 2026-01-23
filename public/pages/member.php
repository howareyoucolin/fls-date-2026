<?php
// Member detail page

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if( !$id ){
	http_response_code(404);
	require_once( ROOT_PATH . '/pages/404.php' );
	exit;
}

$member = get_member($id);

if( !$member ){
	http_response_code(404);
	require_once( ROOT_PATH . '/pages/404.php' );
	exit;
}

$age = calculate_age($member->birthday);
$wechat = isset($member->wechat) && !empty($member->wechat) ? $member->wechat : null;
$email = isset($member->email) && !empty($member->email) ? $member->email : null;
$phone = isset($member->phone) && !empty($member->phone) ? $member->phone : null;

$meta_title = '纽约同城婚介交友 - ' . htmlspecialchars($member->title) . ',' . htmlspecialchars($member->gender) . '交友' . ($age ? ',' . $age : '');
$meta_description = !empty($member->description) ? htmlspecialchars(truncate_text($member->description, 150)) : '';
$meta_keywords = '纽约婚介交友, 法拉盛婚介找友, 纽约找男朋友';

include ROOT_PATH . '/templates/header.php';
?>

<div class="container">
	<div class="breadcrum">
		<a href="<?php echo SITE_URL; ?>"><u>首页</u></a> >> 
		<a href="<?php echo SITE_URL; ?>/members"><u>所有会员</u></a> >>
		<?php echo htmlspecialchars($member->title); ?>
	</div>

	<div class="highlight">
		<img class="profile" src="<?php echo get_profile_image_url($member); ?>" alt="纽约交友会员照片" />
		<div class="contacts">
			<p>名字: <?php echo htmlspecialchars($member->title); ?></p>
			<?php if( $member->gender ): ?>
				<p>性别: <?php echo htmlspecialchars($member->gender); ?></p>
			<?php endif; ?>
			<?php if( $age ): ?>
				<p>年龄: <?php echo $age; ?>岁</p>
			<?php endif; ?>
			<p>
				微信:
				<?php if( $wechat ): ?>
					<b class="focus"><?php echo htmlspecialchars($wechat); ?></b>
				<?php else: ?>
					<span class="sub">未公开</span>
				<?php endif; ?>
			</p>
			<p>
				电邮:
				<?php if( $email ): ?>
					<?php echo htmlspecialchars($email); ?>
				<?php else: ?>
					<span class="sub">未公开</span>
				<?php endif; ?>
			</p>
			<p>
				手机:
				<?php if( $phone ): ?>
					<?php echo htmlspecialchars($phone); ?>
				<?php else: ?>
					<span class="sub">未公开</span>
				<?php endif; ?>
			</p>
		</div>
		<div class="clear"></div>
	</div>

	<div class="content">
		<?php if( !empty($member->description) ): ?>
			<?php 
			// Split description if it contains newlines (about_me and preference)
			$parts = explode("\n", $member->description, 2);
			$about_me = trim($parts[0] ?? '');
			$preference = trim($parts[1] ?? '');
			?>
			
			<?php if( $about_me ): ?>
				<h3>基本资料</h3>
				<div><?php echo nl2br(htmlspecialchars($about_me)); ?></div>
			<?php endif; ?>
			
			<?php if( $preference ): ?>
				<h3>喜欢什么样的<?php echo $member->gender == 'm' ? '女朋友' : ($member->gender == 'f' ? '男朋友' : '对象'); ?></h3>
				<div><?php echo nl2br(htmlspecialchars($preference)); ?></div>
			<?php endif; ?>
			
			<?php if( !$about_me && !$preference ): ?>
				<div><?php echo nl2br(htmlspecialchars($member->description)); ?></div>
			<?php endif; ?>
		<?php endif; ?>
	</div>
</div>

<?php include ROOT_PATH . '/templates/footer.php'; ?>
