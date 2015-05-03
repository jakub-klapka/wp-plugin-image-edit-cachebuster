<?php

namespace Lumiart\ImageEditCachebuster\Controllers;

/**
 * Singleton Class RegisterImageEdit
 * Will handle adding of image versions when appropriate
 * @package Lumiart\ImageEditCachebuster\Controllers
 */
class RegisterImageEdit {

	private $version_meta_name = LUMIART__IMAGEEDITCACHEBUSTER__IMAGE_VER_META_NAME;

	/**
	 * @return RegisterImageEdit
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

		//TODO: find filter which gets fired when using WP image editor
		add_filter( 'wp_generate_attachment_metadata', array( $this, 'topImageModification' ), 10, 2 );

	}

	/**
	 * Add one to image version meta
	 * @param $metadata mixed Original metadata of image
	 * @param $att_id int Attribute ID
	 *
	 * @return mixed Original metadata
	 */
	public function topImageModification( $metadata, $att_id ) {
		$image_version = intval( get_post_meta( $att_id, $this->version_meta_name, true ) );
		$next_version = ( empty( $image_version ) ) ? 1 : $image_version + 1;

		update_post_meta( $att_id, $this->version_meta_name, $next_version );

		return $metadata;
	}

}