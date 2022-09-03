<?php

namespace BTBEvents\Plugin\Core\PostTypes;

use BTBEvents\Plugin\Plugin;

class ArchiveSettings {
	public $post_type    = '';
	public $prefix       = '';
	public $metabox_id   = '';
	public $metabox_args = [];

	public function __construct( $post_type ) {
		$this->post_type    = $post_type;
		$this->prefix       = $this->get_post_type() . '_archive';
		$this->metabox_id   = $this->get_post_type();
		$this->metabox_args = [
			'id'           => $this->get_metabox_id(),
			'title'        => __( 'Archive Settings', Plugin::get_text_domain() ),
			'object_types' => [ 'options-page' ],
			'option_key'   => $this->get_meta_key( 'settings' ),
			'menu_title'   => __( 'Archive Settings', Plugin::get_text_domain() ),
			'parent_slug'  => 'edit.php?post_type=' . $this->get_post_type(),
		];

		add_action( 'cmb2_admin_init', [ $this, 'add_fields' ] );
		add_action( 'wp', [ $this, 'run_hooks' ] );
	}

	public function get_post_type() {
		return $this->post_type;
	}

	public function get_prefix() {
		return $this->prefix;
	}

	public function get_metabox_id() {
		return $this->metabox_id;
	}

	public function get_metabox_args() {
		return $this->metabox_args;
	}

	public function get_meta_key( $key ) {
		return $this->get_prefix() . '_' . $key;
	}

	/**
	 * Define the fields needed for the meta section
	 *
	 * You can use any fields define in the CMB2 documentation
	 * https://github.com/CMB2/CMB2/wiki/Field-Types
	 *
	 * @return array
	 */
	public function fields() {
		return [
			[
				'id'     => $this->get_meta_key( 'title' ),
				'name'   => __( 'Archive title', Plugin::get_text_domain() ),
				'type'   => 'text',
				'filter' => 'post_type_archive_title',
			],
			[
				'id'     => $this->get_meta_key( 'content_before' ),
				'name'   => __( 'Archive Content (before)', Plugin::get_text_domain() ),
				'type'   => 'wysiwyg',
				'filter' => THEMEDOMAIN . '_term_description',
			],
			[
				'id'     => $this->get_meta_key( 'content_after' ),
				'name'   => __( 'Archive Content (after)', Plugin::get_text_domain() ),
				'type'   => 'wysiwyg',
				'action' => THEMEDOMAIN . '_archive_footer',
			],
		];
	}

	/**
	 * Add fields using CMB2
	 *
	 * @return void
	 */
	public function add_fields() {
		$meta_box = new_cmb2_box( $this->metabox_args );

		foreach ( $this->fields() as $field ) {
			if ( $field['type'] == 'group' ) {
				$group_fields = $field['fields'];
				unset( $field['fields'] );

				$field_id = $meta_box->add_field( $field );

				foreach ( $group_fields as $group_field ) {
					$meta_box->add_group_field( $field_id, $group_field );
				}
			} else {
				$meta_box->add_field( $field );
			}
		}
	}

	public function run_hooks() {
		if ( is_post_type_archive( $this->get_post_type() ) ) {
			foreach ( $this->fields() as $field ) {
				if ( isset( $field['filter'] ) && ! empty( $field['filter'] ) ) {
					$this->run_filter( $field['filter'], $field['id'] );
				} elseif ( isset( $field['action'] ) && ! empty( $field['action'] ) ) {
					$this->run_action( $field['action'], $field['id'] );
				}
			}
		}
	}

	public function run_filter( $hook, $key ) {
		return add_filter(
			$hook,
			function ( $value ) use ( $key ) {
				return $this->get_setting( $key, $value );
			}
		);
	}

	public function run_action( $hook, $key ) {
		add_action(
			$hook,
			function ( $value ) use ( $key ) {
				echo do_shortcode( wpautop( $this->get_setting( $key, '' ) ) );
			}
		);
	}

	public function get_setting( $key = '', $default = false ) {
		$settings_key = $this->get_meta_key( 'settings' );

		if ( function_exists( 'cmb2_get_option' ) ) {
			return cmb2_get_option( $settings_key, $key, $default );
		}

		// Fallback to get_option if CMB2 is not loaded yet.
		$opts = get_option( $settings_key, $default );
		$val  = $default;

		if ( 'all' == $key ) {
			$val = $opts;
		} elseif ( is_array( $opts ) && array_key_exists( $key, $opts ) && false !== $opts[ $key ] ) {
			$val = $opts[ $key ];
		}

		return $val;
	}

}
