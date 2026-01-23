<?php
// Signup page

$meta_title = '免费注册会员 - 纽约同城交友';
$meta_description = '免费注册成为会员，找到你的另一半';
$meta_keywords = '纽约婚介交友, 注册会员, 免费注册';

$error_message = '';
$success_message = '';

// Handle image upload
$uploaded_image = '';
if( isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK ){
	$file = $_FILES['profile_image'];
	$allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
	$max_size = 4 * 1024 * 1024; // 4MB
	
	// Validate file type
	if( !in_array($file['type'], $allowed_types) ){
		$error_message = '只允许上传图片文件 (jpg, jpeg, png, gif)!';
	} elseif( $file['size'] > $max_size ){
		$error_message = '图片文件太大了,超过了4MB的上传限制!';
	} else {
		// Create uploads directory if it doesn't exist
		$upload_dir = ROOT_PATH . '/uploads/';
		if( !file_exists($upload_dir) ){
			if( !@mkdir($upload_dir, 0755, true) ){
				$error_message = '无法创建上传目录，请检查服务器权限!';
			}
		}
		
		// Check if directory is writable
		if( !$error_message && !is_writable($upload_dir) ){
			$error_message = '上传目录不可写，请检查服务器权限!';
		}
		
		if( !$error_message ){
			// Generate unique filename
			$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
			$random_filename = md5(time() . rand()) . '.' . $extension;
			$target_file = $upload_dir . $random_filename;
			
			if( @move_uploaded_file($file['tmp_name'], $target_file) ){
				$uploaded_image = SITE_URL . '/uploads/' . $random_filename;
			} else {
				$error_message = '图片上传失败，请稍后再试!';
				if( defined('DEBUG') && DEBUG ){
					$error_message .= ' (错误代码: ' . $file['error'] . ')';
				}
			}
		}
	}
}

// Handle form submission
if( $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit']) ){
	global $db;
	
	$name = trim($_POST['name'] ?? '');
	$gender = $_POST['gender'] ?? '';
	$birth_year = (int)($_POST['birth_year'] ?? 0);
	$birth_month = (int)($_POST['birth_month'] ?? 0);
	$birth_day = (int)($_POST['birth_day'] ?? 0);
	$wechat = trim($_POST['wechat'] ?? '');
	$phone = trim($_POST['phone'] ?? '');
	$email = trim($_POST['email'] ?? '');
	$description = trim($_POST['description'] ?? '');
	$profile_image = $uploaded_image ?: trim($_POST['profile_image'] ?? '');
	
	// Validation
	$errors = [];
	
	if( empty($name) ){
		$errors[] = '必须填写你的名字!';
	}
	
	if( !in_array($gender, ['m', 'f']) ){
		$errors[] = '必须选择你的性别!';
	}
	
	if( $birth_year < 1970 || $birth_year > date('Y') - 18 || $birth_month < 1 || $birth_month > 12 || $birth_day < 1 || $birth_day > 31 ){
		$errors[] = '生日日期格式不正确!';
	} else {
		// Check if user is at least 18 years old
		$birthday = sprintf('%04d-%02d-%02d', $birth_year, $birth_month, $birth_day);
		$birthDate = new DateTime($birthday);
		$today = new DateTime();
		$age = $today->diff($birthDate)->y;
		
		if( $age < 18 ){
			$errors[] = '您必须年满18岁才能注册!';
		}
	}
	
	if( empty($wechat) && empty($phone) && empty($email) ){
		$errors[] = '必须填写微信号码或电话号码或电子邮箱其中之一个联系方式!';
	}
	
	if( !empty($wechat) && strlen($wechat) < 4 ){
		$errors[] = '微信号码格式不正确!';
	}
	
	if( !empty($phone) && !preg_match('/^\d{10,11}$/', $phone) ){
		$errors[] = '电话号码格式不正确!';
	}
	
	if( !empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL) ){
		$errors[] = '电子邮箱格式不正确!';
	}
	
	if( mb_strlen($description) < 40 ){
		$errors[] = '基本资料必须至少有40个字或以上!';
	}
	
	if( empty($errors) ){
		// Format birthday
		$birthday = sprintf('%04d-%02d-%02d', $birth_year, $birth_month, $birth_day);
		
		// Insert into database
		try {
			$sql = "INSERT INTO cz_members (title, gender, birthday, profile_image, wechat, phone, email, description, created_at) 
					VALUES ('" . $db->escape($name) . "', 
							'" . $db->escape($gender) . "', 
							'" . $db->escape($birthday) . "', 
							'" . $db->escape($profile_image) . "', 
							'" . $db->escape($wechat) . "', 
							'" . $db->escape($phone) . "', 
							'" . $db->escape($email) . "', 
							'" . $db->escape($description) . "', 
							NOW())";
			$db->query($sql);
			
			// Redirect to thank you page
			header('Location: ' . SITE_URL . '/signup/thankyou');
			exit;
		} catch( Exception $e ){
			$error_message = '注册失败，请稍后再试。';
			if( defined('DEBUG') && DEBUG ){
				$error_message .= ' ' . $e->getMessage();
			}
		}
	} else {
		$error_message = implode('<br>', $errors);
	}
}

