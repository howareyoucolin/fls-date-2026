<?php if( ! defined('ROOT_PATH') ) die( 'Curiosity kills cat!' );

global $db;
$auth = new Authorizer( $db );
$auth->unset_login_sessions();

// 202 means accepted
header( 'Location: ' . SITE_URL . '/login/202' );
exit(0);