<?php

namespace BTB\Events\Core\REST;

use BTB\Events\Abstracts\RestBase;
use BTB\Events\Plugin;
use BTB\Events\Core\PostTypes\Meta\EventsMeta;
use WP_REST_Request;
use WP_Error;

class Events extends RestBase {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'btbevents';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $route_base = 'v1';

	public function register_routes() {
		register_rest_route(
			$this->get_namespace(),
			$this->get_rest_route( 'admin/calendar' ),
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'admin_calendar_callback' ],
				'permission_callback' => [ $this, 'permission_admin_nonce' ],
				'validate_callback'   => function( WP_REST_Request $request ) {
					if ( empty( $request->get_param( 'start' ) ) ) {
						return false;
					}
					if ( empty( $request->get_param( 'end' ) ) ) {
						return false;
					}
					return true;
				},
			]
		);

		register_rest_route(
			$this->get_namespace(),
			$this->get_rest_route( 'calendar' ),
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'calendar_callback' ],
				'permission_callback' => [ $this, 'permission_public' ],
				'validate_callback'   => function( WP_REST_Request $request ) {
					if ( empty( $request->get_param( 'start' ) ) ) {
						return false;
					}
					if ( empty( $request->get_param( 'end' ) ) ) {
						return false;
					}
					return true;
				},
			]
		);
	}

	public function permission_admin_nonce( WP_REST_Request $request ) {
		if ( ! $request->has_param( 'validation' ) || wp_verify_nonce( $request->get_param( 'validation' ), 'wp_rest' ) ) {
			return new WP_Error( 'missing_auth', __( 'Unauthorized', Plugin::get_text_domain() ), [ 'status' => 401 ] );
		}

		return true;
	}

	public function admin_calendar_callback( WP_REST_Request $request ) {
		//Get Events
		$events = $this->get_admin_events( $request->get_param( 'start' ), $request->get_param( 'end' ) );

		return $events;
	}

	public function calendar_callback( WP_REST_Request $request ) {
		//Get Events
		$events = $this->get_events( $request->get_param( 'start' ), $request->get_param( 'end' ) );

		return $events;
	}

	private function get_admin_events( $start = '', $end = '' ) {
		//Get Events
		$events = $this->query_events( $start, $end );

		//Map data
		$event_data = [];
		foreach ( $events as $post_id ) {
			$event = [];

			$display_in_calendar = get_post_meta( $post_id, EventsMeta::get_meta_key( 'display_in_calendar' ), true );
			if ( empty( $display_in_calendar ) ) {
				//Skip
				continue;
			}

			//$event['display'] = 'background';
			$event['title'] = get_the_title( $post_id );

			$event['url'] = admin_url( 'post.php?post=' . $post_id . '&action=edit' );

			$start          = get_post_meta( $post_id, EventsMeta::get_meta_key( 'start' ), true );
			$event['start'] = $start * 1000;

			$end = get_post_meta( $post_id, EventsMeta::get_meta_key( 'end' ), true );
			if ( ! empty( $end ) ) {
				$event['end'] = $end * 1000;
			} else {
				$event['end'] = ( $start + ( 60 * 60 * 2 ) ) * 1000; //Add two hours
			}

			$event_data[] = $event;
		}

		return $event_data;
	}

	private function get_events( $start = '', $end = '' ) {
		$args = [
			'post_status' => [ 'publish', 'private' ],
		];
		//Get Events
		$events = $this->query_events( $start, $end, $args );

		//Map data
		$event_data = [];
		foreach ( $events as $post_id ) {
			$event = [];

			$display_in_calendar = get_post_meta( $post_id, EventsMeta::get_meta_key( 'display_in_calendar' ), true );
			if ( empty( $display_in_calendar ) ) {
				//Skip
				continue;
			}

			$is_public = get_post_meta( $post_id, EventsMeta::get_meta_key( 'is_public' ), true );
			if ( empty( $is_public ) ) {
				//Only show time
				$event['title'] = __( 'Private Event', Plugin::get_text_domain() );
			} else {
				//$event['display'] = 'background';
				$event['title'] = get_the_title( $post_id );

				$event['url'] = get_the_permalink( $post_id );
			}

			$start          = get_post_meta( $post_id, EventsMeta::get_meta_key( 'start' ), true );
			$event['start'] = $start * 1000;

			$end = get_post_meta( $post_id, EventsMeta::get_meta_key( 'end' ), true );
			if ( ! empty( $end ) ) {
				$event['end'] = $end * 1000;
			} else {
				$event['end'] = ( $start + ( 60 * 60 * 2 ) ) * 1000; //Add two hours
			}

			$event_data[] = $event;
		}

		return $event_data;
	}

	private function query_events( $start = '', $end = '', $args = [] ) {
		$default = [
			'post_type'      => 'btb_events',
			'posts_per_page' => -1,
			'post_status'    => [ 'publish', 'private', 'future' ],
			'meta_query'     => [
				'relation' => 'OR',
				[
					'relation' => 'AND',
					[
						'key'     => EventsMeta::get_meta_key( 'start' ),
						'compare' => 'EXISTS',
					],
					[
						'key'     => EventsMeta::get_meta_key( 'start' ),
						'value'   => [ strtotime( $start ), strtotime( $end ) ],
						'type'    => 'numeric',
						'compare' => 'BETWEEN',
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
						'value'   => [ strtotime( $start ), strtotime( $end ) ],
						'type'    => 'numeric',
						'compare' => 'BETWEEN',
					],
				],
			],
			'fields'         => 'ids',
		];

		$args = wp_parse_args( $args, $default );

		$items = get_posts( $args );

		return $items;
	}

}
