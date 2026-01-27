<?php
/**
 * Simple router - maps clean URLs to page files
 */

define( 'ROOT_PATH', dirname(__FILE__) );

// Start session
if( session_status() == PHP_SESSION_NONE ){
    session_start();
}

// Load configuration
if( !file_exists( ROOT_PATH . '/config.php' ) ){
	die( 'Please create config.php from config.php.sample' );
}
require_once( ROOT_PATH . '/config.php' );
require_once( ROOT_PATH . '/includes/db.php' );
require_once( ROOT_PATH . '/includes/functions.php' );

// Initialize database
try {
	$db = new DB( DB_HOST, DB_NAME, DB_USERNAME, DB_PASSWORD );
	$GLOBALS['db'] = $db;
} catch( Exception $e ) {
	if( defined('DEBUG') && DEBUG ){
		die( 'Database error: ' . $e->getMessage() );
	}
	die( 'Database connection failed.' );
}

// Get the requested URI
$uri = trim( $_GET['uri'] ?? '', '/' );

// Map URIs to page files
$pages = [
	'' => 'home',
	'home' => 'home',
	'members' => 'members',
	'member' => 'member',
	'signup' => 'signup',
	'blog' => 'blog',
	'sitemap' => 'sitemap',
	'api' => 'api',
];

// Determine which page to load
$page = '404';
if( empty($uri) ){
	$page = 'home';
} elseif( isset($pages[$uri]) ){
	$page = $pages[$uri];
} elseif( preg_match('/^member\/(\d+)$/', $uri, $matches) ){
	$page = 'member';
	$_GET['id'] = $matches[1];
} elseif( preg_match('/^members\/page\/(\d+)$/', $uri, $matches) ){
	$page = 'members';
	$_GET['page'] = $matches[1];
} elseif( preg_match('/^signup\/thankyou$/', $uri) ){
	$page = 'signup-thankyou';
} elseif( preg_match('/^contacts\/thankyou$/', $uri) ){
	$page = 'contacts-thankyou';
} elseif( preg_match('/^blog\/page\/(\d+)$/', $uri, $matches) ){
	$page = 'blog';
	$_GET['page'] = $matches[1];
} elseif( preg_match('/^sitemap\.xml$/', $uri) ){
	$page = 'sitemap';
} elseif( preg_match('/^blog\/(.+)$/', $uri, $matches) ){
	// /blog/<post_name>
	$page = 'single';

	// IMPORTANT: <post_name> in prod is percent-encoded Chinese, decode it
	$_GET['post_name'] = urldecode($matches[1]);

	// optional: normalize trailing slash
	$_GET['post_name'] = trim($_GET['post_name'], "/ \t\n\r\0\x0B");
} elseif (preg_match('/^api\/(.+)$/', $uri, $matches)) {
	$page = 'api';
	$_GET['api_path'] = trim($matches[1], "/ \t\n\r\0\x0B");  
} else {
	// Try to find a page file directly
	$page_file = ROOT_PATH . '/pages/' . str_replace('/', '-', $uri) . '.php';
	if( file_exists($page_file) ){
		$page = str_replace('/', '-', $uri);
	}
}

// Load the page
$page_file = ROOT_PATH . '/pages/' . $page . '.php';
if( file_exists($page_file) ){
	require_once( $page_file );
} else {
	http_response_code(404);
	require_once( ROOT_PATH . '/pages/404.php' );
}
