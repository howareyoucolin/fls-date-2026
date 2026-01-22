<?php if( ! defined('ROOT_PATH') ) die( 'Curiosity kills cat!' );

class Authorizer{
	
	const SESSION_EXPIRE_TIME = 3600; // 1 hour
	
	const LOGIN_STATUS_LOGGED_IN = 1;
	const LOGIN_STATUS_NOT_LOGGED_IN = 0;
	const LOGIN_STATUS_EXPIRED = -1;
	
	private $db;
	
	public function __construct(DB $db){
		$this->db = $db;
	}
	
	public function check_login_credential($username, $password){
		
		$password_hash = md5( $password );
		$username = trim( $username );
		
		$member_id = $this->db->get_var(
			$this->db->prepare("
				SELECT A.post_id FROM
					(SELECT post_id FROM wp_postmeta 
					WHERE meta_value = '%s0' AND meta_key = 'password') AS A
				JOIN
					(SELECT post_id FROM wp_postmeta 
					WHERE 
						(meta_value = '%s1' AND meta_key = 'email') OR
						(meta_value = '%s1' AND meta_key = 'wechat') OR
						(meta_value = '%s1' AND meta_key = 'phone')
					) AS B
				ON A.post_id = B.post_id
				LIMIT 1", $password_hash, $username )
		);
		
		if( $member_id ){
			$this->set_session( $member_id, $password_hash );
			return $member_id;
		}
		
		return false;
		
	}
	
	/**
	 * Get login status
	 * @return int 1 = logged in, 0 = not logged in, -1 = expired
	 */
	public function get_login_status(){
		
		if( !$this->has_session() ){
			return self::LOGIN_STATUS_NOT_LOGGED_IN;
		}
		
		if( $_SESSION['expire'] < time() ){
			return self::LOGIN_STATUS_EXPIRED;
		}
		
		if( $this->is_authorized( $_SESSION['member_id'], $_SESSION['password'] ) ){
			return self::LOGIN_STATUS_LOGGED_IN;
		}
		
		return self::LOGIN_STATUS_NOT_LOGGED_IN;
		
	}

	public function is_authorized($id, $password){

		return (bool) $this->db->get_var(
			$this->db->prepare("
				SELECT EXISTS(
					SELECT 1 FROM wp_postmeta
					WHERE post_id = '%s0' 
					AND (meta_key = 'password' OR meta_key = 'backup_password') 
					AND meta_value = '%s1'
				)", $id, $password )
		);

	}
	
	public function unset_login_sessions(){
		
		unset( $_SESSION['password'], $_SESSION['member_id'], $_SESSION['expire'] );
		
	}
	
	private function set_session($member_id, $password_hash){
		$_SESSION['password'] = $password_hash;
		$_SESSION['member_id'] = $member_id;
		$_SESSION['expire'] = time() + self::SESSION_EXPIRE_TIME;
	}
	
	private function has_session(){
		return isset( $_SESSION['password'], $_SESSION['member_id'], $_SESSION['expire'] );
	}
	
}