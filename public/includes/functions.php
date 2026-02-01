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