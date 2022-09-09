<?php

namespace BTB\Events\Utility;

use Mustache_Engine;
use Mustache_Loader_FilesystemLoader;

use RecursiveIteratorIterator;
use RecursiveArrayIterator;

use Exception;

class MustacheTemplate {

	/**
	 * The templates path
	 *
	 * @var string
	 */
	private $templates_path = '';

	/**
	 * Mustache helpers
	 *
	 * @var array
	 */
	private $helpers = [];

	/**
	 * The constructor
	 *
	 * @param string $templates_path The path to the templates directory, usually in the views folder.
	 */
	public function __construct( $templates_path = '' ) {
		if ( empty( $templates_path ) ) {
			throw new Exception( 'Template path is required' );
		}

		$this->templates_path = untrailingslashit( $templates_path );
	}

	/**
	 * Get the mustache template
	 *
	 * @see https://github.com/bobthecow/mustache.php/wiki
	 * @see https://mustache.github.io/
	 * @see https://github.com/bobthecow/mustache.php/wiki/FILTERS-pragma
	 *
	 * @param string $template
	 * @param array $model
	 * @return void
	 */
	public function load_template( $template, $model, $echo = true ) {
		if ( $this->template_exist( $template ) ) {
			// Mustache files loader
			$paths = [
				'loader' => new Mustache_Loader_FilesystemLoader( $this->get_templates_path() ),
			];

			// If partials folder exists, load partials
			if ( $this->template_exist( 'partials' ) ) {
				$paths['partials_loader'] = new Mustache_Loader_FilesystemLoader( $this->get_templates_path() . '/partials' );
			}

			$m = new Mustache_Engine( $paths );

			// add helpers
			$helpers = $this->mustache_helpers();
			$helpers = array_merge( $helpers, $this->helpers );

			foreach ( $helpers as $key => $helper ) {
				$m->addHelper(
					$key,
					$helper
				);
			}

			if ( ! $echo ) {
				return $m->render( $template, $model );
			}

			echo $m->render( $template, $model );
		}
	}

	/**
	 * Return the template folder path
	 *
	 * This returns the path of the template files,
	 *
	 * @return string
	 */
	protected function get_templates_path() {
		return $this->templates_path;
	}

	/**
	 * Returns the path of the single template
	 *
	 * @param string $template_name
	 * @return string
	 */
	protected function template_path( $template_name ) {
		return $this->get_templates_path() . '/' . $template_name;
	}

	/**
	 * Check if template exists
	 * @return bool
	 */
	protected function template_exist( $template_name ) {
		return file_exists( $this->template_path( $template_name ) );
	}

	/**
	 * Mustache template helpers
	 *
	 * @return array
	 */
	private function mustache_helpers() {
		$helpers = [
			'case'  => [
				'lower' => function( $value ) {
					return strtolower( (string) $value );
				},
				'upper' => function( $value ) {
					return strtoupper( (string) $value );
				},
				'camel' => function( $value ) {
					return ucwords( (string) $value );
				},
			],
			'clean' => [
				'tags'      => function( $value ) {
					return strip_tags( (string) $value );
				},
				'kses'      => function( $value ) {
					return wp_kses( $value, [] );
				},
				'kses_post' => function( $value ) {
					return wp_kses_post( $value );
				},
			],
			'esc'   => [
				'url'     => function( $value ) {
					return esc_url( (string) $value );
				},
				'url_raw' => function( $value ) {
					return esc_url_raw( (string) $value );
				},
				'attr'    => function( $value ) {
					return esc_attr( (string) $value );
				},
				'html'    => function( $value ) {
					return esc_html( (string) $value );
				},
			],
			'debug' => [
				's'      => function( $value ) {
					ob_start();
					s( $value );

					return ( WP_DEBUG ) ? ob_get_clean() : '';
				},
				'export' => function( $value ) {
					return ( WP_DEBUG ) ? printf( '<pre>%s</pre>', var_export( $value, true ) ) : '';
				},
				'dot'    => function( $value ) {
					$ritit  = new RecursiveIteratorIterator( new RecursiveArrayIterator( $value ) );
					$result = [];
					foreach ( $ritit as $leaf_value ) {
						$keys = [];
						foreach ( range( 0, $ritit->getDepth() ) as $depth ) {
							$keys[] = $ritit->getSubIterator( $depth )->key();
						}
						$result[ join( '.', $keys ) ] = $leaf_value;
					}

					ob_start();
					s( $result );

					return ( WP_DEBUG ) ? ob_get_clean() : '';
				},
			],
		];

		return $helpers;
	}

	/**
	 * Extend mustache helpers
	 *
	 * Example
	 * return [
	 *     'block' => [
	 *         's'    => function( $value ) {
	 *             ob_start();
	 *             s( $value );
	 *
	 *             return ( WP_DEBUG ) ? ob_get_clean() : '';
	 *         },
	 *         'rand' => function( $value ) {
	 *             return 'hello world';
	 *         }
	 *     ],
	 * ];
	 *
	 * @return object Return class object
	 */
	public function extend_helpers( array $helpers = [] ) {
		if ( ! is_array( $helpers ) ) {
			throw new Exception( 'Helpers should be an array' );
		}

		$this->helpers = $helpers;

		return $this;
	}

}
