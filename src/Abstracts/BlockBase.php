<?php

namespace BTB\Events\Abstracts;

use BTB\Events\Plugin;
use BTB\Events\Utility\MustacheTemplate;

use ReflectionClass;

/**
 * Abstract Block Class
 *
 * @category Block
 */
abstract class BlockBase {

	/**
	 * Block namespace
	 *
	 * @var string
	 */
	protected $namespace = 'Mosaik';

	/**
	 * The block name
	 *
	 * @var string
	 */
	protected $block_name = '';

	/**
	 * The block description
	 *
	 * @var string
	 */
	protected $block_description = '';

	/**
	 * Block icon, dashicon classes used
	 *
	 * @var string
	 */
	protected $icon = 'star-filled';

	/**
	 * Block icon background color
	 *
	 * @var string
	 */
	protected $background_color = '#F18500';

	/**
	 * Block icon foreground color
	 *
	 * @var string
	 */
	protected $foreground_color = '#FFFFFF';

	/**
	 * Is block fullwidth
	 *
	 * @var boolean
	 */
	protected $fullwidth = false;

	/**
	 * The block attributes
	 *
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * The block content
	 *
	 * @var string
	 */
	protected $content = '';

	/**
	 * The render engine
	 *
	 * @var string
	 */
	protected $render_engine = 'mustache';

	/**
	 * Add to hinclude
	 *
	 * When true, the block is loaded via javascript XHR.
	 *
	 * This will happen for all the blocks added, for the
	 * specific block.
	 *
	 * @var boolean
	 */
	protected $include = false;

	/**
	 * The include type
	 *
	 * Must be one of the following,
	 * hx:include, esi:include, xhr:include
	 *
	 * @var string
	 */
	protected $include_type = 'xhr:include';

	/**
	 * Enable extending of schema graph
	 *
	 * @var boolean
	 */
	protected $extend_schema_graph = false;

	/**
	 * Enable debugging
	 *
	 * @var boolean
	 */
	protected $debug = false;

	/**
	 * Constructor
	 *
	 * Initiates the enqueuing of the block assets.
	 * Creates a block category, to be set in block.js
	 * Registers the block and passes the rendering method
	 * for dynamic rendering of the block
	 */
	public function __construct() {
		if ( empty( $this->block_name ) ) {
			wp_die( __( 'The block needs a name', Plugin::get_text_domain() ) );
		}

		$this->init();

		add_action( 'enqueue_block_editor_assets', [ $this, 'editor_assets' ] );
		add_action( 'enqueue_block_assets', [ $this, 'block_assets' ] );

		// Add block categories
		add_filter( 'block_categories', [ $this, 'add_block_category' ], 10, 2 );

		// create a dynamic block using PHP
		if ( function_exists( 'register_block_type' ) ) {
			add_action( 'init', [ $this, 'register_block' ], 10, 2 );
		}

		add_action( $this->get_block_handle() . '_before_render', [ $this, 'style_tag' ] );

		if ( $this->debug ) {
			add_action( $this->get_block_handle() . '_after_render', [ $this, 'debug' ], 12, 2 );
		}

		if ( $this->is_include() ) {
			add_filter( 'render_block', [ $this, 'include_add' ], 10, 2 );
			add_action( 'init', [ $this, 'include_rewrite' ] );
			add_filter( 'query_vars', [ $this, 'include_query_vars' ] );
			add_action( 'template_redirect', [ $this, 'include_render_block' ] );
		}

		// Add schema from this block if $this->extend_schema_graph is true
		// Require Yoast wordpress-seo,
		// but the filter is only called if the plugin is active
		if ( isset( $this->extend_schema_graph ) && $this->extend_schema_graph ) {
			add_filter( 'wpseo_schema_block_' . $this->get_full_block_name(), [ $this, 'render_block_schema' ], 10, 3 );
		}
	}

	/**
	 * Init method
	 *
	 * Can be used to add extra actions/filters or
	 * call methods, before anything is initialized.
	 *
	 * @return void
	 */
	public function init() {}

