<?php

namespace BTB\Events;

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

		if ( is_admin() ) {
			if ( class_exists( __NAMESPACE__ . '\Admin\Bootstrap' ) ) {
				$admin = new Admin\Bootstrap;
			}
		} else {
			if ( class_exists( __NAMESPACE__ . '\Frontend\Bootstrap' ) ) {
				$frontend = new Frontend\Bootstrap;
			}
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

	/**
	 * Get the template path.
	 * @return string
	 */
	public static function view_path( $template_name ) {
		return self::plugin_path() . '/views/' . $template_name;
	}

	/**
	 * Check if template exists
	 * @return bool
	 */
	public static function view_exist( $template_name ) {
		return file_exists( self::view_path( $template_name ) );
	}

	/**
	 * Get the template part and send along any variables.
	 * @return string
	 */
	public static function get_view( $template_name, $vars = [] ) {
		// phpcs:ignore
		extract( $vars );
		include self::view_path( $template_name );
	}

	/**
	 * Get the mustache template
	 *
	 * @see https://github.com/bobthecow/mustache.php/wiki
	 * @see https://mustache.github.io/
	 * @see https://github.com/bobthecow/mustache.php/wiki/FILTERS-pragma
	 *
	 * @param string $template The template name
	 * @param array $model The model
	 * @param boolean $echo If set to false, will return the template data instead
	 * @return void
	 */
	public static function get_template( $template, $model, $echo = true ) {
		if ( self::view_exist( $template ) ) {
			$view_path = self::plugin_path() . '/views';
			$mustache  = new MustacheTemplate( $view_path );
			$mustache->load_template( $template, $model, $echo );
		}
	}

}
