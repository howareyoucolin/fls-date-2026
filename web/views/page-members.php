<!DOCTYPE html>
<html lang="zh">
<head>
	<?php include( ROOT_PATH . '/views/part-head.php' );?>
</head>
<body>
	<?php include( ROOT_PATH . '/views/part-header.php' );?>

	<div class="main">
			<div class="container">
				<div class="content">

				<div class="breadcrum">
					<a href="<?php echo SITE_URL;?>"><u>首页</u></a> >> 所有会员
				</div>

					<div class="row">
						<?php 
							$no_image_members = array();
							$counter = 0;
							foreach($members as $member):
								if($member->get_profile_image_url() == DEFAULT_SILHOUETTE){
									$no_image_members[] = $member;
									continue;
								}
								?>
							<div class="col-6">
								<div class="member">
									<a href="<?php echo $member->get_url();?>">
										<img class="profile" src="<?php echo $member->get_profile_image_url();?>" />
									</a>
									<p style="word-wrap: break-word;">
										<?php echo $member->get_title();?>  
										<br class="mobile-only" />
										<?php echo $member->get_age();?>, <?php echo $member->get_gender();?><br/>
									</p>
									<p class="intro">
										<?php if( $member->get_wechat() ):?>						
											微信: <b class="focus"><?php echo $member->get_wechat();?></b><br/>
										<?php elseif( $member->get_phone() ):?>
											电话: <b class="focus"><?php echo $member->get_phone();?></b><br/>
										<?php elseif( $member->get_email() ):?>
											电邮: <?php echo $member->get_email();?><br/>
										<?php endif;?>
										<?php echo $member->get_intro();?> ...
										<a href="<?php echo $member->get_url();?>">[更多]</a>
									</p>
									<div class="clear"></div>
								</div>
							</div>
						<?php 
							if(++$counter % 2 === 0) echo '<div class="clear"></div>';
							endforeach;?>

							<? foreach($no_image_members as $member): ?>
							<div class="col-6">
								<div class="member">
									<a href="<?php echo $member->get_url();?>">
										<img class="profile" src="<?php echo $member->get_profile_image_url();?>" />
									</a>
									<p style="word-wrap: break-word;">
										<?php echo $member->get_title();?>  
										<br class="mobile-only" />
										<?php echo $member->get_age();?>, <?php echo $member->get_gender();?><br/>
									</p>
									<p class="intro">
										<?php if( $member->get_wechat() ):?>						
											微信: <b class="focus"><?php echo $member->get_wechat();?></b><br/>
										<?php elseif( $member->get_phone() ):?>
											电话: <b class="focus"><?php echo $member->get_phone();?></b><br/>
										<?php elseif( $member->get_email() ):?>
											电邮: <?php echo $member->get_email();?><br/>
										<?php endif;?>
										<?php echo $member->get_intro();?> ...
										<a href="<?php echo $member->get_url();?>">[更多]</a>
									</p>
									<div class="clear"></div>
								</div>
							</div>
						<?php 
							if(++$counter % 2 === 0) echo '<div class="clear"></div>';
							endforeach;?>
						<div class="clear"></div>
					</div>

			</div>
		</div>
	</div>

	<?php include( ROOT_PATH . '/views/part-footer.php' );?>
</body>