	/**
	 * Enqueue block assets for the editor.
	 *
	 * @return void
	 */
	public function editor_assets() {
		$block_path    = plugin_dir_path( $this->get_file_path() );
		$script_inflix = ( file_exists( $block_path . 'dist/js/block.min.js' ) ? '.min' : '' );
		$style_inflix  = ( file_exists( $block_path . 'dist/css/editor.min.css' ) ? '.min' : '' );

		wp_enqueue_script( $this->get_block_handle(), plugins_url( 'dist/js/block' . $script_inflix . '.js', $this->get_file_path() ), [ 'wp-editor', 'wp-components', 'wp-blocks', 'wp-i18n', 'wp-element' ] );

		/**
		 * Add javascript variables, accessible to the block.js file.
		 * These allow the block to be changed via PHP filters
		 */
		wp_localize_script(
			$this->get_block_handle(),
			'custom_block',
			apply_filters(
				$this->get_underscored( $this->get_namespace() ) . '_blocks_settings',
				[
					'namespace'           => $this->get_unspaced( $this->get_namespace( true ) ),
					'block_name'          => $this->get_block_name( true ),
					'block_description'   => $this->block_description,
					'block_category_name' => $this->get_category_name(),
					'block_category_slug' => $this->get_category_slug(),
					'icon'                => $this->icon,
					'background'          => $this->background_color,
					'foreground'          => $this->foreground_color,
					'full_width'          => ( $this->fullwidth ) ? 'yes' : 'no',
					'block_attributes'    => $this->block_attributes(),
				],
				$this->get_block_handle()
			)
		);
		wp_enqueue_style( $this->get_block_handle() . '-editor', plugins_url( 'dist/css/editor' . $style_inflix . '.css', $this->get_file_path() ), [ 'wp-edit-blocks' ] );
	}

	/**
	 * Enqueue block assets for the frontend
	 *
	 * @return void
	 */
	public function block_assets() {
		$block_path   = plugin_dir_path( $this->get_file_path() );
		$style_inflix = ( file_exists( $block_path . 'dist/css/editor.min.css' ) ? '.min' : '' );
		$deps         = [];
		$block        = $this->get_full_block_name();

		if ( version_compare( get_bloginfo( 'version' ), '5.0', '<' ) ) {
			$deps[] = 'wp-blocks';
		}

		if ( ! is_admin() && has_block( $block ) ) {
			wp_enqueue_style( $this->get_block_handle() . '-frontend', plugins_url( 'dist/css/style' . $style_inflix . '.css', $this->get_file_path() ), $deps, '1.0' );
		}
	}

	/**
	 * Adds the structured data blocks category to
	 * the Gutenberg categories.
	 *
	 * Example filter to override categories
	 * add_filter( 'mosaik_blocks_category', function( $categories ) { $categories['mosaik-blocks']['title'] = 'Mosaik Blocks'; return $categories; } );
	 *
	 * @param array $categories Array of block categories.
	 * @param array $post Post being loaded
	 *
	 * @return array The updated categories.
	 */
	public function add_block_category( $categories, $post ) {

		$block_category = [
			'slug'  => $this->get_category_slug(),
			'title' => sprintf( __( '%s Blocks', Plugin::get_text_domain() ), $this->get_category_name() ),
			'icon'  => null,
		];

		$category_slugs = wp_list_pluck( $categories, 'slug' );

		if ( ! in_array( $block_category['slug'], $category_slugs, true ) ) {

			$custom_category = apply_filters(
				$this->get_underscored( $this->get_namespace() ) . '_blocks_category',
				[
					$block_category,
				],
				$this->get_block_handle()
			);

			return array_merge(
				$categories,
				$custom_category
			);

		}

		return $categories;
	}

	/**
	 * Register the block for dynamic rendering.
	 *
	 * This also includes an array of the attributes and
	 * a callback to the rendering method.
	 *
	 * @return void
	 */
	public function register_block() {
		// Hook server side rendering into render callback

		register_block_type(
			$this->get_full_block_name(),
			[
				'attributes'      => $this->block_attributes(),
				'render_callback' => [ $this, 'render' ],
			]
		);
	}

