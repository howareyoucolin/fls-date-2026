<?php
 
define( 'VERSION', '1.0.1' );
define( 'ROOT_PATH', dirname(__FILE__) );

// Start session
if( session_status() == PHP_SESSION_NONE ){
    session_start();
}

// Load configuration
require_once( ROOT_PATH . '/config.php' );
require_once( ROOT_PATH . '/constants.php' );

// Set error reporting based on ERROR_REPORTING constant (or DEBUG as fallback)
$error_reporting = defined('ERROR_REPORTING') ? ERROR_REPORTING : DEBUG;
if( $error_reporting ){
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
} else {
	ini_set('display_errors', 0);
	ini_set('display_startup_errors', 0);
	error_reporting(0);
}

// Load core classes
require_once( ROOT_PATH . '/includes/router.php' );
require_once( ROOT_PATH . '/includes/db.php' );
require_once( ROOT_PATH . '/includes/members_factory.php' );
require_once( ROOT_PATH . '/includes/member.php' );
require_once( ROOT_PATH . '/includes/post.php' );
require_once( ROOT_PATH . '/includes/images.php' );

// Initialize database connection
try {
	$db = new DB( DB_HOST, DB_NAME, DB_USERNAME, DB_PASSWORD );
} catch( Exception $e ) {
	if( DEBUG ){
		die( 'Database connection error: ' . $e->getMessage() );
	} else {
		die( 'Database connection failed. Please contact the administrator.' );
	}
}

// Make $db available globally for backward compatibility
// TODO: Refactor to remove global dependency
$GLOBALS['db'] = $db;

// Load routes
require_once( ROOT_PATH . '/routes.php' );

exit(0);