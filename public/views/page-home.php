<!DOCTYPE html>
<html lang="zh">
<head>
	<?php include( ROOT_PATH . '/views/part-head.php' );?>
</head>
<body>
	<?php include( ROOT_PATH . '/views/part-header.php' );?>
	<?php include( ROOT_PATH . '/views/part-banner.php' );?>
	<div class="main">
		<div class="container">
			<div class="content">

				<div class="row">

					<div class="col-6">
						<div>
							<a style="float:right;" href="/members">查看所有会员</a>
							<h2 style="font-size:24px;">最新会员</h2>
						</div>
						<hr />
						<?php
						$count = 0; 
						foreach($members as $member): 
							if(++$count > 8) break;
							?>
							<div class="member clear">
								<!-- <h2>
									<a href="<?php echo $member->get_url();?>"><?php echo $member->get_super_title();?></a>	
								</h2> -->
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
						<?php endforeach;?>
					</div>

					<div class="col-6">

						<?php //include( ROOT_PATH . '/views/part-ad-rose.php' );?>

						<h2 style="font-size:24px;">高人气会员</h2>
						<hr />
						<div class="row" style="margin-bottom:30px;">
							<?php
							$count = 0; 
							foreach($featured_members as $member):
								if(++$count > 8) break;
								?>
								<div class="col-6-fix">
									<div class="hot-member clear">
										<a href="<?php echo $member->get_url();?>">
											<img class="profile" src="<?php echo $member->get_profile_image_url();?>" />
										</a>
										<p><?php echo $member->get_title();?> <?php echo $member->get_age();?> </p>
									</div>
								</div>
							<?php endforeach;?>
							<div class="clear"></div>
							
						</div>
						<?php include( ROOT_PATH . '/views/part-ad-mary-2.php' );?>
					</div>

					<div class="clear"></div>

				</div>

				<div style="padding:0 15px;">
					<h2>让华人在纽约不再孤单</h2><hr />
					<p style="font-size:14px;">
						当你一个人走在法拉盛的大路上时， 当你一个人坐在通往曼哈顿的地铁上时，当你一个人漫步在布碌克林大桥时，你也许会觉得有少许的孤单。
						如果你只要向前走一小步，纽约同城交友网将帮助你跨出走向爱情的一大步。我们的目标就是让法拉盛的大街上走着的人都是成双成对，让我们华人在异国每个人都有情人終成眷屬。
					</p>
				</div>

			</div>
		</div>
	</div>
	<?php include( ROOT_PATH . '/views/part-footer.php' );?>
</body>