include ROOT_PATH . '/templates/header.php';
?>

<div class="container">
	<div class="signup-page">
		<h2 class="signup-title">免费注册会员</h2>
		
		<?php if( $error_message ): ?>
			<div class="error-message">
				<?php echo $error_message; ?>
			</div>
		<?php endif; ?>
		
		<?php if( $success_message ): ?>
			<div class="success-message">
				<?php echo $success_message; ?>
			</div>
		<?php endif; ?>
		
		<form id="form-signup" method="post" action="" enctype="multipart/form-data">
			<div class="form-group">
				<label>名字: <span class="required">*</span></label>
				<input type="text" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required />
			</div>
			
			<div class="form-group">
				<label>性别: <span class="required">*</span></label>
				<div class="radio-group">
					<label><input type="radio" name="gender" value="m" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'm') ? 'checked' : ''; ?> required> 男生</label>
					<label><input type="radio" name="gender" value="f" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'f') ? 'checked' : ''; ?>> 女生</label>
				</div>
			</div>
			
			<div class="form-group">
				<label>生日日期: <span class="required">*</span></label>
				<div class="date-selectors">
					<select name="birth_year" required>
						<option value="">年</option>
						<?php for($i = date('Y') - 18; $i >= 1970; $i--): ?>
							<option value="<?php echo $i; ?>" <?php echo (isset($_POST['birth_year']) && $_POST['birth_year'] == $i) ? 'selected' : ''; ?>><?php echo $i; ?></option>
						<?php endfor; ?>
					</select>
					<select name="birth_month" required>
						<option value="">月</option>
						<?php for($i = 1; $i <= 12; $i++): ?>
							<option value="<?php echo $i; ?>" <?php echo (isset($_POST['birth_month']) && $_POST['birth_month'] == $i) ? 'selected' : ''; ?>><?php echo $i; ?></option>
						<?php endfor; ?>
					</select>
					<select name="birth_day" required>
						<option value="">日</option>
						<?php for($i = 1; $i <= 31; $i++): ?>
							<option value="<?php echo $i; ?>" <?php echo (isset($_POST['birth_day']) && $_POST['birth_day'] == $i) ? 'selected' : ''; ?>><?php echo $i; ?></option>
						<?php endfor; ?>
					</select>
				</div>
			</div>
			
			<div class="form-group">
				<label>上传头像:</label>
				<div class="image-upload-wrapper">
					<div id="image-preview" class="image-preview">
						<img id="preview-img" src="" alt="Preview" style="display: none;" />
						<span id="upload-text" class="upload-text">点击选择图片</span>
						<span id="upload-close" class="upload-close" style="display: none;">&times;</span>
					</div>
					<input type="file" id="profile_image" name="profile_image" accept="image/jpeg,image/jpg,image/png,image/gif" style="display: none;" />
					<input type="hidden" name="profile_image_url" id="profile_image_url" value="<?php echo htmlspecialchars($_POST['profile_image_url'] ?? ''); ?>" />
					<div id="upload-message" class="upload-message"></div>
				</div>
			</div>
			
			<div class="form-group contact-required-group">
				<label>至少要填一个或一个以上的联系方式: <span class="required">*</span></label>
			</div>
			
			<div class="form-group">
				<label>微信号码:</label>
				<input type="text" name="wechat" value="<?php echo htmlspecialchars($_POST['wechat'] ?? ''); ?>" />
			</div>
			
			<div class="form-group">
				<label>电话号码:</label>
				<input type="text" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" maxlength="11" />
			</div>
			
			<div class="form-group">
				<label>电子邮箱:</label>
				<input type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" />
			</div>
			
			<div class="form-group">
				<label>基本资料: <span class="required">*</span></label>
				<p class="form-hint">请尽量填详细的信息：现居地(精确到区，例如纽约法拉盛), 来自哪个城市，职业，兴趣爱好，来美多久，学历，婚姻状况，信仰，身高，体重，三观等等...</p>
				<textarea name="description" rows="8" required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
				<p class="char-count">至少需要40个字</p>
			</div>
			
			
			<div class="form-group">
				<input type="submit" name="submit" value="提交" class="submit-btn" />
			</div>
		</form>
	</div>
