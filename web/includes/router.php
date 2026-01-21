<?php if( ! defined('ROOT_PATH') ) die( 'Curiosity kills cat!' );

class Router{
	
	private $uri = null;
	private $url = null;
	private $segments = null;
	
	public function __construct(){
		
		$this->uri = trim( $_GET['uri'] ?? '' );
		$this->url = rtrim( SITE_URL . '/' . $this->uri, '/' );
		
		// Remove trailing slash for SEO purposes.
		if( str_ends_with( $this->uri, '/' ) && $this->uri !== '' ){
			header( 'Location: ' . $this->url );
			exit(0);
		}
		
		$this->segments = $this->uri ? explode( '/', $this->uri ) : array();
		
	}
	
	public function get_uri(){
		return $this->uri;
	}
	
	public function get_url(){
		return $this->url;
	}
	
	public function get_segment($n){
		return $this->segments[$n] ?? false;
	}
	
	public function get_segments(){
		return $this->segments;
	}
	
	public function render($page){
		$controller_path = ROOT_PATH . '/controllers/' . $page . '.php';
		
		if( !file_exists( $controller_path ) ){
			throw new Exception( 'Controller not found: ' . $page );
		}
		
		require_once( $controller_path );
	}
	
	public function matches($pattern){
		return preg_match( $pattern, $this->uri ) === 1;
	}
	
}