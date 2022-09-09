<?php

namespace BTB\Events\Core;

use BTB\Events\Plugin;

/**
 * Usage: ( new Assets )->register();
 *        ( new Assets )->load(); load everything
 *        ( new Assets )->load_js(); load only javascript files
 *        ( new Assets )->load_css(); load only css files
 *        ( new Assets )->load_react_js( [ 'react-app-folder-name' ] ); // load react app files, js and css
 *
 */
class Assets {

	private $hook       = 'btb_events';
	private $nonce_seed = 'wp_rest';
	private $page       = '';
	private $react_apps = [];

	public function __construct() {
		//
	}

	/**
	 * Get localize options
	 *
	 * @return array
	 */
	public function get_options(): array {
		$options = [
			'wpAPI' => get_rest_url(),
			'nonce' => wp_create_nonce( $this->get_nonce_seed() ),
		];

		if ( is_admin() ) {
			$options = apply_filters( 'btb_events_base_options', $options );
			return apply_filters( 'btb_events_base-admin_options', $options );
		} else {
			return apply_filters( 'btb_events_base_options', $options );
		}
	}

	/**
	 * Get localize options for react app
	 *
	 * @return array
	 */
	public function get_react_options(): array {
		return [];
	}

	/**
	 * Get hook
	 *
	 * @return string
	 */
	public function get_hook( $suffix = '' ): string {
		$hook = $this->hook;

		if ( $suffix != '' ) {
			$hook .= '_' . $suffix;
		}

		return $hook;
	}

	/**
	 * Get nonce
	 *
	 * @return string
	 */
	public function get_nonce_seed(): string {
		return $this->nonce_seed;
	}

