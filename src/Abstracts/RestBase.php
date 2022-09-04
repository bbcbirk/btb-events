<?php

namespace BTB\Events\Abstracts;

use BTB\Events\Plugin;

use WP_REST_Server;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

abstract class RestBase extends WP_REST_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = '';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $route_base = '';

	public function __construct() {
		$this->init();

		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	/**
	 * This function can be used to add actions and
	 * filters, before the routes are registered.
	 *
	 * @return void
	 */
	protected function init() {}

	/**
	 * Return the REST namespace
	 *
	 * @return string
	 */
	protected function get_namespace() {
		return $this->namespace;
	}

	/**
	 * Return the REST route
	 *
	 * @param string $route
	 * @return string
	 */
	protected function get_rest_route( $route ) {
		return trim( $this->route_base, '/' ) . '/' . trim( $route . '/' );
	}

	/**
	 * Public permission
	 *
	 * This type of permission does not require anything and
	 * makes the API, completely open.
	 *
	 * @return boolean true
	 */
	public function permission_public() {
		return true;
	}

	/**
	 * Public permission
	 *
	 * This type of permission requires that a nonce
	 * header is sent with the requst.
	 *
	 * @return boolean
	 */
	public function permission_nonce( $request ) {
		$nonce = $request->get_header( 'X-WP-Nonce' );
		if ( wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			return true;
		}

		return new WP_Error( 'missing_auth', __( 'Unauthorized', Plugin::get_text_domain() ), [ 'status' => 401 ] );
	}

	/**
	 * Public permission
	 *
	 * This type of permission requires that the user
	 * is logged in, this can be shown with a nonce
	 * header sent with the requst.
	 *
	 * @return boolean
	 */
	public function permission_authenticated() {
		if ( is_user_logged_in() ) {
			return true;
		}

		return new WP_Error( 'missing_auth', __( 'Unauthorized', Plugin::get_text_domain() ), [ 'status' => 401 ] );
	}

	/**
	* Filter to hook the rest_pre_dispatch, if the is an error in the request
	* send it, if there is no error just continue with the current request.
	*
	* @param $request
	* @since 1.0
	*/
	public function permission_token( $request ) {
		if ( is_wp_error( $this->validate_token() ) ) {
			return $this->validate_token();
		}
		return $request;
	}

	/**
	 * Validate the token
	 *
	 * Based on the JWT plugin.
	 *
	 * @see https://github.com/jonathan-dejong/simple-jwt-authentication/blob/55ab17f7e9e4211aef97950c315f4502f36dfa4a/includes/class-simple-jwt-authentication-rest.php#L292
	 *
	 * @return mixed Returns either a wp error or null
	 */
	protected function validate_token() {
		/*
		 * Looking for the HTTP_AUTHORIZATION header, if not present just
		 * return the user.
		 */
		$auth = $this->get_authorization_header();
		// Double check for different auth header string (server dependent)
		if ( ! $auth ) {
			$auth = isset( $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ) ? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] : false;
		}

		if ( ! $auth ) {
			return new WP_Error(
				'no_auth_header',
				__( 'Authorization header not found.', Plugin::get_text_domain() ),
				[
					'status' => 403,
				]
			);
		}

		/*
		 * The HTTP_AUTHORIZATION is present verify the format
		 * if the format is wrong return the user.
		 */
		list( $token ) = sscanf( $auth, 'Bearer %s' );
		if ( ! $token ) {
			return new WP_Error(
				'bad_auth_header',
				__( 'Authorization header malformed.', Plugin::get_text_domain() ),
				[
					'status' => 403,
				]
			);
		}

		if ( $token != $this->get_token() ) {
			return new WP_Error(
				'missing_auth',
				__( 'Authorization failed.', Plugin::get_text_domain() ),
				[
					'status' => 403,
				]
			);
		}

		return null;
	}

	protected function get_token() {
		return get_option( $this->token_settings_field );
	}

	/**
	 * Get header Authorization
	 *
	 * @see https://stackoverflow.com/a/40582472
	 * */
	protected function get_authorization_header() {
		$headers = null;
		if ( isset( $_SERVER['Authorization'] ) ) {
			$headers = trim( $_SERVER['Authorization'] );
		} elseif ( isset( $_SERVER['HTTP_AUTHORIZATION'] ) ) { //Nginx or fast CGI
			$headers = trim( $_SERVER['HTTP_AUTHORIZATION'] );
		} elseif ( function_exists( 'apache_request_headers' ) ) {
			$request_headers = apache_request_headers();
			// Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
			$request_headers = array_combine( array_map( 'ucwords', array_keys( $request_headers ) ), array_values( $request_headers ) );

			if ( isset( $request_headers['Authorization'] ) ) {
				$headers = trim( $request_headers['Authorization'] );
			}
		}
		return $headers;
	}

}
