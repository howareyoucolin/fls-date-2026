<?php if( ! defined('ROOT_PATH') ) die( 'Curiosity kills cat!' );

// Validate input
$username = isset( $_POST['username'] ) ? trim( $_POST['username'] ) : '';
$password = isset( $_POST['password'] ) ? trim( $_POST['password'] ) : '';

if( empty( $username ) || empty( $password ) ){
	header( 'Location: ' . SITE_URL . '/login/403' );
	exit(0);
}

// Authenticate user
try {
	global $db;
	$auth = new Authorizer( $db );
	$member_id = $auth->check_login_credential( $username, $password );
	
	if( $member_id ){
		header( 'Location: ' . SITE_URL . '/account' );
		exit(0);
	} else {
		header( 'Location: ' . SITE_URL . '/login/403' );
		exit(0);
	}
} catch( Exception $e ) {
	// Log error in production, show message in debug
	if( DEBUG ){
		die( 'Login error: ' . $e->getMessage() );
	}
	header( 'Location: ' . SITE_URL . '/login/403' );
	exit(0);
}