	/**
	 * The dynamic rendering method
	 *
	 * @param array $attributes An array of the block attributes
	 * @param string $content The static content, if converted from a static block
	 * @return void
	 */
	public function render( $attributes, $content ) {
		$this->set_attributes( $attributes );
		$this->set_content( $content );
		$template       = $this->get_template();
		$templates_path = $this->get_templates_path();
		$model          = $this->get_model( $attributes, $content );

		ob_start();

		do_action( $this->get_block_handle() . '_before_render', $attributes, $content );

		printf( '<div%s>', $this->get_html_attributes() );

		printf( '<div class="%s-blocks__inner %s__inner">', sanitize_html_class( $this->get_namespace() ), sanitize_html_class( $this->get_dashed( $this->get_block_name() ) ) );

		do_action( $this->get_block_handle() . '_before_render_inner', $attributes, $content );

		if ( isset( $this->render_engine ) && $this->render_engine == 'mustache' ) {
			/**
			 * Using mustache template
			 *
			 * @see https://github.com/bobthecow/mustache.php/wiki
			 * @see https://mustache.github.io/
			 */
			$mustache         = new MustacheTemplate( $templates_path );
			$mustache_helpers = $this->extend_mustache_helpers();

			if ( is_array( $mustache_helpers ) && ! empty( $mustache_helpers ) ) {
				$mustache->extend_helpers( $mustache_helpers );
			}

			$mustache->load_template(
				$template . '.mustache',
				$model
			);
		} else {
			Plugin::get_view(
				'blocks/' . $this->get_block_handle() . '/' . $template . '.php',
				$model
			);
		}

		do_action( $this->get_block_handle() . '_after_render_inner', $attributes, $content );

		echo '</div>';

		echo '</div>';

		do_action( $this->get_block_handle() . '_after_render', $attributes, $content );

		return ob_get_clean();

	}

	/**
	 * Get the template model
	 *
	 * This is the default model to use with the
	 * mustache template engine, but this can be
	 * overriden in your Block.php file, so that
	 * you can pass exactly what you need in the
	 * mustache template.
	 *
	 * @param array $attributes
	 * @param string $content
	 * @return array
	 */
	protected function get_model( $attributes, $content ) {
		$model = [
			'attributes' => $attributes,
			'content'    => $content,
		];

		$model = apply_filters(
			$this->get_underscored( $this->get_namespace() ) . '_block_model',
			$this->extend_model( $model ),
			$this->get_block_handle()
		);

		return $model;
	}

	/**
	 * Extend the model
	 *
	 * Override or extend the block model
	 *
	 * @param array $model
	 * @return array
	 */
	protected function extend_model( $model ) {
		return $model;
	}

	/**
	 * Creates and returns html attributes e.g. class, id, data
	 *
	 * @return string Returns html attributes
	 */
	protected function get_html_attributes() {
		$html_attributes = [
			'id'    => $this->get_attribute( 'blockId' ),
			'class' => $this->get_block_classes(),
		];

		$anchor = $this->get_anchor();
		if ( ! empty( $anchor ) ) {
			$html_attributes['id'] = $anchor;
		}

		$html_attributes = apply_filters(
			$this->get_underscored( $this->get_namespace() ) . '_block_html_attributes',
			$this->extend_html_attributes( $html_attributes ),
			$this->get_block_handle(),
			$this->get_attributes()
		);

		$html_attributes_extended = '';

		if ( ! empty( $html_attributes ) ) {
			foreach ( $html_attributes as $key => $value ) {
				$html_attributes_extended .= ' ' . $key . '="' . $value . '"';
			}
		}

		return $html_attributes_extended;
	}

	/**
	 * Creates and returns an ID tag
	 *
	 * @return string Returns the id tag: Default: empty
	 */
	protected function get_anchor() {
		return ( $this->get_attribute( 'htmlAnchor' ) ) ? esc_html( $this->get_attribute( 'htmlAnchor' ) ) : '';
	}

	/**
	 * Creates a string of classes, to be used on the wrapper
	 *
	 * @return string Returns the class tag: Default: empty
	 */
	protected function get_block_classes() {
		$classes = [
			sanitize_html_class( $this->get_unspaced( $this->get_namespace() ) . '-blocks' ),
			sanitize_html_class( $this->get_dashed( $this->get_block_name() ) ),
			sanitize_html_class( $this->get_block_class_name() ),
		];

		if ( $this->get_attribute( 'className' ) && ! empty( $this->get_attribute( 'className' ) ) ) {
			$classes[] = $this->get_attribute( 'className' );
		}

		$classes = apply_filters(
			$this->get_underscored( $this->get_namespace() ) . '_block_classes',
			$this->extend_block_classes( $classes ),
			$this->get_block_handle(),
			$this->get_attributes()
		);

		return ( ! empty( $classes ) ) ? implode( ' ', $classes ) : '';
	}

	/**
	 * Get the unique block class name
	 *
	 * @return string
	 */
	protected function get_block_class_name() {
		return $this->get_dashed( $this->get_block_name() ) . '--' . $this->get_attribute( 'blockId' );
	}

	/**
	 * Block html attributes
	 *
	 * This method can be used to extend
	 * the block html attributes, from a child class
	 * if you do not wish to use a filter.
	 *
	 * @param array $html_attributes
	 * @return array
	 */
	protected function extend_html_attributes( $html_attributes ) {
		return $html_attributes;
	}

