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
	<div class="hero-content-wrapper">
		<div class="hero-left">
			<div class="featured-member">
				<div class="featured-member-image">
					<img src="<?php echo SITE_URL; ?>/assets/paid-members/image-1.png" alt="Featured Member" />
				</div>
				<div class="featured-member-content">
					<div class="featured-member-promo">
						<span class="promo-icon">⭐</span>
						<span class="promo-text">只需50美元即可置顶一个月，让更多人看到你！</span>
					</div>
					<h2 class="featured-member-name">张美丽</h2>
					<div class="featured-member-info">
						<span class="featured-age">28岁</span>
						<span class="featured-gender">女生</span>
					</div>
					<div class="featured-member-description">
						<p>来自上海，在纽约工作已有5年。喜欢旅行、阅读和美食。性格开朗，喜欢交朋友。希望找到一个有共同兴趣爱好的另一半，一起探索纽约的美好生活。</p>
					</div>
					<div class="featured-member-contact">
						<p>微信: <strong>zhangmeili2024</strong></p>
						<p>电邮: zhangmeili@example.com</p>
					</div>
				</div>
			</div>
		</div>
		<div class="hero-right">
			<div class="signup-card">
				<div class="signup-card-header">
					<h3 class="signup-title">马上注册</h3>
					<p class="signup-subtitle">你的幸福从这里开始</p>
				</div>
				<div class="signup-features">
					<div class="feature-item">
						<span class="feature-icon">✓</span>
						<span class="feature-text">完全免费</span>
					</div>
					<div class="feature-item">
						<span class="feature-icon">✓</span>
						<span class="feature-text">注册简单</span>
					</div>
					<div class="feature-item">
						<span class="feature-icon">✓</span>
						<span class="feature-text">只需半分钟</span>
					</div>
				</div>
				<a href="<?php echo SITE_URL; ?>/signup" class="hero-btn-large">
					<span class="btn-text-main">马上免费注册</span>
					<span class="btn-text-sub">立即开始 →</span>
				</a>
			</div>
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
