<?php if( ! defined('ROOT_PATH') ) die( 'Curiosity kills cat!' );

$route = new Router();
$uri = $route->get_uri();

// Helper function to serve cached content or render controller
function serve_cached_or_render( $cache_file, $controller, Router $route ){
	if( file_exists( $cache_file ) ){
		echo file_get_contents( $cache_file );
	} else {
		try {
			$route->render( $controller );
		} catch( Exception $e ) {
			if( DEBUG ){
				die( 'Error rendering controller: ' . $e->getMessage() );
			}
			$route->render( '500' );
		}
	}
}

// Route definitions
$routes = array(
	'' => function() use ($route) {
		// Home page - render directly without cache
		try {
			$route->render( 'home' );
		} catch( Exception $e ) {
			if( DEBUG ){
				die( 'Error rendering controller: ' . $e->getMessage() );
			}
			$route->render( '500' );
		}
	},
	'members' => function() use ($route) {
		serve_cached_or_render( './cache/members.cache', 'members', $route );
	},
);

// Check exact matches first
if( isset( $routes[$uri] ) ){
	$routes[$uri]();
	exit(0);
}

// Check pattern matches
try {
	if( $route->matches( '/^member\/\d+$/' ) ){
		$route->render( 'member' );
		exit(0);
	} elseif( $route->matches( '/^blog$/' ) ){
		$route->render( 'blog' );
		exit(0);
	} elseif( $route->matches( '/^blog\/.+$/' ) ){
		$route->render( 'post' );
		exit(0);
	} elseif( $route->matches( '/^sitemap$/' ) ){
		$route->render( 'sitemap' );
		exit(0);
	} elseif( $route->matches( '/^sitemap\.xml$/' ) ){
		$route->render( 'sitemap.xml' );
		exit(0);
	} elseif( $route->matches( '/^signup$/' ) ){
		$route->render( 'signup' );
		exit(0);
	} elseif( $route->matches( '/^signup\/thankyou$/' ) ){
		$route->render( 'signup-thankyou' );
		exit(0);
	} elseif( $route->matches( '/^world-single-union$/' ) ){
		$route->render( 'page' );
		exit(0);
	} elseif( $route->matches( '/^profile\/update$/' ) ){
		$route->render( 'profile-update' );
		exit(0);
	} elseif( $route->matches( '/^login(\/\d+)?$/' ) ){
		$route->render( 'login' );
		exit(0);
	} elseif( $route->matches( '/^logout$/' ) ){
		$route->render( 'logout' );
		exit(0);
	} elseif( $route->matches( '/^account(\/saved)?$/' ) ){
		$route->render( 'account' );
		exit(0);
	} elseif( $route->matches( '/^500$/' ) ){
		$route->render( '500' );
		exit(0);
	} else {
		$route->render( '404' );
		exit(0);
	}
} catch( Exception $e ) {
	if( DEBUG ){
		die( 'Routing error: ' . $e->getMessage() . ' (URI: ' . $uri . ')' );
	}
	$route->render( '500' );
	exit(0);
}