	/**
	 * Register
	 *
	 * @return void
	 */
	public function register() {
		if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', [ $this, 'register_js' ] );
			add_action( 'admin_enqueue_scripts', [ $this, 'register_css' ] );
		} else {
			add_action( 'wp_enqueue_scripts', [ $this, 'register_js' ], 5 );
			add_action( 'wp_enqueue_scripts', [ $this, 'register_css' ], 5 );
		}
	}

	/**
	 * Load
	 *
	 * @return void
	 */
	public function load() {
		if ( is_admin() ) {
			$this->load_js();
			$this->load_css();
		} else {
			$this->load_js();
			$this->load_css();
		}
	}

	/**
	 * Load
	 *
	 * @return void
	 */
	public function load_block_assets() {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_block_js' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_block_css' ] );
	}

	/**
	 * Load JS
	 *
	 * @return void
	 */
	public function load_js() {
		if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_js' ] );
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_js' ] );
		} else {
			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_js' ] );
		}
	}

	/**
	 * Load CSS
	 *
	 * @return void
	 */
	public function load_css() {
		if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_css' ] );
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_css' ] );
		} else {
			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_css' ] );
		}
	}

	/**
	 * Load assets for react apps
	 *
	 * @param array $apps An array of app names
	 * @return void
	 */
	public function load_react_js( array $apps ): void {
		$this->react_apps = $apps;
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_react_apps' ] );
	}

	/**
	 * Enqueue assets for react apps
	 *
	 * @return void
	 */
	public function enqueue_react_apps(): void {
		if ( ! is_admin() && isset( $this->react_apps ) && ! empty( $this->react_apps ) ) {
			foreach ( $this->react_apps as $app ) {
				$deps           = [];
				$dir            = "/assets/react-apps/{$app}/build";
				$path           = Plugin::plugin_path() . $dir;
				$path_url       = Plugin::plugin_url() . $dir;
				$asset_manifest = file_get_contents( $path . '/asset-manifest.json' );

				if ( $asset_manifest ) {
					$asset_manifest_array = json_decode( $asset_manifest );

					if ( ! is_null( $asset_manifest_array ) && isset( $asset_manifest_array->entrypoints ) ) {
						foreach ( $asset_manifest_array->entrypoints as $key => $entrypoint ) {
							$entrypoint_sanitized = sanitize_title( $entrypoint );
							$handle               = sprintf( '%s-%s', Plugin::get_text_domain(), $entrypoint_sanitized );
							$src                  = sprintf( '%s/%s', $path_url, $entrypoint );
							$args                 = [
								$handle, // handle
								$src, // src
								$deps, // deps
								'1.0.0', // version
							];

							if ( pathinfo( $entrypoint, PATHINFO_EXTENSION ) === 'css' ) {
								wp_enqueue_style( ...$args );
							} else {
								$args[] = true; // in footer
								wp_enqueue_script( ...$args ); // Eg. wp_enqueue_script( $handle, $src, $deps, '1.0', true );
							}

							// load global variables, before first script
							if ( $key === 0 && ! empty( $this->get_react_options() ) ) {
								wp_localize_script( $handle, $this->get_hook( 'js' ), $this->get_react_options() );
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Register js
	 *
	 * @return void
	 */
	public function register_js() {
		// wp_register_script( $this->get_hook( 'select2' ), 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.12/js/select2.full.min.js', [ 'jquery' ], '4.0.12', true );
		wp_register_script( $this->get_hook( 'fullcalendar' ), 'https://cdn.jsdelivr.net/npm/fullcalendar@5.10.2/main.min.js', [ 'jquery' ], '5.10.2', true );
		wp_register_script( $this->get_hook( 'add-to-calendar-button' ), 'https://cdn.jsdelivr.net/npm/add-to-calendar-button@1', [], '1.15.0', true );

		$script_inflix = ( file_exists( Plugin::plugin_path() . '/assets/dist/js/base.min.js' ) ? '.min' : '' );

		wp_register_script( $this->get_hook( 'base' ), Plugin::plugin_url() . '/assets/dist/js/base' . $script_inflix . '.js', [ 'jquery', $this->get_hook( 'add-to-calendar-button' ) ], '1.0', true );

		wp_localize_script( $this->get_hook( 'base' ), $this->get_hook( 'js' ), $this->get_options() );

		wp_register_script( $this->get_hook( 'base_admin' ), Plugin::plugin_url() . '/assets/dist/js/base-admin' . $script_inflix . '.js', [ 'jquery', $this->get_hook( 'fullcalendar' ) ], '1.0', true );

		wp_localize_script( $this->get_hook( 'base_admin' ), $this->get_hook( 'js' ), apply_filters( $this->get_hook( 'base_admin' ) . '_options', $this->get_options() ) );
	}

	/**
	 * Register css
	 *
	 * @return void
	 */
	public function register_css() {
		// wp_register_style( $this->get_hook( 'select2' ), 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.12/css/select2.min.css' );
		wp_register_style( $this->get_hook( 'fullcalendar' ), 'https://cdn.jsdelivr.net/npm/fullcalendar@5.10.2/main.min.css', [], '5.10.2' );
		wp_register_style( $this->get_hook( 'add-to-calendar-button' ), 'https://cdn.jsdelivr.net/npm/add-to-calendar-button@1/assets/css/atcb.min.css', [], '1.15.0' );

		$style_inflix = ( file_exists( Plugin::plugin_path() . '/assets/dist/css/base.min.css' ) ? '.min' : '' );

		wp_register_style( $this->get_hook( 'base' ), Plugin::plugin_url() . '/assets/dist/css/base' . $style_inflix . '.css', [], false, 'all' );

		wp_register_style( $this->get_hook( 'base_admin' ), Plugin::plugin_url() . '/assets/dist/css/base-admin' . $style_inflix . '.css', [], false, 'all' );
	}

	/**
	 * Enqueue js
	 *
	 * @return void
	 */
	public function enqueue_js() {
		// wp_enqueue_script( $this->get_hook( 'select2' ) );
		wp_enqueue_script( $this->get_hook( 'base' ) );
	}

	/**
	 * Enqueue css
	 *
	 * @return void
	 */
	public function enqueue_css() {
		// wp_enqueue_style( $this->get_hook( 'select2' ) );
		wp_enqueue_style( $this->get_hook( 'base' ) );
	}

	/**
	 * Enqueue admin js
	 *
	 * @return void
	 */
	public function enqueue_admin_js() {
		wp_enqueue_script( $this->get_hook( 'fullcalendar' ) );
		wp_enqueue_script( $this->get_hook( 'base_admin' ) );
	}

	/**
	 * Enqueue admin css
	 *
	 * @return void
	 */
	public function enqueue_admin_css() {
		wp_enqueue_style( $this->get_hook( 'fullcalendar' ) );
		wp_enqueue_style( $this->get_hook( 'base_admin' ) );
	}

	/**
	 * Enqueue block js
	 *
	 * @return void
	 */
	public function enqueue_block_js() {
		if ( has_block( 'btb/event-teaser' ) ) {
			wp_enqueue_script( $this->get_hook( 'base' ) );
			wp_enqueue_script( $this->get_hook( 'add-to-calendar-button' ) );
		}
	}

	/**
	 * Enqueue block css
	 *
	 * @return void
	 */
	public function enqueue_block_css() {
		if ( has_block( 'btb/event-teaser' ) ) {
			wp_enqueue_style( $this->get_hook( 'add-to-calendar-button' ) );
		}
	}

}
