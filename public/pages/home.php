<?php
// Home page - Latest members

$meta_title = '纽约同城交友免费平台 - 找男朋友找女朋友';
$meta_description = '纽约有什么靠谱的免费婚介交友平台？专注纽约华人相亲与交友服务，覆盖法拉盛、纽约市，免费注册、真实用户、无中介、无套路，帮你高效脱单，轻松找男朋友找女朋友。';
$meta_keywords = '纽约免费交友, 纽约婚介交友, 法拉盛相亲, 纽约找男朋友, 纽约找女朋友, 纽约华人婚介, 免费相亲平台, NYC dating, 脱单';



// Get latest members
$members = get_members(20);

include ROOT_PATH . '/templates/header.php';
?>

<style>
/* Paid member corner ribbon */
.paid-ribbon {
	position: absolute;
	top: 0;
	left: 0;
	width: 0;
	height: 0;
	border-top: 80px solid #ffca28;
	border-right: 80px solid transparent;
	z-index: 5;
}

/* Gradient overlay to match promo */
.paid-ribbon::before {
	content: "";
	position: absolute;
	top: -80px;
	left: 0;
	width: 80px;
	height: 80px;
	background: linear-gradient(135deg, #ffe082 0%, #ffca28 100%);
	clip-path: polygon(0 0, 100% 0, 0 100%);
}

/* Text inside triangle */
.paid-ribbon span {
	position: absolute;
	top: -40px;
	left: 2px;
	transform: rotate(-45deg);
	transform-origin: left top;
	color: #212529;
	font-size: 14px;
	font-weight: 700;
	letter-spacing: 0.5px;
	white-space: nowrap;
}
@media (max-width: 768px) {
	.paid-ribbon {
		border-top: 60px solid #ffca28;
		border-right: 60px solid transparent;
	}

	.paid-ribbon::before {
		top: -60px;
		width: 60px;
		height: 60px;
	}

	.paid-ribbon span {
		top: -28px;
		font-size: 11px;
	}
}
</style>

<!-- Hero Section -->
<section class="hero">
	<div class="hero-content-wrapper">

	<div class="hero-left">
	<div class="featured-member">
			<div class="paid-ribbon">
				<span>付费会员</span>
			</div>

			<div class="featured-member-image">
				<img src="<?php echo SITE_URL; ?>/assets/paid-members/image-2.png" alt="Featured Member" />
			</div>

			<div class="featured-member-content">
				<div class="featured-member-promo">
					<span class="promo-text">
						只需50美元即可置顶一个月，让更多人看到你！
						<a href="<?php echo SITE_URL; ?>/contacts">联系我们</a>了解更多详情。
					</span>
				</div>

				<h2 class="featured-member-name">Leo先生</h2>
				<div class="featured-member-info">
					<span class="featured-age">32岁</span>
					<span class="featured-gender">男生</span>
				</div>

				<div class="featured-member-description">
					<p>
						在纽约法拉盛生活将近20年，已稳定扎根本地，有自己的房子和车。白领办公室工作，生活规律，做事认真踏实。性格温和、有责任感，待人真诚，重视沟通和尊重。平时喜欢科技和AI相关的新事物，也会健身、看电影、尝试不同的美食。希望遇到一个三观相近、愿意一起规划未来、互相支持的另一半，一起把生活过得简单而有温度。
					</p>
				</div>

				<div class="featured-member-contact">
					<p>微信: <strong class="copy-text" onclick="copy('goodboy2ny')" role="button" tabindex="0">goodboy2ny</strong></p>
					<p>电邮: <span class="sub">未公开</span></p>
					<p>电话: <span class="sub">未公开</span></p>
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

		<?php include __DIR__ . '/../templates/search-filters.php'; ?>

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
							
							?>

							<?php if( $wechat || $email || $phone ): ?>
								<div class="member-contact">
									<?php if( $wechat ): ?>
										微信:
										<strong class="copy-text" onclick="copy('<?php echo htmlspecialchars($wechat, ENT_QUOTES); ?>')">
											<?php echo htmlspecialchars($wechat); ?>
										</strong><br/>
									<?php endif; ?>
							
									<?php if( $email ): ?>
										电邮:
										<span class="copy-text" onclick="copy('<?php echo htmlspecialchars($email, ENT_QUOTES); ?>')">
											<?php echo htmlspecialchars($email); ?>
										</span><br/>
									<?php endif; ?>
							
									<?php if( $phone ): ?>
										手机:
										<span class="copy-text" onclick="copy('<?php echo htmlspecialchars($phone, ENT_QUOTES); ?>')">
											<?php echo htmlspecialchars($phone); ?>
										</span>
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

<!-- Latest Posts Section -->
<?php
$latest_posts = get_latest_wp_posts(10);
?>

<section class="posts-section">
	<div class="container">
		<div class="section-header">
			<h2 class="section-title">最新文章</h2>
			<a href="<?php echo SITE_URL; ?>/blog" class="view-all-link">查看所有文章 <span>→</span></a>
		</div>

		<?php if (empty($latest_posts)): ?>
			<div class="empty-state">
				<p>暂无文章</p>
			</div>
		<?php else: ?>
			<div class="posts-list">
				<?php foreach ($latest_posts as $post): ?>
					<?php
						$post_id   = (int)$post->ID;
						$title     = $post->post_title ?: '（无标题）';
						$date      = date('Y-m-d', strtotime($post->post_date));
						$excerpt  = wp_excerpt($post->post_content, 160);

						// Pretty blog URL: /blog/<post_name>
						$slug = isset($post->post_name) ? $post->post_name : '';
						$slug = trim($slug);

						// Encode for URL (handles Chinese safely)
						$slug_encoded = rawurlencode($slug);

						$url = SITE_URL . '/blog/' . $slug_encoded;
					?>
					<article class="post-card">
						<div class="post-card-inner">
							<h3 class="post-title">
								<a href="<?php echo htmlspecialchars($url); ?>">
									<?php echo htmlspecialchars($title); ?>
								</a>
							</h3>
							<div class="post-meta"><?php echo htmlspecialchars($date); ?></div>
							<p class="post-excerpt"><?php echo htmlspecialchars($excerpt); ?></p>
						</div>
					</article>
				<?php endforeach; ?>

				<div class="clear"></div>
			</div>
			
			<!-- See More Posts Button -->
			<div style="text-align: center; padding: 100px 0 30px 0;">
				<a href="<?php echo SITE_URL; ?>/blog" class="see-more-btn">
					查看更多文章
				</a>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php include ROOT_PATH . '/templates/footer.php'; ?>
