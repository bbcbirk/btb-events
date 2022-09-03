<?php

namespace BTBEvents\Plugin\Core\PostTypes\Meta;

use BTBEvents\Plugin\Plugin;

class EventsMeta {

	const PREFIX     = 'boilerplate';
	const METABOX_ID = 'boilerplate_example';

	public function __construct( $post_type_object, $post_type ) {
		$this->post_type_object = $post_type_object;

		$this->meta_box_args = [
			'id'           => self::get_metabox_id(),
			'title'        => __( 'Example Post Type Meta', Plugin::get_text_domain() ),
			'object_types' => [ $post_type ],
			'show_names'   => true,
			'context'      => 'normal',
			'priority'     => 'default',
		];

		// Setup additional columns in the backend
		// $this->add_columns();

		// Do not show CMB2 styles in the frontend
		if ( ! is_admin() ) {
			$this->meta_box_args['cmb_styles'] = false;
		}

		add_action( 'cmb2_init', [ $this, 'add_fields' ] );
	}

	public static function get_prefix() {
		return self::PREFIX;
	}

	public static function get_metabox_id() {
		return self::METABOX_ID;
	}

	public static function get_meta_key( $key ) {
		return self::get_prefix() . '_' . $key;
	}

	/**
	 * Define the fields needed for the meta section
	 *
	 * You can use any fields define in the CMB2 documentation
	 * https://github.com/CMB2/CMB2/wiki/Field-Types
	 *
	 * @return array
	 */
	public static function fields() {
		return [
			[
				'id'      => self::get_meta_key( 'text' ),
				'name'    => __( 'Text', Plugin::get_text_domain() ),
				'desc'    => __( 'Field description (optional)', Plugin::get_text_domain() ),
				'default' => 'standard value (optional)',
				'type'    => 'text',
			],
			[
				'id'      => self::get_meta_key( 'icon' ),
				'name'    => __( 'Icon Image', Plugin::get_text_domain() ),
				'type'    => 'file',
				'options' => [
					'url' => false,
				],
				'text'    => [
					'add_upload_file_text' => __( 'Add Icon', Plugin::get_text_domain() ),
				],
			],
			[
				'name'       => __( 'Field Group', Plugin::get_text_domain() ),
				'id'         => self::get_meta_key( 'participants' ),
				'type'       => 'group',
				'repeatable' => true,
				'options'    => [
					'group_title'   => __( 'Entry {#}', Plugin::get_text_domain() ),
					'add_button'    => __( 'Add Another Entry', Plugin::get_text_domain() ),
					'remove_button' => __( 'Remove Entry', Plugin::get_text_domain() ),
					'closed'        => true,
				],
				'fields'     => [
					[
						'name' => __( 'Title', Plugin::get_text_domain() ),
						'id'   => 'title',
						'type' => 'text',
					],
					[
						'name' => __( 'title-2', Plugin::get_text_domain() ),
						'id'   => 'title-2',
						'type' => 'text',
					],
				],
			],
		];
	}

	/**
	 * Possibly set up columns in the backend
	 *
	 * @return void
	 */
	public function add_columns() {
		$this->post_type->columns()->add(
			[
				'price' => __( 'Price' ),
			]
		);
	}

	/**
	 * Add fields using CMB2
	 *
	 * @return void
	 */
	public function add_fields() {
		$meta_box = new_cmb2_box( $this->meta_box_args );

		foreach ( self::fields() as $field ) {
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

	/**
	 * Get meta data
	 *
	 * Example: will return all fields, defined above
	 *
	 *     ExamplePostTypeMeta::get_meta_data( $post_id );
	 *
	 *
	 * Example: will return only the keys set in the $keys param, from the fields, defined above
	 *
	 *     ExamplePostTypeMeta::get_meta_data( $post_id, [ ExamplePostTypeMeta::get_meta_key( 'text' ), ExamplePostTypeMeta::get_meta_key( 'icon' ) ] );
	 *
	 *
	 * @param  integer $post_id The post ID
	 * @param  array   $keys    (Optional) Array of keys/meta fields, you would like returned from the prefixed meta data
	 * @return array            Returns and array of meta data, by prefix
	 */
	public static function get_meta_data( int $post_id, array $keys = [] ) : array {
		$return_meta = [];
		$post_meta   = get_post_meta( $post_id );
		$fields      = array_column( self::fields(), 'id' );

		foreach ( $post_meta as $key => $meta ) {
			if ( in_array( $key, $fields ) ) {
				$return_meta[ $key ] = $meta[0];
			}
		}

		if ( ! empty( $keys ) ) {
			return array_intersect_key( $return_meta, array_flip( $keys ) );
		}

		return $return_meta;
	}

}
