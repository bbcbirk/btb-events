<?php

namespace BTBEvents\Plugin\Core\PostTypes;

use BTBEvents\Plugin\Core\PostTypes\ArchiveSettings;
use BTBEvents\Plugin\Core\PostTypes\Meta\EventsMeta;
use BTBEvents\Plugin\Plugin;
use PostTypes\PostType;

class Events {

	const POST_TYPE = 'btb_events'; // Max 20 chars

	public $post_type_object;

	public function __construct() {
		$this->post_type_object();
		$this->meta_data();
		$this->taxonomy();
		$this->archive_settings();
		$this->register_post_type_object();
	}

	public static function get_post_type() {
		return self::POST_TYPE;
	}

	/**
	 * validates if it is same post type.
	 *
	 * @param mixed id | object
	 * @return boolean
	 */
	public static function is_same_post_type( $post ) {
		return self::get_post_type() === get_post_type( $post );
	}

	/**
	 * Setup post type
	 *
	 * @return void
	 */
	public function post_type_object() {
		$this->post_type_object = new PostType(
			[
				'name'     => self::get_post_type(),
				'singular' => __( 'Event', Plugin::get_text_domain() ),
				'plural'   => __( 'Events', Plugin::get_text_domain() ),
			],
			[
				'supports'     => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
				'menu_icon'    => 'dashicons-calendar',
				'public'       => true,
				'has_archive'  => false,
				'show_in_rest' => true, // to enable the Gutenberg editor
				'rewrite'      => [
					'slug' => __( 'event', Plugin::get_text_domain() ),
				],

			]
		);
	}

	/**
	 * register post type
	 *
	 * @return void
	 */
	public function register_post_type_object() {
		$this->post_type_object->register();
	}

	/**
	 * Init the meta fields related to the post type
	 *
	 * @return void
	 */
	public function meta_data() {
		$event_meta = new EventsMeta( $this->post_type_object, self::get_post_type() );
	}

	/**
	 * Init the taxonomy related to the post type
	 *
	 * @return void
	 */
	public function taxonomy() {
		//$example_post_type_taxonomy = new ExamplePostTypeTaxonomy( self::get_post_type() );
	}

	/**
	 * Load the archive settings page
	 *
	 * @return void
	 */
	public function archive_settings() {
		//$case_archive_settings = new ArchiveSettings( self::get_post_type() );
	}

}
