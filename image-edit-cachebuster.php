<?php
/*
 * Plugin name: Image Edit Cachebusting
 * Description: Add cachebusting to all resized images, when they are modified (for example recroped witch My Eyes Are Up Here plugin)
 * Version: 1.0
 * Author: Jakub Klapka
 * Author URI: https://klapka.lumiart.cz
 */

/**
 * Classes Autoloading
 */
spl_autoload_register( function( $class_name ){
	if( strpos( $class_name, 'Lumiart\\ImageEditCachebuster\\Controllers\\' ) !== false ) {
		include_once( __DIR__ . '/controllers/' . str_replace( 'Lumiart\\ImageEditCachebuster\\Controllers\\', '', $class_name ) . '.php' );
	}
} );


/**
 * Config
 */
/** Name of postmeta name for image versions */
define( 'LUMIART__IMAGEEDITCACHEBUSTER__IMAGE_VER_META_NAME', '_lumiart__imageeditcachebuster__image_version' );


/**
 * Initialize controllers
 */
add_action( 'init', function() {

	\Lumiart\ImageEditCachebuster\Controllers\AddImageVerToURL::getInstance();

	//Assuming, that only request from user with edit permission can modify images
	if( current_user_can( 'edit_posts' ) ) {
		\Lumiart\ImageEditCachebuster\Controllers\RegisterImageEdit::getInstance();
	}

} );