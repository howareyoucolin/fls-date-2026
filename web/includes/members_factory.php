<?php if( ! defined('ROOT_PATH') ) die( 'Curiosity kills cat!' );

class Members_Factory{
	
	public function get_all_members(){
		
		$members = array();
		
		global $db;
		$results = $db->get_results("
			SELECT ID 
			FROM wp_posts
			WHERE post_type = 'member'
			ORDER BY post_date DESC
		");
		
		foreach( $results AS $result ){
			$members[] = new Member( $result->ID );
		}
		
		return $members;
		
	}
	
	public function get_all_active_members(){
		
		$members = array();
		
		global $db;
		$results = $db->get_results("
			SELECT ID 
			FROM wp_posts
			WHERE post_type = 'member' AND post_status = 'publish'
			ORDER BY post_date DESC
		");
		
		foreach( $results AS $result ){
			$memberObject = new Member( $result->ID );
			if($memberObject->is_approved()) $members[] = $memberObject;
		}
		
		return $members;
		
	}

	public function get_all_topped_members(){
		
		$members = array();
		
		global $db;
		$results = $db->get_results("
			SELECT ID 
			FROM wp_posts
			WHERE post_type = 'member' AND post_status = 'publish'
			ORDER BY post_date DESC
		");
		
		foreach( $results AS $result ){
			$memberObject = new Member( $result->ID );
			if($memberObject->is_topped()) $members[] = $memberObject;
		}
		
		return $members;
		
	}

	public function get_all_featured_members(){
		
		$members = array();
		
		global $db;
		$results = $db->get_results("
			SELECT ID 
			FROM wp_posts
			WHERE post_type = 'member' AND post_status = 'publish'
			ORDER BY post_date DESC
		");
		
		foreach( $results AS $result ){
			$memberObject = new Member( $result->ID );
			if($memberObject->is_featured()) $members[] = $memberObject;
		}
		
		return $members;
		
	}
	
}