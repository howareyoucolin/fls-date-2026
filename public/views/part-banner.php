<style>
	.banner{background:#474747;color:#FFF;padding:30px 0;}
	.banner h2{font-size: 24px;color:#FFF;margin-bottom: 10px;}
	.banner ul{padding-left:20px;}
	.banner li{list-style: square;}
	@media only screen and (max-width: 680px) {
		.banner h2{text-align: center;}
	}
</style>
<div class="banner">
	<div class="container">
		<h2>纽约交友找男女朋友</h2>
		<div class="col-6">
			<ul>
				<li id="mary-content"></li>
			</ul>
		</div>
		<!-- <div class="col-6">
			<ul>
				<li>仅限纽约本地人，非纽约居住者不要注册(包括纽约附近地区，如纽泽西，康州，费城...)。</li>
				<li><u>免费注册，免费介绍</u>，如果配对成功请自觉包大红包给红娘。</li>
				<li>保证资料真实性，红娘会私底下了解每个会员，以防骗子。</li>
				<li>提供正确的婚恋建议，帮助坚立正确的择偶标准。</li>
				<li>本网站红娘有着上20年的婚介经验，帮助配成上百对情侣。</li>
			</ul>
		</div>  -->
		<div class="col-6" style="padding:35px 0;text-align: center;">
			<a href="<?php echo SITE_URL;?>/signup">
				<button class="btn" style="font-size:28px;padding:20px 35px;">
					马上免费注册
				</button>
			</a>
			<div style="margin-top:6px;font-size:12px;">
				注册十分简单<br/>
				只花半分钟<br/>
			</div>
		</div>
		<div class="clear"></div>
	</div>
</div>
<script>
fetch('/intro.txt').then(function(response){return response.text()}).then(function (response) {
	 document.getElementById("mary-content").innerHTML = response;
});
</script>



