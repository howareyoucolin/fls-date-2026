<?php if( ! defined('ROOT_PATH') ) die( 'Curiosity kills cat!' );

class Member{

	private $id = null;
	private $data = null;

	public function __construct($id){
		
		$this->id = $id;
		
	}
	
	public function exists(){
		
		global $db;
		
		$result = $db->get_var(
			$db->prepare("
				SELECT id 
				FROM cz_members 
				WHERE id = '%s0'
			", $this->id )
		);
		
		return (bool) $result;
		
	}
	
	public function is_active(){
		
		// All members in cz_members are considered active
		return $this->exists();
		
	}
	
	public function get_data(){
		
		global $db;

		$member_data = $db->get_row(
			$db->prepare("
				SELECT * 
				FROM cz_members
				WHERE id = '%s0'
				", $this->id
			)
		);
		
		if( !$member_data ){
			return $this->data = new stdClass();
		}
		
		// Map new table structure to old structure for compatibility
		$results = new stdClass();
		
		// Direct mappings
		$results->id = $member_data->id;
		$results->title = $member_data->title;
		$results->gender = $member_data->gender;
		$results->wechat = $member_data->wechat;
		$results->profile_image = $member_data->profile_image;
		$results->description = $member_data->description;
		$results->birthday = $member_data->birthday;
		$results->created_at = $member_data->created_at;
		$results->updated_at = $member_data->updated_at;
		
		// Legacy mappings for compatibility
		$results->post_title = $member_data->title;
		$results->post_content = $member_data->description;
		
		// Split description back into about_me and preference for methods that need them
		if( $member_data->description ){
			$parts = explode("\n", $member_data->description, 2);
			$results->about_me = $parts[0] ?? '';
			$results->preference = $parts[1] ?? '';
		} else {
			$results->about_me = '';
			$results->preference = '';
		}
		
		return $this->data = $results;
		
	}
	
	public function get_var($key){
		
		//Init data only once.
		if( $this->data === null ){
			$this->get_data();
		}
		
		if( isset($this->data->{$key}) ){
			
			//Post_content and description are exceptions with no htmlentities escaping.
			if( $key == 'post_content' || $key == 'description' ){
				return trim($this->data->{$key});
			}
			
			return htmlentities( trim($this->data->{$key}) );
		}
		
		return false;
		
	}

	public function get_age(){
		
		$birthday = $this->get_var('birthday');
		
		if( !$birthday ){
			return 'N/A';
		}
		
		// birthday is in DATE format (YYYY-MM-DD)
		$birth_timestamp = strtotime($birthday);
		if( !$birth_timestamp ){
			return 'N/A';
		}
		
		$birth_date = new DateTime($birthday);
		$today = new DateTime();
		$age = $today->diff($birth_date)->y;
		
		return $age;
		
	}

	public function get_super_title(){
		
		return $this->get_var('super_title') ? $this->get_var('super_title') : $this->get_var('post_title');
		
	}
	
	public function get_title(){
		
		return $this->get_var('title');
		
	}

	public function is_approved(){
		
		// All members in cz_members are considered approved
		return true;
		
	}

	public function is_featured(){
		
		// Consider featured if they have a profile image
		return !empty($this->get_var('profile_image'));
		
	}

	public function is_topped(){
		
		// For now, all members can be considered "topped" (latest members)
		// You can add a "topped" column to cz_members later if needed
		return true;
		
	}
	
	public function get_gender(){
		
		return $this->get_var('gender') == 'm' ? '男生' : '女生';
		
	}
	
	public function get_opposite_gender(){
		
		return $this->get_var('gender') == 'f' ? '男生' : '女生';
		
	}
	
	public function get_wechat(){
		
		return $this->get_var('wechat');
		
	}
	
	public function get_email(){
		
		return $this->get_var('email');
		
	}
	
	public function get_phone(){
		
		return $this->get_var('phone');
		
	}
	
	public function get_intro(){

		// Use description (which contains about_me + preference)
		$content = $this->get_var('description');
		
		// If description is empty, try about_me (from split)
		if( !$content ){
			$content = $this->get_var('about_me');
		}

		if( ! $content ){
			return false;
		}
		
		return mb_substr( $content, 0, 128 );
		
	}
	
	public function get_content(){

		return $this->get_var('description');

	}
	
	public function get_about_me(){
		
		$content = $this->get_var('about_me');
		
		if( ! $content ){
			return false;
		}
		
		return nl2br( $content );
		
	}
	
	public function get_preference(){
		
		$content = $this->get_var('preference');
		
		if( ! $content ){
			return false;
		}
		
		return nl2br( $content );
		
	}
	
	public function get_description(){
		
		$content = $this->get_var('description');
		
		if( ! $content ){
			return false;
		}
		
		return nl2br( $content );
		
	}
	
	public function get_last_modified(){
		
		return $this->get_var('updated_at');
		
	}

	public function get_url(){
		
		return SITE_URL . '/member/' . $this->id;
		
	}
	
	public function get_profile_image_url(){
		
		$profile_image = $this->get_var('profile_image');
		
		if( $profile_image ){
			return str_replace('http://', 'https://', $profile_image);//Make sure image is loaded as HTTPS.
		}
		
		return DEFAULT_SILHOUETTE;
		
	}
	
	public function get_suggestions(){
		
		if( $this->get_var('gender') === 'm' ){
			$suggestions = array(16,21,126,108);
		}
		else{
			$suggestions = array(94,98,111,114);
		}
		
		foreach($suggestions as $key => $value){
			if($this->id === $value){
				unset($suggestions[$key]);
				return $suggestions;
			}
		}
		
		//Default
		unset($suggestions[3]);
		return $suggestions;
		
	}

}