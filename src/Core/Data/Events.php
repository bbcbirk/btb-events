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
				'id'                      => $post_id,
				'title'                   => get_the_title( $post_id ),
				'permalink'               => get_the_permalink( $post_id ),
				'readmore'                => __( 'Read More', Plugin::get_text_domain() ),
				'title'                   => get_the_title( $post_id ),
				'date'                    => date_i18n( 'D j M', get_post_meta( $post_id, EventsMeta::get_meta_key( 'start' ), true ) ),
				'datetime'                => date_i18n( 'Y-m-d h:i', get_post_meta( $post_id, EventsMeta::get_meta_key( 'start' ), true ) ),
				'time'                    => sprintf( __( 'Kl. %s', Plugin::get_text_domain() ), date_i18n( 'h:i', get_post_meta( $post_id, EventsMeta::get_meta_key( 'start' ), true ) ) ),
				'artist'                  => get_post_meta( $post_id, EventsMeta::get_meta_key( 'artist' ), true ),
				'featuring'               => get_post_meta( $post_id, EventsMeta::get_meta_key( 'featuring' ), true ),
				'location'                => get_post_meta( $post_id, EventsMeta::get_meta_key( 'location' ), true ),
				'location_url'            => 'https://google.com/maps/search/' . urlencode_deep( get_post_meta( $post_id, EventsMeta::get_meta_key( 'location' ), true ) ),
				'display_add_to_calendar' => ! empty( self::get_add_to_calendar_settings( $post_id ) ) && get_post_meta( $post_id, EventsMeta::get_meta_key( 'show_add_to_calendar' ), true ),
				'add_to_calendar_setting' => self::get_add_to_calendar_settings( $post_id ),
				'add_to_calendar_text'    => __( 'Add to Calendar', Plugin::get_text_domain() ),
			];
		}

		return $events;
	}

	public static function get_add_to_calendar_settings( int $post_id, bool $encoded = true ) {
		$add_to_calendar_setting         = [];
		$add_to_calendar_setting['name'] = get_the_title( $post_id );
		if ( empty( $add_to_calendar_setting['name'] ) ) {
			return false;
		}
		$add_to_calendar_setting['startDate'] = date_i18n( 'Y-m-d', get_post_meta( $post_id, EventsMeta::get_meta_key( 'start' ), true ) );
		if ( empty( $add_to_calendar_setting['startDate'] ) ) {
			return false;
		}
		$add_to_calendar_setting['startTime'] = date_i18n( 'H:i', get_post_meta( $post_id, EventsMeta::get_meta_key( 'start' ), true ) );
		if ( empty( $add_to_calendar_setting['startTime'] ) ) {
			return false;
		}
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
		if ( empty( $add_to_calendar_setting['options'] ) ) {
			return false;
		}

		if ( ! empty( get_post_meta( $post_id, EventsMeta::get_meta_key( 'end' ), true ) ) ) {
			if ( get_post_meta( $post_id, EventsMeta::get_meta_key( 'end' ), true ) < get_post_meta( $post_id, EventsMeta::get_meta_key( 'start' ), true ) ) {
				return false;
			}

			$add_to_calendar_setting['endDate'] = date_i18n( 'Y-m-d', get_post_meta( $post_id, EventsMeta::get_meta_key( 'end' ), true ) );
			if ( empty( $add_to_calendar_setting['endDate'] ) ) {
				return false;
			}
			$add_to_calendar_setting['endTime'] = date_i18n( 'H:i', get_post_meta( $post_id, EventsMeta::get_meta_key( 'end' ), true ) );
			if ( empty( $add_to_calendar_setting['endTime'] ) ) {
				return false;
			}
		} else {
			$add_to_calendar_setting['endTime'] = date_i18n( 'H:i', get_post_meta( $post_id, EventsMeta::get_meta_key( 'start' ), true ) + ( 60 * 60 * 2 ) );
			if ( empty( $add_to_calendar_setting['endTime'] ) ) {
				return false;
			}
		}

		if ( ! empty( get_post_meta( $post_id, EventsMeta::get_meta_key( 'artist' ), true ) ) || ( ! empty( get_post_meta( $post_id, EventsMeta::get_meta_key( 'featuring' ), true ) ) ) ) {
			$add_to_calendar_setting['description'] = ! empty( get_post_meta( $post_id, EventsMeta::get_meta_key( 'artist' ), true ) ) ? get_post_meta( $post_id, EventsMeta::get_meta_key( 'artist' ), true ) . '<br>' . get_post_meta( $post_id, EventsMeta::get_meta_key( 'featuring' ), true ) : get_post_meta( $post_id, EventsMeta::get_meta_key( 'featuring' ), true );
		}

		return $encoded ? base64_encode( json_encode( $add_to_calendar_setting ) ) : $add_to_calendar_setting;
	}

	/**
	 * Using https://github.com/add2cal/add-to-calendar-button
	 */
	public static function get_add_to_calendar_button( int $post_id ) {
		//Validate
		if ( empty( self::get_add_to_calendar_settings( $post_id ) ) ) {
			return '';
		}

		ob_start();
		?>
		<span class="btb_event_add_to_calendar" 
			data-event-settings="<?php echo self::get_add_to_calendar_settings( $post_id ); ?>"
			data-event-id="<?php echo $post_id; ?>" 
			title="<?php _e( 'Add to Calendar', Plugin::get_text_domain() ); ?>" 
			tabindex="0" 
			role="button" 
			aria-pressed="false" 
			aria-haspopup="listbox">
			<svg class="calendar_icon" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 110.01 122.88" style="width:1rem;height:1rem;fill:var(--wp--preset--color--foreground)" xml:space="preserve"><style type="text/css">.st0{fill-rule:evenodd;clip-rule:evenodd;}</style><g><path class="st0" d="M1.87,14.69h22.66L24.5,14.3V4.13C24.5,1.86,26.86,0,29.76,0c2.89,0,5.26,1.87,5.26,4.13V14.3l-0.03,0.39 h38.59l-0.03-0.39V4.13C73.55,1.86,75.91,0,78.8,0c2.89,0,5.26,1.87,5.26,4.13V14.3l-0.03,0.39h24.11c1.03,0,1.87,0.84,1.87,1.87 v19.46c0,1.03-0.84,1.87-1.87,1.87H1.87C0.84,37.88,0,37.04,0,36.01V16.55C0,15.52,0.84,14.69,1.87,14.69L1.87,14.69z M0.47,42.19 h109.08c0.26,0,0.46,0.21,0.46,0.46l0,0v79.76c0,0.25-0.21,0.46-0.46,0.46l-109.08,0c-0.25,0-0.47-0.21-0.47-0.46V42.66 C0,42.4,0.21,42.19,0.47,42.19L0.47,42.19L0.47,42.19z M97.27,52.76H83.57c-0.83,0-1.5,0.63-1.5,1.4V66.9c0,0.77,0.67,1.4,1.5,1.4 h13.71c0.83,0,1.51-0.63,1.51-1.4V54.16C98.78,53.39,98.1,52.76,97.27,52.76L97.27,52.76z M12.24,74.93h13.7 c0.83,0,1.51,0.63,1.51,1.4v12.74c0,0.77-0.68,1.4-1.51,1.4H12.71c-0.83,0-1.5-0.63-1.5-1.4V75.87c0-0.77,0.68-1.4,1.5-1.4 L12.24,74.93L12.24,74.93z M12.24,97.11h13.7c0.83,0,1.51,0.63,1.51,1.4v12.74c0,0.77-0.68,1.4-1.51,1.4l-13.24,0 c-0.83,0-1.5-0.63-1.5-1.4V98.51c0-0.77,0.68-1.4,1.5-1.4L12.24,97.11L12.24,97.11z M12.24,52.76h13.7c0.83,0,1.51,0.63,1.51,1.4 V66.9c0,0.77-0.68,1.4-1.51,1.4l-13.24,0c-0.83,0-1.5-0.63-1.5-1.4V54.16c0-0.77,0.68-1.4,1.5-1.4L12.24,52.76L12.24,52.76z M36.02,52.76h13.71c0.83,0,1.5,0.63,1.5,1.4V66.9c0,0.77-0.68,1.4-1.5,1.4l-13.71,0c-0.83,0-1.51-0.63-1.51-1.4V54.16 C34.51,53.39,35.19,52.76,36.02,52.76L36.02,52.76L36.02,52.76z M36.02,74.93h13.71c0.83,0,1.5,0.63,1.5,1.4v12.74 c0,0.77-0.68,1.4-1.5,1.4H36.02c-0.83,0-1.51-0.63-1.51-1.4V75.87c0-0.77,0.68-1.4,1.51-1.4V74.93L36.02,74.93z M36.02,97.11h13.71 c0.83,0,1.5,0.63,1.5,1.4v12.74c0,0.77-0.68,1.4-1.5,1.4l-13.71,0c-0.83,0-1.51-0.63-1.51-1.4V98.51 C34.51,97.74,35.19,97.11,36.02,97.11L36.02,97.11L36.02,97.11z M59.79,52.76H73.5c0.83,0,1.51,0.63,1.51,1.4V66.9 c0,0.77-0.68,1.4-1.51,1.4l-13.71,0c-0.83,0-1.51-0.63-1.51-1.4V54.16C58.29,53.39,58.96,52.76,59.79,52.76L59.79,52.76 L59.79,52.76z M59.79,74.93H73.5c0.83,0,1.51,0.63,1.51,1.4v12.74c0,0.77-0.68,1.4-1.51,1.4H59.79c-0.83,0-1.51-0.63-1.51-1.4 V75.87c0-0.77,0.68-1.4,1.51-1.4V74.93L59.79,74.93z M97.27,74.93H83.57c-0.83,0-1.5,0.63-1.5,1.4v12.74c0,0.77,0.67,1.4,1.5,1.4 h13.71c0.83,0,1.51-0.63,1.51-1.4l0-13.21c0-0.77-0.68-1.4-1.51-1.4L97.27,74.93L97.27,74.93z M97.27,97.11H83.57 c-0.83,0-1.5,0.63-1.5,1.4v12.74c0,0.77,0.67,1.4,1.5,1.4h13.71c0.83,0,1.51-0.63,1.51-1.4l0-13.21c0-0.77-0.68-1.4-1.51-1.4 L97.27,97.11L97.27,97.11z M59.79,97.11H73.5c0.83,0,1.51,0.63,1.51,1.4v12.74c0,0.77-0.68,1.4-1.51,1.4l-13.71,0 c-0.83,0-1.51-0.63-1.51-1.4V98.51C58.29,97.74,58.96,97.11,59.79,97.11L59.79,97.11L59.79,97.11z M7.01,47.71h96.92 c0.52,0,0.94,0.44,0.94,0.94v67.77c0,0.5-0.44,0.94-0.94,0.94H6.08c-0.5,0-0.94-0.42-0.94-0.94V49.58 C5.14,48.55,5.98,47.71,7.01,47.71L7.01,47.71L7.01,47.71z M78.8,29.4c2.89,0,5.26-1.87,5.26-4.13V15.11l-0.03-0.41H73.58 l-0.03,0.41v10.16C73.55,27.54,75.91,29.4,78.8,29.4L78.8,29.4L78.8,29.4z M29.76,29.4c2.89,0,5.26-1.87,5.26-4.13V15.11 l-0.03-0.41H24.53l-0.03,0.41v10.16C24.5,27.54,26.86,29.4,29.76,29.4L29.76,29.4z"/></g></svg>
			<?php _e( 'Add to Calendar', Plugin::get_text_domain() ); ?>
		</span>
		<?php
		$data = ob_get_contents();
		ob_end_clean();
		return $data;
	}

}
