<?php

namespace Lumiart\ImageEditCachebuster\Controllers;

/**
 * Singleton Class AddImageVerToURL
 * Hook into url generation for resized images and add cachebuster
 * @package Lumiart\ImageEditCachebuster\Controllers
 */
class AddImageVerToURL {

	/**
	 * @return AddImageVerToURL
	 */
	public static function getInstance()
	{
		static $instance = null;
		if (null === $instance) {
			$instance = new static();
		}

		return $instance;
	}

	/**
	 * WP hooks
	 */
	private function __construct() {

		add_filter( 'image_downsize', array( $this, 'customImageDownsizeFunction' ), 10, 3 );

	}

	/**
	 * Modified function from wp-includes/media.php -> function image_downsize
	 * @param $output mixed anything than false will trigger function overwrite
	 * @param $id int Attachment id
	 * @param $size string Intermidiate size name
	 *
	 * @return array|bool
	 */
	public function customImageDownsizeFunction( $output, $id, $size ) {

		$img_url = wp_get_attachment_url($id);
		$meta = wp_get_attachment_metadata($id);
		$width = $height = 0;
		$is_intermediate = false;
		$img_url_basename = wp_basename($img_url);

		// try for a new style intermediate size
		if ( $intermediate = image_get_intermediate_size($id, $size) ) {
			$img_url = str_replace($img_url_basename, $intermediate['file'], $img_url);
			/**
			 * Our Modification
			 */
			$img_url = $this->addCachebuster( $img_url, $id );
			$width = $intermediate['width'];
			$height = $intermediate['height'];
			$is_intermediate = true;
		}
		elseif ( $size == 'thumbnail' ) {
			// fall back to the old thumbnail
			if ( ($thumb_file = wp_get_attachment_thumb_file($id)) && $info = getimagesize($thumb_file) ) {
				$img_url = str_replace($img_url_basename, wp_basename($thumb_file), $img_url);
				$width = $info[0];
				$height = $info[1];
				$is_intermediate = true;
			}
		}
		if ( !$width && !$height && isset( $meta['width'], $meta['height'] ) ) {
			// any other type: use the real image
			$width = $meta['width'];
			$height = $meta['height'];
		}

		if ( $img_url) {
			// we have the actual image size, but might need to further constrain it if content_width is narrower
			list( $width, $height ) = image_constrain_size_for_editor( $width, $height, $size );

			return array( $img_url, $width, $height, $is_intermediate );
		}
		return false;

	}

	/**
	 * Add cachebuster to url
	 * @param $img_url string Resized image URL
	 * @param $id int Attachment ID
	 *
	 * @return string URL with cachebuster (or not, if there is none)
	 */
	private function addCachebuster( $img_url, $id ) {
		$image_ver = get_post_meta( $id, LUMIART__IMAGEEDITCACHEBUSTER__IMAGE_VER_META_NAME, true );
		if( !empty( $image_ver ) ) {
			return( add_query_arg( 'imgver', $image_ver, $img_url ) );
		}

		return $img_url;
	}

}