	/**
	 * Block classes
	 *
	 * This method can be used to extend
	 * the block classes, from a child class
	 * if you do not wish to use a filter.
	 *
	 * @param array $classes
	 * @return array
	 */
	protected function extend_block_classes( $classes ) {
		return $classes;
	}

	/**
	 * Creates a style tag, to be added before the output
	 *
	 * @param array $attributes
	 * @return string
	 */
	public function get_style_tag( $attributes ) {
		$style_attr = apply_filters(
			$this->get_underscored( $this->get_namespace() ) . '_block_styles',
			$this->extend_block_styles( [] ),
			$this->get_block_handle(),
			$attributes
		);
		$return     = '';

		$style_str = ( ! empty( $style_attr ) ) ? $this->add_css( $style_attr ) : '';

		if ( ! empty( $style_str ) ) {
			$return = sprintf( '<style>%1$s</style>', $style_str );
		}

		return $return;
	}

	/**
	 * Output the style tag
	 *
	 * @param array $attributes
	 * @return void
	 */
	public function style_tag( $attributes ) {
		echo $this->get_style_tag( $attributes );
	}

	/**
	 * Block styles
	 *
	 * This method can be used to extend
	 * the block styles, from a child class
	 * if you do not wish to use a filter.
	 *
	 * @param array $styles
	 * @return array
	 */
	protected function extend_block_styles( $styles ) {
		return $styles;
	}

	/**
	 * adds inline css, taking an array of string of css rules. if using an array, use following structure. if string, just a normal css string.
	 * This can be called inside shortcodes, widgets ect, where normal enque_style would work.
	 * $css_array = [
	 *      '.element-selector' => [
	 *          'background-size' => 'contain',
	 *      ],
	 *  ];
	 *
	 * @param mixed:array|string $css
	 * @return string
	 */
	protected function add_css( $css = [] ) {

		if ( is_array( $css ) ) {
			$css_string = $this->css_array_to_css( $css );
		} else {
			$css_string = $css;
		}

		return $css_string;
	}

	/**
	 * Converts an array to css rules
	 *  $css_array = [
	 *      '.element-selector' => [
	 *          'background-size' => 'contain',
	 *      ],
	 *  ];
	 *
	 * @param array $rules
	 * @param integer $indent
	 * @return string
	 */
	protected function css_array_to_css( $rules = [], $indent = 0 ) {

		$css    = '';
		$prefix = str_repeat( '  ', $indent );
		foreach ( $rules as $key => $value ) {
			if ( is_array( $value ) ) {
				$selector   = $key;
				$properties = $value;

				$rule = $this->css_array_to_css( $properties, $indent + 1 );
				if ( ! empty( $rule ) ) {
					$css .= $prefix . "$selector {\n";
					$css .= $prefix . esc_attr( $rule );
					$css .= $prefix . "}\n";
				}
			} else {
				if ( ! empty( $value ) ) {
					if ( $key == 'background-image' && filter_var( $value, FILTER_VALIDATE_URL ) ) {
						$value = 'url(' . $value . ')';
					}
					$property = $key;
					$rule     = esc_attr( $value );
					$css     .= $prefix . "{$property}: {$rule};\n";
				}
			}
		}

		return $css;
	}

	/**
	 * Block attributes
	 *
	 * @return void
	 */
	abstract protected function block_attributes();

	/**
	 * Sets the attributes, so they are available in the class
	 *
	 * @param array $attributes
	 * @return object Returns the class instance
	 */
	protected function set_attributes( $attributes = [] ) {
		$this->attributes = $attributes;

		return $this;
	}

	/**
	 * Get all the block attributes
	 *
	 * @return array Returns all the attributes
	 */
	protected function get_attributes() {
		return $this->attributes;
	}

	/**
	 * Get one attributes
	 *
	 * @param string $attribute
	 * @param mixed $default return value
	 * @return mixed string|bool Returns the attribute, if it exists and isset else return false
	 */
	protected function get_attribute( $attribute = '', $default = false ) {
		$attributes = $this->get_attributes();

		return $attributes[ $attribute ] ?? $default;
	}

	/**
	 * Sets the content, so they are available in the class
	 *
	 * @param string $content
	 * @return object Returns the class instance
	 */
	protected function set_content( $content = '' ) {
		$this->content = $content;

		return $this;
	}

	/**
	 * Get the block content
	 *
	 * @return string Returns the content
	 */
	protected function get_content() {
		return $this->content;
	}

