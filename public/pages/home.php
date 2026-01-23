<?php
// Home page - Latest members

$meta_title = '纽约同城婚介交友 - 找男朋友找女朋友';
$meta_description = '纽约有什么好的婚介交友? 你在相亲,找男朋友吗? 理工男在找女朋友. 18岁与家人一起移民来美国, 在纽约法拉盛住了差不多十多年. 广交友, 性格好.';
$meta_keywords = '纽约婚介交友, 法拉盛婚介找友, 纽约找男朋友';

// Get latest members
$members = get_members(12);

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
								if( $member->gender ) echo '<span class="gender">' . htmlspecialchars($member->gender) . '</span>';
								?>
							</div>
							<?php if( !empty($member->description) ): ?>
								<p class="member-intro">
									<?php echo htmlspecialchars(truncate_text($member->description, 100)); ?>
								</p>
							<?php endif; ?>
							<?php 
							$wechat = isset($member->wechat) && !empty($member->wechat) ? $member->wechat : '';
							if( $wechat ): ?>
								<div class="member-contact">
									微信: <strong><?php echo htmlspecialchars($wechat); ?></strong>
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

<!-- About Section -->
<section class="about-section">
	<div class="container">
		<div class="about-content">
			<h2 class="about-title">让华人在纽约不再孤单</h2>
			<div class="about-text">
				<p>当你一个人走在法拉盛的大路上时，当你一个人坐在通往曼哈顿的地铁上时，当你一个人漫步在布碌克林大桥时，你也许会觉得有少许的孤单。</p>
				<p>如果你只要向前走一小步，纽约同城交友网将帮助你跨出走向爱情的一大步。我们的目标就是让法拉盛的大街上走着的人都是成双成对，让我们华人在异国每个人都有情人終成眷屬。</p>
			</div>
		</div>
	</div>
</section>

<?php include ROOT_PATH . '/templates/footer.php'; ?>