</div>

<script>
// Auto-correct phone number (numbers only, max 11 digits)
document.querySelector('input[name="phone"]').addEventListener('input', function(e){
	this.value = this.value.replace(/\D+/g, '').substring(0, 11);
});

// Adjust days in month based on year and month
function updateDaysInMonth(){
	var year = parseInt(document.querySelector('select[name="birth_year"]').value) || new Date().getFullYear();
	var month = parseInt(document.querySelector('select[name="birth_month"]').value) || 1;
	var daySelect = document.querySelector('select[name="birth_day"]');
	var selectedDay = parseInt(daySelect.value) || 1;
	
	var daysInMonth = new Date(year, month, 0).getDate();
	
	// Update options
	var currentOptions = daySelect.querySelectorAll('option');
	var currentMaxDay = currentOptions.length - 1; // Subtract 1 for the "日" option
	
	if( daysInMonth != currentMaxDay ){
		// Remove all but first option
		while(daySelect.options.length > 1){
			daySelect.remove(1);
		}
		
		// Add new options
		for(var i = 1; i <= daysInMonth; i++){
			var option = document.createElement('option');
			option.value = i;
			option.textContent = i;
			if( i == selectedDay && i <= daysInMonth ){
				option.selected = true;
			}
			daySelect.appendChild(option);
		}
	}
}

document.querySelector('select[name="birth_year"]').addEventListener('change', updateDaysInMonth);
document.querySelector('select[name="birth_month"]').addEventListener('change', updateDaysInMonth);

// Character count for description
var descriptionTextarea = document.querySelector('textarea[name="description"]');
var charCount = document.querySelector('.char-count');

descriptionTextarea.addEventListener('input', function(){
	var length = this.value.length;
	charCount.textContent = '已输入 ' + length + ' 字 (至少需要40个字)';
	if( length >= 40 ){
		charCount.style.color = '#4CAF50';
	} else {
		charCount.style.color = '#D72171';
	}
});

// Validate at least one contact method is filled
var contactFields = {
	wechat: document.querySelector('input[name="wechat"]'),
	phone: document.querySelector('input[name="phone"]'),
	email: document.querySelector('input[name="email"]')
};

function validateContactMethods(){
	var wechat = contactFields.wechat.value.trim();
	var phone = contactFields.phone.value.trim();
	var email = contactFields.email.value.trim();
	
	return wechat.length > 0 || phone.length > 0 || email.length > 0;
}

function showContactError(message){
	var existingError = document.querySelector('.contact-error');
	if( existingError ){
		existingError.remove();
	}
	
	var errorDiv = document.createElement('div');
	errorDiv.className = 'contact-error';
	errorDiv.style.cssText = 'color: #D72171; font-size: 12px; margin-top: 5px;';
	errorDiv.textContent = message;
	
	// Insert after the contact required label
	var contactGroup = document.querySelector('.contact-required-group');
	if( contactGroup ){
		contactGroup.appendChild(errorDiv);
	}
}

// Validate age (must be 18 or older)
function validateAge(){
	var year = parseInt(document.querySelector('select[name="birth_year"]').value);
	var month = parseInt(document.querySelector('select[name="birth_month"]').value);
	var day = parseInt(document.querySelector('select[name="birth_day"]').value);
	
	if( !year || !month || !day ){
		return { valid: false, message: '请选择完整的生日日期!' };
	}
	
	var birthDate = new Date(year, month - 1, day);
	var today = new Date();
	var age = today.getFullYear() - birthDate.getFullYear();
	var monthDiff = today.getMonth() - birthDate.getMonth();
	
	if( monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate()) ){
		age--;
	}
	
	if( age < 18 ){
		return { valid: false, message: '您必须年满18岁才能注册!' };
	}
	
	return { valid: true };
}

function showAgeError(message){
	var existingError = document.querySelector('.age-error');
	if( existingError ){
		existingError.remove();
	}
	
	var errorDiv = document.createElement('div');
	errorDiv.className = 'age-error';
	errorDiv.style.cssText = 'color: #D72171; font-size: 12px; margin-top: 5px;';
	errorDiv.textContent = message;
	
	var ageGroup = document.querySelector('select[name="birth_year"]').closest('.form-group');
	if( ageGroup ){
		ageGroup.appendChild(errorDiv);
	}
}

