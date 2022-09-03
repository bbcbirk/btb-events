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
				'singular' => __( 'Example Post Type', Plugin::get_text_domain() ),
				'plural'   => __( 'Example Post Types', Plugin::get_text_domain() ),
			],
			[
				'supports'     => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
				'menu_icon'    => 'dashicons-admin-tools',
				'public'       => true,
				'has_archive'  => true,
				'show_in_rest' => true, // to enable the Gutenberg editor
				'rewrite'      => [
					'slug' => __( 'example-post-type', Plugin::get_text_domain() ),
				],

			]
		);

		$this->post_type_object->register();
	}

	/**
	 * Init the meta fields related to the post type
	 *
	 * @return void
	 */
	public function meta_data() {
		$example_post_type_meta = new ExamplePostTypeMeta( $this->post_type_object, self::get_post_type() );
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
		$case_archive_settings = new ArchiveSettings( self::get_post_type() );
	}

}
