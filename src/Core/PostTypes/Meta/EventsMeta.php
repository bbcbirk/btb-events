<?php

namespace BTB\Events\Core\PostTypes\Meta;

use BTB\Events\Plugin;
use BTB\Events\Core\Data\Events as DATA;

class EventsMeta {

	const PREFIX     = 'btb_events';
	const METABOX_ID = 'btb_events_meta';

	public function __construct( $post_type_object, $post_type ) {
		$this->post_type_object = $post_type_object;

		$this->meta_box_args = [
			'id'           => self::get_metabox_id(),
			'title'        => __( 'Event Meta', Plugin::get_text_domain() ),
			'object_types' => [ $post_type ],
			'show_names'   => true,
			'context'      => 'normal',
			'priority'     => 'default',
		];

		// Setup additional columns in the backend
		$this->add_columns();

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
				'id'          => self::get_meta_key( 'start' ),
				'name'        => __( 'Event start', Plugin::get_text_domain() ),
				'type'        => 'text_datetime_timestamp',
				'attributes'  => [
					'data-timepicker' => json_encode(
						[
							'timeFormat' => 'HH:mm',
						]
					),
				],
				'time_format' => 'H:i',
				'date_format' => 'd-m-Y',
			],
			[
				'id'          => self::get_meta_key( 'end' ),
				'name'        => __( 'Event end', Plugin::get_text_domain() ),
				'type'        => 'text_datetime_timestamp',
				'desc'        => __( 'If left unset, the duration is set to 2 hours in the calendar.', Plugin::get_text_domain() ),
				'attributes'  => [
					'data-timepicker' => json_encode(
						[
							'timeFormat' => 'HH:mm',
						]
					),
				],
				'time_format' => 'H:i',
				'date_format' => 'd-m-Y',
			],
			[
				'id'   => self::get_meta_key( 'location' ),
				'name' => __( 'Location', Plugin::get_text_domain() ),
				'type' => 'text',
			],
			[
				'id'   => self::get_meta_key( 'artist' ),
				'name' => __( 'Artist', Plugin::get_text_domain() ),
				'type' => 'text',
			],
			[
				'id'         => self::get_meta_key( 'featuring' ),
				'name'       => __( 'Featuring', Plugin::get_text_domain() ),
				'type'       => 'text',
				'attributes' => [
					'placeholder' => __( 'Featuring John Doe', Plugin::get_text_domain() ),
				],
			],
			[
				'id'           => self::get_meta_key( 'price' ),
				'name'         => __( 'Ticket Price', Plugin::get_text_domain() ),
				'type'         => 'text',
				'after_field'  => 'DKK',
				'before_field' => ' ',
			],
			[
				'id'   => self::get_meta_key( 'ticket_link' ),
				'name' => __( 'Link to ticket vendor', Plugin::get_text_domain() ),
				'type' => 'text_url',
				'desc' => __( 'Link to a ticket vendor, like ticketmaster.', Plugin::get_text_domain() ),
			],
			[
				'id'         => self::get_meta_key( 'ticket_link_text' ),
				'name'       => __( 'Ticket Link text', Plugin::get_text_domain() ),
				'type'       => 'text',
				'attributes' => [
					'placeholder' => __( 'Buy Tickets', Plugin::get_text_domain() ),
				],
			],
			[
				'id'   => self::get_meta_key( 'link' ),
				'name' => __( 'Link to event', Plugin::get_text_domain() ),
				'type' => 'text_url',
				'desc' => __( 'Link to a event, like festival webpage.', Plugin::get_text_domain() ),
			],
			[
				'id'         => self::get_meta_key( 'link_text' ),
				'name'       => __( 'Link text', Plugin::get_text_domain() ),
				'type'       => 'text',
				'attributes' => [
					'placeholder' => __( 'Read More', Plugin::get_text_domain() ),
				],
			],
			[
				'id'      => self::get_meta_key( 'show_add_to_calendar' ),
				'name'    => __( 'Display Add to Calendar', Plugin::get_text_domain() ),
				'desc'    => __( 'Display Add to Calendar button.', Plugin::get_text_domain() ),
				'type'    => 'radio_inline',
				'default' => true,
				'options' => [
					true  => __( 'Display', Plugin::get_text_domain() ),
					false => __( 'Do not Display', Plugin::get_text_domain() ),
				],
			],
			[
				'id'      => self::get_meta_key( 'is_public' ),
				'name'    => __( 'Display Type', Plugin::get_text_domain() ),
				'desc'    => __( 'Private events will display as booked with no information of the event.', Plugin::get_text_domain() ),
				'type'    => 'radio_inline',
				'default' => true,
				'options' => [
					true  => __( 'Public', Plugin::get_text_domain() ),
					false => __( 'Private', Plugin::get_text_domain() ),
				],
			],
			[
				'id'      => self::get_meta_key( 'display_in_calendar' ),
				'name'    => __( 'Display in calendar', Plugin::get_text_domain() ),
				'type'    => 'radio_inline',
				'default' => true,
				'options' => [
					true  => __( 'Display', Plugin::get_text_domain() ),
					false => __( 'Do not Display', Plugin::get_text_domain() ),
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
		$this->post_type_object->columns()->add(
			[
				self::get_meta_key( 'start' )           => __( 'Event start', Plugin::get_text_domain() ),
				self::get_meta_key( 'location' )        => __( 'Location', Plugin::get_text_domain() ),
				self::get_meta_key( 'add_to_calendar' ) => __( 'Add to Calendar', Plugin::get_text_domain() ),
			]
		);

		//Populate
		$this->post_type_object->columns()->populate(
			self::get_meta_key( 'start' ),
			function ( $column, $post_id ) {
				$timestamp = get_post_meta( $post_id, self::get_meta_key( 'start' ), true );
				if ( ! empty( $timestamp ) ) {
					echo wp_date( 'Y/m/d h:i', $timestamp );
				}
			}
		);

		$this->post_type_object->columns()->populate(
			self::get_meta_key( 'location' ),
			function ( $column, $post_id ) {
				$location = get_post_meta( $post_id, self::get_meta_key( 'location' ), true );
				if ( ! empty( $location ) ) {
					echo '<a href="http://google.com/maps/search/' . urlencode_deep( $location ) . '" target="_blank" rel="noopener noreferrer">' . $location . '</a>';
				}
			}
		);

		$this->post_type_object->columns()->populate(
			self::get_meta_key( 'add_to_calendar' ),
			function ( $column, $post_id ) {
				$button = DATA::get_add_to_calendar_button( $post_id );
				if ( empty( $button ) ) {
					_e( 'Event end is before Event start', Plugin::get_text_domain() );
				} else {
					echo $button;
				}
			}
		);

		//Sortable
		$this->post_type_object->columns()->sortable(
			[
				self::get_meta_key( 'start' ) => [ self::get_meta_key( 'start' ), false ],
			]
		);

		//Order
		$this->post_type_object->columns()->order(
			[
				self::get_meta_key( 'location' )        => 2,
				self::get_meta_key( 'start' )           => 3,
				self::get_meta_key( 'add_to_calendar' ) => 4,
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
