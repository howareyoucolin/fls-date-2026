<?php
// Home page - Latest members

$meta_title = '纽约同城婚介交友 - 找男朋友找女朋友';
$meta_description = '纽约有什么好的婚介交友? 你在相亲,找男朋友吗? 理工男在找女朋友. 18岁与家人一起移民来美国, 在纽约法拉盛住了差不多十多年. 广交友, 性格好.';
$meta_keywords = '纽约婚介交友, 法拉盛婚介找友, 纽约找男朋友';

// Get latest members
$members = get_members(20);

include ROOT_PATH . '/templates/header.php';
?>

<!-- Hero Section -->
<section class="hero">
	<div class="container">
		<div class="hero-left">
			<h1 class="hero-title">纽约交友找男女朋友</h1>
			<div class="hero-content">
				<p>■ 单身朋友欢迎参加世界单身感恩节活動2/14单身遇见爱情,会员单身派对活动 Single party</p>
				<p>2/14周六下午 Party 2:00-4:00内容:国标舞学习</p>
				<p>聚晚餐,速配交友,才艺表演,兴趣联络,卡拉ok,跳舞交友.</p>
				<p>报名参加人士包括商界白领精英,公司主管,艺术 音乐界人士等等,</p>
				<p>活动详细报名见公司网站:世界单身联谊会。详细信息请上公司网站</p>
				<p>注册: www.gsg365.com.</p>
				<p>地點: NY.7326662066/7323316189/.</p>
				<p>歡迎报名参加等等,活动详细报名见公司网站:世界单身联</p>
			</div>
		</div>
		<div class="hero-right">
			<a href="<?php echo SITE_URL; ?>/signup" class="hero-btn">马上免费注册</a>
			<p class="hero-btn-text">注册十分简单</p>
			<p class="hero-btn-text">只花半分钟</p>
		</div>
		<div class="clear"></div>
	</div>
</section>

<!-- Latest Members Section -->
<section class="members-section">
	<div class="container">
		<div class="section-header">
			<h2 class="section-title">最新会员</h2>
			<a href="<?php echo SITE_URL; ?>/members" class="view-all-link">查看所有会员 <span>→</span></a>
		</div>

		<?php if( empty($members) ): ?>
			<div class="empty-state">
				<p>暂无会员信息</p>
			</div>
		<?php else: ?>
			<div class="members-grid">
				<?php foreach( $members as $member ): ?>
					<article class="member-card">
						<a href="<?php echo get_member_url($member->id); ?>" class="member-image-link">
							<img src="<?php echo get_profile_image_url($member); ?>" alt="<?php echo htmlspecialchars($member->title); ?>" class="member-image">
						</a>
						<div class="member-card-content">
							<h3 class="member-name">
								<a href="<?php echo get_member_url($member->id); ?>">
									<?php echo htmlspecialchars($member->title); ?>
								</a>
							</h3>
							<div class="member-info">
								<?php 
								$age = calculate_age($member->birthday);
								if( $age ) echo '<span class="age">' . $age . '岁</span>';
								if( $member->gender ) echo '<span class="gender">' . get_gender_display($member->gender) . '</span>';
								?>
							</div>
							<?php if( !empty($member->description) ): ?>
								<p class="member-intro">
									<?php echo htmlspecialchars(truncate_text($member->description, 100)); ?>
								</p>
							<?php endif; ?>
							<?php 
							$wechat = isset($member->wechat) && !empty($member->wechat) ? $member->wechat : '';
							$email = isset($member->email) && !empty($member->email) ? $member->email : '';
							$phone = isset($member->phone) && !empty($member->phone) ? $member->phone : '';
							
							if( $wechat || $email || $phone ): ?>
								<div class="member-contact">
									<?php if( $wechat ): ?>
										微信: <strong><?php echo htmlspecialchars($wechat); ?></strong><br/>
									<?php endif; ?>
									<?php if( $email ): ?>
										电邮: <?php echo htmlspecialchars($email); ?><br/>
									<?php endif; ?>
									<?php if( $phone ): ?>
										手机: <?php echo htmlspecialchars($phone); ?>
									<?php endif; ?>
								</div>
							<?php endif; ?>
							<div class="clear"></div>
						</div>
					</article>
				<?php endforeach; ?>
				<div class="clear"></div>
			</div>
		<?php endif; ?>
	</div>
</section>

<!-- See More Members Button -->
<section class="see-more-section">
	<div class="container">
		<div style="text-align: center; padding: 30px 0;">
			<a href="<?php echo SITE_URL; ?>/members/page/2" class="see-more-btn">查看更多会员</a>
		</div>
	</div>
</section>

<?php include ROOT_PATH . '/templates/footer.php'; ?>
