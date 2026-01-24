<?php
// Contact page (single file: PHP + HTML, no extra submit file)

// Turnstile site key (hardcoded - get from https://dash.cloudflare.com/?to=/:account/turnstile)
$turnstile_site_key = '0x4AAAAAACOW5GKIH-hAtF2X'; // Your Turnstile site key

// Skip Turnstile on localhost
$is_localhost = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1', 'localhost:9090', '127.0.0.1:9090']) ||
                (isset($_SERVER['SERVER_NAME']) && in_array($_SERVER['SERVER_NAME'], ['localhost', '127.0.0.1']));
$use_turnstile = !$is_localhost && !empty($turnstile_site_key);

$meta_title = '联系我们 - 纽约同城交友';
$meta_description = '联系网站管理员，提交问题或合作咨询。';
$meta_keywords = '联系我们, 广告, 置顶, 纽约同城交友';

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
	$name = trim($_POST['name'] ?? '');
	$wechat = trim($_POST['wechat'] ?? '');
	$email = trim($_POST['email'] ?? '');
	$message = trim($_POST['message'] ?? '');

	// Anti-spam
	$honeypot = trim($_POST['website'] ?? '');
	$form_start_time = isset($_POST['form_start_time']) ? (int)$_POST['form_start_time'] : 0;
	$turnstile_response = $_POST['cf-turnstile-response'] ?? '';
	$human_verify = isset($_POST['human_verify']) && $_POST['human_verify'] === 'yes';

	$errors = [];

	// Honeypot filled => bot (silently succeed)
	if (!empty($honeypot)) {
		$success_message = '已收到你的留言，我会尽快回复你。';
	} else {

		// Minimum time on page (8s)
		if ($form_start_time > 0) {
			$time_spent = time() - $form_start_time;
			if ($time_spent < 8) {
				$errors[] = '提交太快了，请花几秒钟认真填写后再提交。';
			}
		}

		// Turnstile on non-localhost, fallback checkbox if Turnstile not configured
		if ($is_localhost) {
			// Skip verification on localhost
		} elseif ($use_turnstile) {
			if (empty($turnstile_response)) {
				$errors[] = '请完成人机验证!';
			}
		} else {
			if (!$human_verify) {
				$errors[] = '请完成人机验证!';
			}
		}

		// Required fields
		if (empty($name)) {
			$errors[] = '必须填写你的名字!';
		}

		if (mb_strlen($message) < 5) {
			$errors[] = '留言内容太短了，请多写一点。';
		}

		// Require at least one contact method (wechat or email)
		if (empty($wechat) && empty($email)) {
			$errors[] = '请至少填写一种联系方式（微信 / 邮箱）方便我回复你。';
		}

		if (!empty($wechat) && strlen($wechat) < 4) {
			$errors[] = '微信号码格式不正确。';
		}

		if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$errors[] = '电子邮箱格式不正确。';
		}

		if (empty($errors)) {
			$to = 'howareyoucolin@gmail.com';
			$mail_subject = '【纽约同城交友】联系表单留言';

			$body = implode("\n", [
				"你收到一条新的联系表单留言：",
				"",
				"名字: " . ($name ? $name : '(未填写)'),
				"微信: " . ($wechat ? $wechat : '(未填写)'),
				"邮箱: " . ($email ? $email : '(未填写)'),
				"时间: " . date('Y-m-d H:i:s'),
				"IP: " . ($_SERVER['REMOTE_ADDR'] ?? ''),
				"",
				"留言内容：",
				$message,
				"",
			]);

			$headers = [];
			$headers[] = 'MIME-Version: 1.0';
			$headers[] = 'Content-Type: text/plain; charset=UTF-8';
			$headers[] = 'From: no-reply@' . ($_SERVER['HTTP_HOST'] ?? 'dev.flushingdating.com');

			// If user provided email, set reply-to for convenience
			if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$headers[] = 'Reply-To: ' . $email;
			}

			$ok = @mail($to, $mail_subject, $body, implode("\r\n", $headers));

			if ($ok) {
				$success_message = '✅ 已收到你的留言！我会尽快回复你。';
				$_POST = []; // reset form
			} else {
				$error_message = '发送失败（服务器 mail() 可能不可用）。请稍后再试。';
			}
		} else {
			$error_message = implode('<br>', $errors);
		}
	}
}

include ROOT_PATH . '/templates/header.php';
?>

