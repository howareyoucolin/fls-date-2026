<?php
/**
 * Helper functions
 */

function get_members($limit = 20, $offset = 0){
	global $db;
	$limit = (int)$limit;
	$offset = (int)$offset;
	
	$results = $db->get_results("
		SELECT * 
		FROM cz_members
		WHERE is_archived = 0
		ORDER BY id DESC
		LIMIT $limit OFFSET $offset
	");
	
	return $results ?: [];
}

function get_member($id){
	global $db;
	$id = (int)$id;
	
	return $db->get_row("
		SELECT * 
		FROM cz_members
		WHERE id = $id
	");
}

function get_profile_image_url($member){
	if( !empty($member->profile_image) ){
		return htmlspecialchars($member->profile_image);
	}
	return '//www.flushingdating.com/assets/images/nobody.png';
}

function get_member_url($id){
	return SITE_URL . '/member/' . (int)$id;
}

function calculate_age($birthday){
	if( empty($birthday) || $birthday == '0000-00-00' ){
		return '';
	}
	
	$birth = new DateTime($birthday);
	$today = new DateTime();
	$age = $today->diff($birth);
	return $age->y;
}

function truncate_text($text, $length = 100){
	if( mb_strlen($text) <= $length ){
		return $text;
	}
	return mb_substr($text, 0, $length) . '...';
}

function get_gender_display($gender){
	if( $gender == 'm' ){
		return '男生';
	} elseif( $gender == 'f' ){
		return '女生';
	} elseif( !empty($gender) ){
		return htmlspecialchars($gender);
	}
	return '';
}

function get_members_count(){
	global $db;
	return (int)$db->get_var("SELECT COUNT(*) FROM cz_members WHERE is_archived = 0");
}

// --- Latest Posts (from wp_posts) ---

function get_latest_wp_posts($limit = 10){
	global $db;

	$limit = (int)$limit;
	if ($limit <= 0) $limit = 10;

	$sql = "
		SELECT ID, post_title, post_date, post_content, post_name
		FROM wp_posts
		WHERE post_status = 'publish'
			AND post_type = 'post'
		ORDER BY post_date DESC
		LIMIT {$limit}
	";

	return $db->get_results($sql);
}

function wp_excerpt($html, $maxLen = 160){
	$text = trim(html_entity_decode(strip_tags($html), ENT_QUOTES, 'UTF-8'));
	$text = preg_replace('/\s+/u', ' ', $text);

	if (mb_strlen($text, 'UTF-8') <= $maxLen) return $text;
	return mb_substr($text, 0, $maxLen, 'UTF-8') . '...';
}

function send_telegram_message($text, $chat_id = null){
	$bot_token = defined('TELEGRAM_BOT_TOKEN') ? TELEGRAM_BOT_TOKEN : getenv('TELEGRAM_BOT_TOKEN');
	if( !$bot_token ){
		return false;
	}

	$default_chat_id = defined('TELEGRAM_CHAT_ID') ? TELEGRAM_CHAT_ID : getenv('TELEGRAM_CHAT_ID');
	$target_chat_id = $chat_id ?: $default_chat_id;
	if( !$target_chat_id ){
		return false;
	}

	$url = 'https://api.telegram.org/bot' . rawurlencode($bot_token) . '/sendMessage';
	$post_fields = http_build_query([
		'chat_id' => $target_chat_id,
		'text' => $text,
	], '', '&');

	// Prefer cURL when available, fallback to streams.
	if( function_exists('curl_init') ){
		$ch = curl_init($url);
		curl_setopt_array($ch, [
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $post_fields,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 10,
			CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
		]);
		$response = curl_exec($ch);
		$http_code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		return $response !== false && $http_code >= 200 && $http_code < 300;
	}

	$context = stream_context_create([
		'http' => [
			'method' => 'POST',
			'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
			'content' => $post_fields,
			'timeout' => 10,
		],
	]);
	$response = @file_get_contents($url, false, $context);
	return $response !== false;
}
