<?php

namespace BTB\Events\Core\Data;

use BTB\Events\Plugin;
use BTB\Events\Core\PostTypes\Meta\EventsMeta;

class Events {

	public static function get_events( array $args = [] ) {
		$default = [
			'post_type'   => 'btb_events',
			'post_status' => [ 'publish' ],
			'meta_query'  => [
				'relation' => 'AND',
				[
					'key'     => EventsMeta::get_meta_key( 'is_public' ),
					'value'   => true,
					'compare' => '=',
				],
				[
					'relation' => 'OR',
					[
						'relation' => 'AND',
						[
							'key'     => EventsMeta::get_meta_key( 'start' ),
							'compare' => 'EXISTS',
						],
						[
							'key'     => EventsMeta::get_meta_key( 'end' ),
							'compare' => 'EXISTS',
						],
						[
							'key'     => EventsMeta::get_meta_key( 'end' ),
							'value'   => time(),
							'type'    => 'numeric',
							'compare' => '>=',
						],
					],
					[
						'relation' => 'AND',
						[
							'key'     => EventsMeta::get_meta_key( 'start' ),
							'compare' => 'EXISTS',
						],
						[
							'key'     => EventsMeta::get_meta_key( 'end' ),
							'compare' => 'NOT EXISTS',
						],
						[
							'key'     => EventsMeta::get_meta_key( 'start' ),
							'value'   => ( time() + ( 60 * 60 * 2 ) ),
							'type'    => 'numeric',
							'compare' => '>=',
						],
					],
				],
			],
			'fields'      => 'ids',
		];

		$args = wp_parse_args( $args, $default );

		$items = get_posts( $args );

		//Map data
		$events = [];
		foreach ( $items as $post_id ) {

			$events[] = [
				'id'                       => $post_id,
				'title'                    => get_the_title( $post_id ),
				'permalink'                => get_the_permalink( $post_id ),
				'readmore'                 => __( 'Read More', Plugin::get_text_domain() ),
				'title'                    => get_the_title( $post_id ),
				'date'                     => date_i18n( 'D j M', get_post_meta( $post_id, EventsMeta::get_meta_key( 'start' ), true ) ),
				'datetime'                 => date_i18n( 'Y-m-d h:i', get_post_meta( $post_id, EventsMeta::get_meta_key( 'start' ), true ) ),
				'time'                     => sprintf( __( 'Kl. %s', Plugin::get_text_domain() ), date_i18n( 'h:i', get_post_meta( $post_id, EventsMeta::get_meta_key( 'start' ), true ) ) ),
				'artist'                   => get_post_meta( $post_id, EventsMeta::get_meta_key( 'artist' ), true ),
				'featuring'                => get_post_meta( $post_id, EventsMeta::get_meta_key( 'featuring' ), true ),
				'location'                 => get_post_meta( $post_id, EventsMeta::get_meta_key( 'location' ), true ),
				'location_url'             => 'https://google.com/maps/search/' . urlencode_deep( get_post_meta( $post_id, EventsMeta::get_meta_key( 'location' ), true ) ),
				'add_to_calendar_setting'  => self::get_add_to_calendar_settings( $post_id ),
				'add_to_calendar_text'     => __( 'Add to Calendar', Plugin::get_text_domain() ),
				'add_to_calendar_dashicon' => 'dashicons-calendar',
			];
		}

		return $events;
	}

	public static function get_add_to_calendar_settings( int $post_id, bool $encoded = true ) {
		$add_to_calendar_setting                 = [];
		$add_to_calendar_setting['name']         = get_the_title( $post_id );
		$add_to_calendar_setting['startDate']    = date_i18n( 'Y-m-d', get_post_meta( $post_id, EventsMeta::get_meta_key( 'start' ), true ) );
		$add_to_calendar_setting['startTime']    = date_i18n( 'h:i', get_post_meta( $post_id, EventsMeta::get_meta_key( 'start' ), true ) );
		$add_to_calendar_setting['iCalFileName'] = get_the_title( $post_id );
		$add_to_calendar_setting['location']     = get_post_meta( $post_id, EventsMeta::get_meta_key( 'location' ), true );
		$add_to_calendar_setting['options']      = [
			'Apple',
			'Google',
			'iCal',
			'Microsoft365',
			'MicrosoftTeams',
			'Outlook.com',
			'Yahoo',
		];

		if ( ! empty( get_post_meta( $post_id, EventsMeta::get_meta_key( 'end' ), true ) ) ) {
			$add_to_calendar_setting['endDate'] = date_i18n( 'Y-m-d', get_post_meta( $post_id, EventsMeta::get_meta_key( 'end' ), true ) );
			$add_to_calendar_setting['endTime'] = date_i18n( 'h:i', get_post_meta( $post_id, EventsMeta::get_meta_key( 'end' ), true ) );
		} else {
			$add_to_calendar_setting['endTime'] = date_i18n( 'h:i', get_post_meta( $post_id, EventsMeta::get_meta_key( 'start' ), true ) + ( 60 * 60 * 2 ) );
		}

		if ( ! empty( get_post_meta( $post_id, EventsMeta::get_meta_key( 'artist' ), true ) ) || ( ! empty( get_post_meta( $post_id, EventsMeta::get_meta_key( 'featuring' ), true ) ) ) ) {
			$add_to_calendar_setting['description'] = ! empty( get_post_meta( $post_id, EventsMeta::get_meta_key( 'artist' ), true ) ) ? get_post_meta( $post_id, EventsMeta::get_meta_key( 'artist' ), true ) . '<br>' . get_post_meta( $post_id, EventsMeta::get_meta_key( 'featuring' ), true ) : get_post_meta( $post_id, EventsMeta::get_meta_key( 'featuring' ), true );
		}

		return $encoded ? base64_encode( json_encode( $add_to_calendar_setting ) ) : $add_to_calendar_setting;
	}

}
