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
	<style>
		* { margin: 0; padding: 0; box-sizing: border-box; }
		body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "PingFang SC", "Hiragino Sans GB", "Microsoft YaHei", sans-serif; line-height: 1.6; color: #333; background: #fafafa; }
		.container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
		
		/* Header */
		.header { height: 60px; line-height: 60px; background: #e91e63; color: #FFF; }
		.header .container { width: 860px; max-width: 100%; margin: 0 auto; padding: 0 15px; box-sizing: border-box; }
		.header a { color: #FFF; text-decoration: none; }
		.header h1 { padding: 0; margin: 0; font-size: 18px; font-weight: bold; }
		.header h1 a { float: left; font-weight: normal; }
		.header .normal { float: right; line-height: 60px; color: #FFF; padding: 0 10px; margin-right: 10px; }
		.header .signup { float: right; line-height: 60px; color: #FFF; padding: 0 10px; }
		.header .last { margin-right: -5px; }
		.mobile-only { display: none; }
		
		/* Main */
		.main { min-height: 60vh; }
		
		/* Hero Section */
		.hero { background: #474747; color: #fff; padding: 30px 0; }
		.hero .container { width: 860px; max-width: 100%; margin: 0 auto; padding: 0 15px; box-sizing: border-box; }
		.hero-left { float: left; width: 65%; padding-right: 20px; box-sizing: border-box; }
		.hero-right { float: right; width: 35%; text-align: center; }
		.hero-title { font-size: 20px; font-weight: bold; margin-bottom: 15px; color: #fff; }
		.hero-content { font-size: 14px; line-height: 1.8; }
		.hero-content p { margin: 0 0 8px; }
		.hero-btn { display: inline-block; background: #4CAF50; color: #fff; padding: 15px 30px; border-radius: 5px; text-decoration: none; font-size: 18px; font-weight: bold; margin-bottom: 10px; }
		.hero-btn:hover { background: #45a049; }
		.hero-btn-text { font-size: 14px; color: #fff; margin: 5px 0; }
		.clear { clear: both; }
		
		/* Members Section */
		.members-section { padding: 4rem 0; }
		.section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2.5rem; }
		.section-title { font-size: 2.25rem; font-weight: 700; color: #1a1a1a; }
		.view-all-link { color: #e53935; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; transition: gap 0.2s; }
		.view-all-link:hover { gap: 0.75rem; }
		.view-all-link span { transition: transform 0.2s; }
		.view-all-link:hover span { transform: translateX(4px); }
		
		.members-grid { display: block; }
		.member-card { background: #fff; margin-bottom: 20px; overflow: hidden; border: 1px solid #e0e0e0; width: 48%; float: left; margin-right: 4%; }
		.member-card:nth-child(2n) { margin-right: 0; }
		.member-image-link { float: left; width: 150px; height: 150px; overflow: hidden; }
		.member-image { width: 150px; height: 150px; object-fit: cover; display: block; }
		.member-card-content { margin-left: 160px; padding: 15px; }
		.member-name { font-size: 18px; font-weight: bold; margin-bottom: 8px; }
		.member-name a { color: #000; text-decoration: none; }
		.member-name a:hover { color: #D72171; }
		.member-info { margin-bottom: 10px; color: #666; font-size: 14px; }
		.member-info .age, .member-info .gender { margin-right: 10px; }
		.member-intro { color: #555; font-size: 14px; line-height: 1.6; margin-bottom: 10px; }
		.member-contact { padding-top: 10px; border-top: 1px dashed #ddd; color: #555; font-size: 14px; }
		.member-contact strong { color: #D72171; font-weight: bold; }
		
		.empty-state { text-align: center; padding: 4rem 2rem; color: #999; }
		
		/* Signup Page */
		.signup-page { max-width: 600px; margin: 40px auto; background: #fff; padding: 40px; border: 1px solid #e0e0e0; border-radius: 8px; }
		.signup-title { font-size: 24px; font-weight: bold; margin-bottom: 30px; color: #333; text-align: center; }
		.form-group { margin-bottom: 25px; }
		.form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: #333; }
		.form-group .required { color: #D72171; }
		.form-group input[type="text"], 
		.form-group input[type="email"],
		.form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; font-family: inherit; box-sizing: border-box; }
		.form-group input[type="text"]:focus, 
		.form-group input[type="email"]:focus,
		.form-group textarea:focus { outline: none; border-color: #D72171; }
		.form-group textarea { resize: vertical; min-height: 120px; }
		.radio-group { display: flex; gap: 20px; }
		.radio-group label { display: flex; align-items: center; gap: 5px; font-weight: normal; cursor: pointer; }
		.date-selectors { display: flex; gap: 10px; }
		.date-selectors select { padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; cursor: pointer; }
		.date-selectors select:focus { outline: none; border-color: #D72171; }
		.form-hint { font-size: 12px; color: #777; margin-bottom: 8px; line-height: 1.5; }
		.char-count { font-size: 12px; color: #D72171; margin-top: 5px; }
		.submit-btn { background: #D72171; color: #fff; padding: 12px 40px; border: none; border-radius: 4px; font-size: 16px; font-weight: bold; cursor: pointer; width: 100%; transition: background 0.3s; }
		.submit-btn:hover { background: #b81a5a; }
		.error-message { background: #ffebee; color: #c62828; padding: 15px; border-radius: 4px; margin-bottom: 20px; border-left: 4px solid #c62828; }
		.success-message { background: #e8f5e9; color: #2e7d32; padding: 15px; border-radius: 4px; margin-bottom: 20px; border-left: 4px solid #2e7d32; }
		
		/* Image Upload */
		.image-upload-wrapper { margin-top: 10px; }
		.image-preview { position: relative; width: 185px; height: 185px; border: 2px dashed #ddd; border-radius: 4px; overflow: hidden; margin-bottom: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center; background: #f9f9f9; }
		.image-preview:hover { border-color: #D72171; background: #f5f5f5; }
		.image-preview img { width: 100%; height: 100%; object-fit: cover; display: block; }
		.upload-text { color: #999; font-size: 14px; text-align: center; pointer-events: none; }
		.image-preview.has-image .upload-text { display: none; }
		.upload-close { position: absolute; top: 5px; right: 5px; width: 24px; height: 24px; line-height: 24px; text-align: center; background: #D72171; color: #fff; border-radius: 50%; font-size: 18px; cursor: pointer; z-index: 10; }
		.upload-close:hover { background: #b81a5a; }
		.upload-message { margin-top: 10px; font-size: 12px; }
		.upload-message.error { color: #D72171; }
		.upload-message.success { color: #4CAF50; }
		
		/* See More Section */
		.see-more-section { padding: 20px 0; }
		.see-more-btn { display: inline-block; background: #D72171; color: #fff; padding: 12px 30px; border-radius: 5px; text-decoration: none; font-size: 16px; font-weight: bold; transition: background 0.3s; }
		.see-more-btn:hover { background: #b81a5a; }
		
		/* Pagination */
		.pagination { text-align: center; margin: 40px 0; padding: 20px 0; }
		.pagination-link { display: inline-block; padding: 8px 12px; margin: 0 4px; color: #333; text-decoration: none; border: 1px solid #ddd; border-radius: 4px; transition: all 0.3s; }
		.pagination-link:hover { background: #D72171; color: #fff; border-color: #D72171; }
		.pagination-link.current { background: #D72171; color: #fff; border-color: #D72171; cursor: default; }
		.pagination-link.prev, .pagination-link.next { font-weight: bold; }
		.pagination-ellipsis { display: inline-block; padding: 8px 4px; color: #999; }
		.member-count { float: right; color: #666; font-size: 14px; line-height: 24px; }
		
		/* Member Detail Page */
		/* Breadcrumb */
		.breadcrumb { margin-bottom: 5px; padding: 25px 0 0 0; font-size: 14px; }
		.breadcrumb-item { color: #666; text-decoration: none; transition: color 0.2s; }
		.breadcrumb-item:hover { color: #D72171; text-decoration: underline; }
		.breadcrumb-separator { color: #999; margin: 0 8px; }
		.breadcrumb-current { color: #333; font-weight: 500; }
		.highlight { margin: 15px 0 25px; background: #FFF; padding: 20px; border: 1px solid #e0e0e0; }
		.highlight .profile { width: 300px; float: left; margin: 0 25px 0 0; }
		.highlight .contacts p { border-bottom: 1px dashed #DDD; line-height: 37px; margin: 0; }
		.highlight .contacts p span.sub { color: #DDD; }
		.highlight .contacts p b.focus { color: #D72171; }
		.content { background: #FFF; padding: 20px; border: 1px solid #e0e0e0; }
		.content h3 { padding: 0; margin: 15px 0; font-size: 18px; font-weight: bold; color: #000; }
		.content h3:first-child { margin-top: 0; }
		.content div { line-height: 1.8; color: #333; }
		
		/* About Section */
		.about-section { background: #fff; padding: 4rem 0; margin-top: 2rem; }
		.about-content { max-width: 800px; margin: 0 auto; text-align: center; }
		.about-title { font-size: 2rem; font-weight: 700; color: #1a1a1a; margin-bottom: 1.5rem; }
		.about-text { color: #555; line-height: 1.8; }
		.about-text p { margin-bottom: 1rem; }
		.about-text p:last-child { margin-bottom: 0; }
		
		/* Footer */
		.footer { background: #1a1a1a; color: #fff; padding: 2.5rem 0; margin-top: 4rem; text-align: center; }
		
		/* Responsive */
		@media only screen and (max-width: 680px) {
			.header .last { margin-right: -5px; }
			.header .signup, .header .normal {
				line-height: 22px;
				padding: 8px 8px;
			}
			.hero { padding: 20px 0; }
			.hero-left, .hero-right { width: 100%; float: none; padding: 0; }
			.hero-title { font-size: 18px; }
			.hero-content { font-size: 13px; }
			.section-header { flex-direction: column; align-items: flex-start; gap: 1rem; }
			.section-title { font-size: 1.75rem; }
			.member-card { width: 100%; margin-right: 0; }
			.member-image-link { width: 100px; height: 100px; float: left; }
			.member-image { width: 100px; height: 100px; }
			.member-card-content { margin-left: 110px; padding: 10px; }
			.highlight .profile { max-width: 100%; width: 100%; float: none; margin: 0 0 15px 0; }
			.highlight { padding: 15px; }
			.about-title { font-size: 1.5rem; }
			.mobile-only { display: block; }
		}
		@media (max-width: 480px) {
			.members-grid { grid-template-columns: 1fr; }
			.hero-cta { flex-direction: column; }
			.btn { width: 100%; text-align: center; }
		}
	</style>
</head>
<body>
	<div class="header">
		<div class="container">
			<h1><a href="<?php echo SITE_URL; ?>">纽约同城交友</a></h1>
			<a class="normal last" href="<?php echo SITE_URL; ?>/members"><span>所有</span><br class="mobile-only" />会员</a>
			<a class="signup" href="<?php echo SITE_URL; ?>/signup"><span>免费</span><br class="mobile-only" />注册</a>
		</div>
	</div>
	<main class="main">