<div class="container">
	<div class="signup-page">
		<h2 class="signup-title">联系我们</h2>

		<p class="form-hint" style="font-size:14px; color:#666; line-height:1.8; margin-top:-10px; margin-bottom:18px;">
			如果你有任何问题、建议、举报信息，或想合作推广，都可以在这里给我留言。我会尽快回复你。
		</p>

		<div style="background:#fff7e6; border:1px solid #ffe0a6; padding:16px 18px; border-radius:8px; line-height:1.9; color:#333; margin-bottom:20px;">
			<b>📣 广告 / 置顶服务</b><br>
			如果你想让更多人看到你的资料，可以购买 <b>首页置顶展示</b> 服务。<br>
			<b>只需 $50 美元 / 月</b>，你的信息卡会在首页更醒目位置展示，提升曝光与联系机会。<br>
			有兴趣的话请在下方留言，写上你的微信或邮箱，我会把详细流程发给你。
		</div>

		<?php if ($error_message): ?>
			<div class="error-message">
				<?php echo $error_message; ?>
			</div>
		<?php endif; ?>

		<?php if ($success_message): ?>
			<div class="success-message">
				<?php echo htmlspecialchars($success_message); ?>
			</div>
		<?php endif; ?>

		<form id="form-contact" method="post" action="">
			<input type="hidden" name="form_start_time" id="form_start_time" value="<?php echo time(); ?>" />

			<!-- Honeypot (hidden) -->
			<div style="display:none;">
				<label>Leave this field empty</label>
				<input type="text" name="website" value="" />
			</div>

			<div class="form-group">
				<label>你的名字: <span class="required">*</span></label>
				<input type="text" name="name" id="contact_name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required />
			</div>

			<div class="form-group contact-required-group">
				<label>至少要填一个或一个以上的联系方式: <span class="required">*</span></label>
			</div>

			<div class="form-group">
				<label>微信号码:</label>
				<input type="text" name="wechat" id="contact_wechat" value="<?php echo htmlspecialchars($_POST['wechat'] ?? ''); ?>" />
			</div>

			<div class="form-group">
				<label>电子邮箱:</label>
				<input type="email" name="email" id="contact_email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" />
			</div>

			<div class="form-group">
				<label>留言内容: <span class="required">*</span></label>
				<p class="form-hint">请尽量描述清楚你的问题或需求（例如：咨询置顶、广告合作、举报信息等）。</p>
				<textarea name="message" id="contact_message" rows="7" required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
			</div>

			<?php if ($is_localhost): ?>
				<div class="form-group">
					<div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 4px; padding: 12px; margin-bottom: 20px;">
						<p style="margin: 0; color: #856404; font-size: 14px;">
							<strong>开发模式:</strong> 在 localhost 上已跳过人机验证，生产环境将启用 Turnstile 验证。
						</p>
					</div>
				</div>
			<?php else: ?>
				<div class="form-group">
					<label>人机验证: <span class="required">*</span></label>
					<?php if ($use_turnstile): ?>
						<div class="cf-turnstile" data-sitekey="<?php echo htmlspecialchars($turnstile_site_key); ?>"></div>
						<p class="form-hint">请完成上面的验证以确认您是真人</p>
					<?php else: ?>
						<label class="human-verify-label">
							<input type="checkbox" name="human_verify" value="yes" required class="human-verify-checkbox" />
							<span class="human-verify-custom">
								<span class="human-verify-checkmark">✓</span>
							</span>
							<span class="human-verify-text">我不是机器人</span>
							<span class="required">*</span>
						</label>
						<p class="form-hint">请勾选此选项以验证您是真人</p>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<div class="form-group">
				<input type="submit" name="submit" value="提交" class="submit-btn" />
			</div>
		</form>
	</div>
</div>

<?php if (!$is_localhost && $use_turnstile): ?>
	<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
<?php endif; ?>

<script>
(function () {
  var form = document.getElementById('form-contact');
  if (!form) return;

  function trim(v) { return (v || '').replace(/^\s+|\s+$/g, ''); }

  form.addEventListener('submit', function (e) {
    var name = trim(document.getElementById('contact_name').value);
    var wechat = trim(document.getElementById('contact_wechat').value);
    var email = trim(document.getElementById('contact_email').value);
    var message = trim(document.getElementById('contact_message').value);

    // Basic checks
    var errs = [];

    if (!name) errs.push('必须填写你的名字!');
    if (message.length < 5) errs.push('留言内容太短了，请多写一点。');

    // At least one contact: wechat or email
    if (!wechat && !email) {
      errs.push('请至少填写一种联系方式（微信 / 邮箱）方便我回复你。');
    }

    // Lightweight format checks
    if (wechat && wechat.length < 4) {
      errs.push('微信号码格式不正确。');
    }

    if (email) {
      var emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
      if (!emailOk) errs.push('电子邮箱格式不正确。');
    }

    // Minimum time check (8s)
    var startEl = document.getElementById('form_start_time');
    if (startEl) {
      var start = parseInt(startEl.value || '0', 10);
      if (start > 0) {
        var spent = Math.floor(Date.now() / 1000) - start;
        if (spent < 8) errs.push('提交太快了，请花几秒钟认真填写后再提交。');
      }
    }

    // Turnstile: if widget exists, require token
    var turnstileInput = document.querySelector('input[name="cf-turnstile-response"]');
    if (turnstileInput && !trim(turnstileInput.value)) {
      errs.push('请完成人机验证!');
    }

    // Fallback checkbox
    var hv = document.querySelector('input[name="human_verify"]');
    if (hv && !hv.checked) {
      errs.push('请完成人机验证!');
    }

    if (errs.length) {
      e.preventDefault();
      alert(errs.join("\n"));
      return false;
    }
  });
})();
</script>

<?php include ROOT_PATH . '/templates/footer.php'; ?>
