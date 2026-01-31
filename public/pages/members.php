<?php
// Members listing page with pagination

$per_page = 20;
$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($current_page - 1) * $per_page;

// Get total count and members
$total_members = get_members_count();
$total_pages = ceil($total_members / $per_page);
$members = get_members($per_page, $offset);

$meta_title = '所有会员｜纽约华人交友｜法拉盛相亲名单浏览';
$meta_description = '浏览纽约华人交友平台所有会员，覆盖法拉盛与纽约市，查看真实用户资料，免费找男朋友、找女朋友，快速匹配合适对象，开启你的脱单之旅。';
$meta_keywords = '纽约华人交友, 法拉盛相亲, 纽约找男朋友, 纽约找女朋友, 所有会员, 纽约婚介平台, 免费交友, NYC dating';


include ROOT_PATH . '/templates/header.php';
?>

<div class="container">
	<div class="members-section">
		<div class="section-header">
			<h2 class="section-title">所有会员</h2>
			<?php if( $total_members > 0 ): ?>
				<span class="member-count">共 <?php echo $total_members; ?> 位会员</span>
			<?php endif; ?>
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
								if( isset($member->gender) && $member->gender ) echo '<span class="gender">' . get_gender_display($member->gender) . '</span>';
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
							
						</div>
					</article>
				<?php endforeach; ?>
				<div class="clear"></div>
			</div>

			<?php if( $total_pages > 1 ): ?>
				<div class="pagination">
					<?php if( $current_page > 1 ): ?>
						<?php if( $current_page == 2 ): ?>
							<a href="<?php echo SITE_URL; ?>/members" class="pagination-link prev">上一页</a>
						<?php else: ?>
							<a href="<?php echo SITE_URL; ?>/members/page/<?php echo $current_page - 1; ?>" class="pagination-link prev">上一页</a>
						<?php endif; ?>
					<?php endif; ?>
					
					<?php
					// Show page numbers
					$start_page = max(1, $current_page - 2);
					$end_page = min($total_pages, $current_page + 2);
					
					if( $start_page > 1 ): ?>
						<a href="<?php echo SITE_URL; ?>/members" class="pagination-link">1</a>
						<?php if( $start_page > 2 ): ?>
							<span class="pagination-ellipsis">...</span>
						<?php endif; ?>
					<?php endif; ?>
					
					<?php for( $i = $start_page; $i <= $end_page; $i++ ): ?>
						<?php if( $i == $current_page ): ?>
							<span class="pagination-link current"><?php echo $i; ?></span>
						<?php elseif( $i == 1 ): ?>
							<a href="<?php echo SITE_URL; ?>/members" class="pagination-link"><?php echo $i; ?></a>
						<?php else: ?>
							<a href="<?php echo SITE_URL; ?>/members/page/<?php echo $i; ?>" class="pagination-link"><?php echo $i; ?></a>
						<?php endif; ?>
					<?php endfor; ?>
					
					<?php if( $end_page < $total_pages ): ?>
						<?php if( $end_page < $total_pages - 1 ): ?>
							<span class="pagination-ellipsis">...</span>
						<?php endif; ?>
						<a href="<?php echo SITE_URL; ?>/members/page/<?php echo $total_pages; ?>" class="pagination-link"><?php echo $total_pages; ?></a>
					<?php endif; ?>
					
					<?php if( $current_page < $total_pages ): ?>
						<a href="<?php echo SITE_URL; ?>/members/page/<?php echo $current_page + 1; ?>" class="pagination-link next">下一页</a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		<?php endif; ?>
	</div>
</div>

<?php include ROOT_PATH . '/templates/footer.php'; ?>