// Add validation on form submit
document.getElementById('form-signup').addEventListener('submit', function(e){
	var hasError = false;
	
	// Validate contact methods
	if( !validateContactMethods() ){
		e.preventDefault();
		showContactError('必须填写微信号码或电话号码或电子邮箱其中之一个联系方式!');
		hasError = true;
		
		// Scroll to contact section
		contactFields.wechat.scrollIntoView({ behavior: 'smooth', block: 'center' });
		contactFields.wechat.focus();
	}
	
	// Validate age
	var ageValidation = validateAge();
	if( !ageValidation.valid ){
		e.preventDefault();
		showAgeError(ageValidation.message);
		hasError = true;
		
		if( !hasError ){
			// Scroll to age section
			document.querySelector('select[name="birth_year"]').scrollIntoView({ behavior: 'smooth', block: 'center' });
			document.querySelector('select[name="birth_year"]').focus();
		}
	}
	
	return !hasError;
});

// Clear age error when user changes date
document.querySelector('select[name="birth_year"]').addEventListener('change', function(){
	var error = document.querySelector('.age-error');
	if( error ){
		var ageValidation = validateAge();
		if( ageValidation.valid ){
			error.remove();
		}
	}
});

document.querySelector('select[name="birth_month"]').addEventListener('change', function(){
	var error = document.querySelector('.age-error');
	if( error ){
		var ageValidation = validateAge();
		if( ageValidation.valid ){
			error.remove();
		}
	}
});

document.querySelector('select[name="birth_day"]').addEventListener('change', function(){
	var error = document.querySelector('.age-error');
	if( error ){
		var ageValidation = validateAge();
		if( ageValidation.valid ){
			error.remove();
		}
	}
});

// Clear error when user starts typing in any contact field
Object.values(contactFields).forEach(function(field){
	if( field ){
		field.addEventListener('input', function(){
			var error = document.querySelector('.contact-error');
			if( error && validateContactMethods() ){
				error.remove();
			}
		});
	}
});

// Image upload handling
var imagePreview = document.getElementById('image-preview');
var previewImg = document.getElementById('preview-img');
var uploadText = document.getElementById('upload-text');
var fileInput = document.getElementById('profile_image');
var uploadClose = document.getElementById('upload-close');
var uploadMessage = document.getElementById('upload-message');
var profileImageUrl = document.getElementById('profile_image_url');

if( imagePreview && fileInput ){
	// Click preview to trigger file input
	imagePreview.addEventListener('click', function(e){
		// Don't trigger if clicking the close button
		if( e.target !== uploadClose ){
			fileInput.click();
		}
	});

	// Handle file selection
	fileInput.addEventListener('change', function(e){
		var file = e.target.files[0];
		if( !file ){
			return;
		}
		
		// Validate file type
		var allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
		if( !allowedTypes.includes(file.type) ){
			uploadMessage.textContent = '只允许上传图片文件 (jpg, jpeg, png, gif)!';
			uploadMessage.className = 'upload-message error';
			return;
		}
		
		// Validate file size (4MB)
		if( file.size > 4 * 1024 * 1024 ){
			uploadMessage.textContent = '图片文件太大了,超过了4MB的上传限制!';
			uploadMessage.className = 'upload-message error';
			return;
		}
		
		// Show preview
		var reader = new FileReader();
		reader.onload = function(e){
			previewImg.src = e.target.result;
			previewImg.style.display = 'block';
			if( uploadText ){
				uploadText.style.display = 'none';
			}
			if( uploadClose ){
				uploadClose.style.display = 'block';
			}
			imagePreview.classList.add('has-image');
			uploadMessage.textContent = '';
			uploadMessage.className = '';
		};
		reader.readAsDataURL(file);
	});

	// Remove/change image
	if( uploadClose ){
		uploadClose.addEventListener('click', function(e){
			e.stopPropagation();
			previewImg.src = '';
			previewImg.style.display = 'none';
			if( uploadText ){
				uploadText.style.display = 'block';
			}
			uploadClose.style.display = 'none';
			imagePreview.classList.remove('has-image');
			fileInput.value = '';
			if( profileImageUrl ){
				profileImageUrl.value = '';
			}
			uploadMessage.textContent = '';
			uploadMessage.className = '';
		});
	}
}
</script>

<?php include ROOT_PATH . '/templates/footer.php'; ?>