	/**
	 * Return the template file
	 *
	 * This returns the path of the template file,
	 * without the extension.
	 *
	 * @return string
	 */
	protected function get_template() {
		return 'block';
	}

	/**
	 * Return the template folder path
	 *
	 * This returns the path of the template files,
	 *
	 * @return string
	 */
	protected function get_templates_path() {
		return Plugin::plugin_path() . '/views/blocks/' . $this->get_block_handle();
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
	 * @return array
	 */
	protected function extend_mustache_helpers() {
		return [];
	}

	/**
	 * Get block namespace
	 *
	 * @param boolean $camelcase
	 * @return string
	 */
	protected function get_namespace( $camelcase = false ) {
		return ( ! $camelcase ) ? strtolower( $this->namespace ) : ucwords( $this->namespace );
	}

	/**
	 * Get block name
	 *
	 * @param boolean $camelcase
	 * @return string
	 */
	protected function get_block_name( $camelcase = false ) {
		return ( ! $camelcase ) ? strtolower( $this->block_name ) : ucwords( $this->block_name );
	}

	/**
	 * Get the full block name including namespace
	 *
	 * @return string
	 */
	protected function get_full_block_name() {
		return $this->get_unspaced( $this->get_namespace() ) . '/' . $this->get_dashed( $this->get_block_name() );
	}

	/**
	 * Set a block description
	 *
	 * Used for translating the block description
	 *
	 * @param string $description
	 * @return object Returns the class instance
	 */
	protected function set_block_description( $description ) {
		$this->block_description = $description;

		return $this;
	}

	/**
	 * Get block handle
	 *
	 * @return string
	 */
	protected function get_block_handle() {
		return $this->get_underscored( $this->get_unspaced( $this->get_namespace() ) . '_' . $this->get_block_name() );
	}

	/**
	 * Get block category name
	 *
	 * @return string
	 */
	protected function get_category_name() {
		return ucwords( strtolower( $this->namespace ) );
	}

	/**
	 * Get block category slug
	 *
	 * @ return string
	 */
	protected function get_category_slug() {
		return strtolower( $this->get_dashed( $this->get_category_name() ) ) . '-blocks';
	}

	/**
	 * Return string with underscore and dashes replaced
	 * with spaces
	 *
	 * @param string $string
	 * @return string
	 */
	protected function get_spaced( $string = '' ) {
		return str_ireplace( [ '_', '-' ], ' ', $string );
	}

	/**
	 * Return string with underscore, dashes and spaces stripped
	 *
	 * @param string $string
	 * @return string
	 */
	protected function get_unspaced( $string = '' ) {
		return str_ireplace( [ '_', '-', ' ' ], '', $string );
	}

	/**
	 * Return string with spaces and underscore replaced
	 * with dashes
	 *
	 * @param string $string
	 * @return string
	 */
	protected function get_dashed( $string = '' ) {
		$string = sanitize_title( $string );
		return str_ireplace( [ ' ', '_' ], '-', $string );
	}

	/**
	 * Return string with spaces and dashes replaced
	 * with underscores
	 *
	 * @param string $string
	 * @return string
	 */
	protected function get_underscored( $string = '' ) {
		$string = sanitize_title( $string );
		return str_ireplace( [ ' ', '-' ], '_', $string );
	}

	/**
	 * Check if include type is available
	 *
	 * @return bool
	 */
	protected function is_include() {
		$include_types = [
			'hx:include',
			'esi:include',
			'xhr:include',
		];

		return ( $this->include && in_array( $this->include_type, $include_types ) ) ? true : false;
	}

	/**
	 * Adds hx:include tag, instead of block
	 *
	 * If the hx:include parameter is set, this adds a <xh:include> tag
	 * instead of the block, when webinclude is not in the request URI.
	 *
	 * The <hx:include> tag loads content from the source, which is dynamically
	 * generated for the specific block, using the Rewrite API.
	 *
	 * @param string $block_content
	 * @param array $block
	 * @return void
	 */
	public function include_add( $block_content, $block ) {
		if ( ! is_admin() && ! stristr( $_SERVER['REQUEST_URI'], 'webinclude' ) && $block['blockName'] == $this->get_full_block_name() ) {
			$tag = '';
			$url = home_url( 'webinclude/blocks/' . $this->get_full_block_name() . '/' . get_the_ID() . '/' );

			switch ( $this->include_type ) {
				case 'esi:include':
					$tag = '<esi:include src="%s"></esi:include>';
					$url = $url . '?' . time();
					break;
				case 'xhr:include':
					$tag = '<div data-include data-include-src="%s"></div>';
					break;
				case 'hx:include':
					$tag = '<hx:include src="%s"></hx:include>';
					$url = $url . '?' . time();
					break;
				default:
					$tag = '<hx:include src="%s"></hx:include>';
					$url = $url . '?' . time();
					break;
			}
			return sprintf( $tag, $url );
		}

		return $block_content;
	}

	/**
	 * Generates a Rewrite URL for the block
	 *
	 * This generates a dynamic URL for the block, which is used
	 * when using the include.
	 *
	 * @return void
	 */
	public function include_rewrite() {
		add_rewrite_rule(
			'webinclude/blocks/([^/]+)/([^/]+)/([^/]+)/?$',
			'index.php?pagename=webinclude&include=true&block_namespace=$matches[1]&block_name=$matches[2]&block_post_id=$matches[3]',
			'top'
		);
	}

	/**
	 * Sets the query vars for the include
	 *
	 * @param array $vars
	 * @return array
	 */
	public function include_query_vars( $vars ) {
		$vars[] = 'include';
		$vars[] = 'block_namespace';
		$vars[] = 'block_name';
		$vars[] = 'block_post_id';

		return $vars;
	}

	/**
	 * Output the specific block for include
	 *
	 * This outputs the specific block for the include.
	 *
	 * The post content is parsed and the block is rendered,
	 * based on the parameters, that were dynamically passed,
	 * via query vars.
	 *
	 * @return void
	 */
	public function include_render_block() {
		if ( get_query_var( 'include' ) ) {
			// set header status to 200
			status_header( 200 );
			header_remove( 'Cache-Control' );
			header( 'Cache-Control: no-cache, must-revalidate, max-age=0' );

			$block_name = get_query_var( 'block_namespace' ) . '/' . get_query_var( 'block_name' );
			$content    = get_post_field( 'post_content', get_query_var( 'block_post_id' ) );
			$blocks     = parse_blocks( $content );

			foreach ( $blocks as $block ) {
				if ( $block_name === $block['blockName'] ) {
					echo render_block( $block );
					break;
				}
			}
			exit();
		}
	}

	/**
	 * The debug method
	 *
	 * Displays debug information about the block
	 *
	 * @param array $attributes
	 * @param string $content
	 * @return void
	 */
	public function debug( $attributes, $content ) {
		$template = $this->get_template();
		$model    = $this->get_model( $attributes, $content );
		echo '<div style="max-width: 100%; overflow-y: scroll; background-color: #eee;">';
		s( $this->get_block_name() );
		s( $this->get_namespace() );
		s( $this->get_block_handle() );
		s( $this->get_block_classes() );
		s( $template );
		s( $model );
		echo '</div>';
	}

	/**
	 * This method is used to get the extending
	 * classes FILE path, as __FILE__ will not work
	 * with parent class.
	 *
	 * @return string
	 */
	protected function get_file_path() {
		$c = new ReflectionClass( $this );
		return $c->getFileName();
	}

	/**
	 * Render block schema
	 * Get $graph, $block and $context from wpseo's filter
	 *
	 * @param array $graph
	 * @param array $block
	 * @param array $context
	 * @return array Updated graph
	 */
	public function render_block_schema( $graph, $block, $context ) {
		$graph = $this->extend_schema_graph( $graph, $this->get_model( $block['attrs'], $block['innerContent'] ) );
		return $graph;
	}

	/**
	 * Schema graph
	 *
	 * This method can be used to extend
	 * the schema graph , from a child class
	 * if you do not wish to use the filter direct.
	 *
	 * @param array $graph
	 * @param array $model
	 * @return array Updated graph
	 */
	protected function extend_schema_graph( $graph, $model ) {
		return $graph;
	}

	/**
	 * Find the first graph piece with a @type
	 * Helperfunction
	 *
	 * @param string $piece_type // the type to search for
	 * @param array $graph
	 * @return int the key to the graph piece
	 */
	public static function find_graph_piece( $piece_type, $graph ) {
		foreach ( $graph as $piece_key => $piece ) {
			// if @type is a sting, compare
			if ( is_string( $piece['@type'] ) && $piece['@type'] == $piece_type ) {
				return $piece_key;
				// if @type is a array, compare each
			} elseif ( is_array( $piece['@type'] ) ) {
				foreach ( $piece['@type'] as $type ) {
					if ( $type == $piece_type ) {
						return $piece_key;
					}
				}
			}
		}
	}

}
