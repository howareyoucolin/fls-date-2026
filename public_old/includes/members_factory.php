<?php if( ! defined('ROOT_PATH') ) die( 'Curiosity kills cat!' );

class Members_Factory{
	
	public function get_all_members(){
		
		$members = array();
		
		global $db;
		$results = $db->get_results("
			SELECT id 
			FROM cz_members
			ORDER BY id DESC
		");
		
		foreach( $results AS $result ){
			$members[] = new Member( $result->id );
		}
		
		return $members;
		
	}
	
	public function get_all_active_members(){
		
		$members = array();
		
		global $db;
		$results = $db->get_results("
			SELECT id 
			FROM cz_members
			ORDER BY id DESC
		");
		
		foreach( $results AS $result ){
			$members[] = new Member( $result->id );
		}
		
		return $members;
		
	}

	public function get_all_topped_members(){
		
		// Since we don't have a "topped" field in cz_members, return latest members
		$members = array();
		
		global $db;
		$results = $db->get_results("
			SELECT id 
			FROM cz_members
			ORDER BY id DESC
			LIMIT 20
		");
		
		foreach( $results AS $result ){
			$members[] = new Member( $result->id );
		}
		
		return $members;
		
	}

	public function get_all_featured_members(){
		
		// Since we don't have a "featured" field, return members with profile images
		$members = array();
		
		global $db;
		$results = $db->get_results("
			SELECT id 
			FROM cz_members
			WHERE profile_image IS NOT NULL AND profile_image != ''
			ORDER BY id DESC
			LIMIT 20
		");
		
		foreach( $results AS $result ){
			$members[] = new Member( $result->id );
		}
		
		return $members;
		
	}
	
}