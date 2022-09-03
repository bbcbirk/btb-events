<?php

namespace BTBEvents\Plugin;

class Plugin {

	// Text domain for translators
	const TEXT_DOMAIN = 'btb-events'; // Don't use - in the name

	/**
	 * @var object Instance of this class.
	 */
	protected static $instance = null;

	/**
	 * Do not load this more than once.
	 */
	private function __construct() {
		$this->load_textdomain();
		$this->bootstrap();
	}

	/**
	 * Returns the instance of this class.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public static function get_text_domain() {
		return self::TEXT_DOMAIN;
	}

	/**
	 * General setup.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( self::get_text_domain(), false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Init plugin
	 */
	public function bootstrap() {

		if ( class_exists( __NAMESPACE__ . '\Core\Bootstrap' ) ) {
			$core = new Core\Bootstrap;
		}
	}

	public static function activate() {
		flush_rewrite_rules();
	}

	public static function deactivate() {
		flush_rewrite_rules();
	}

	public static function require_file( $file ) {
		if ( ! file_exists( self::plugin_path() . '/' . $file ) ) {
			return false;
		}

		require_once self::plugin_path() . '/' . $file;
		return true;
	}

	/**
	 * Get the plugin url.
	 * @return string
	 */
	public static function plugin_url() {
		return untrailingslashit( plugins_url( '/', __FILE__ ) );
	}

	/**
	 * Get the plugin path.
	 * @return string
	 */
	public static function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

}
