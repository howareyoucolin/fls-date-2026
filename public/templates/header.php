<!DOCTYPE html>
<html lang="zh-CN">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo isset($meta_title) ? htmlspecialchars($meta_title) : '纽约同城交友 - 让华人在纽约不再孤单'; ?></title>
	<?php if( isset($meta_description) ): ?>
	<meta name="description" content="<?php echo htmlspecialchars($meta_description); ?>">
	<?php endif; ?>
	<?php if( isset($meta_keywords) ): ?>
	<meta name="keywords" content="<?php echo htmlspecialchars($meta_keywords); ?>">
	<?php endif; ?>
	<link rel="icon" href="/favicon.ico" type="image/x-icon">
	<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css?v=6">
</head>
<body>
	<div class="header">
		<div class="header-content-wrapper">
			<h1><a href="<?php echo SITE_URL; ?>">纽约同城交友</a></h1>
			<a class="normal last" href="<?php echo SITE_URL; ?>/contacts"><span>联系</span><br class="mobile-only" />我们</a>
			<a class="normal" href="<?php echo SITE_URL; ?>/members"><span>所有</span><br class="mobile-only" />会员</a>
			<a class="signup" href="<?php echo SITE_URL; ?>/signup"><span>免费</span><br class="mobile-only" />注册</a>
		</div>
	</div>
	<main class="main">
