<?php

namespace BTB\Events\Core\Blocks\EventTeaser;

use BTB\Events\Plugin;
use BTB\Events\Abstracts\BlockBase;
use BTB\Events\Core\Data\Events as DATA;
use BTB\Events\Core\PostTypes\Meta\EventsMeta;

use WP_Query;

class Block extends BlockBase {

	/**
	 * Block namespace
	 *
	 * @var string
	 */
	protected $namespace = 'BTB';

	/**
	 * The block name
	 *
	 * @var string
	 */
	protected $block_name = 'Event Teaser';

	/**
	 * Block icon, dashicon classes used
	 *
	 * @var string
	 */
	protected $icon = 'calendar';

	/**
	 * Block icon background color
	 *
	 * @var string
	 */
	protected $background_color = '#6667AB';

	/**
	 * Block icon foreground color
	 *
	 * @var string
	 */
	protected $foreground_color = '#FFFFFF';

	/**
	 * Enable debugging
	 *
	 * @var boolean
	 */
	protected $debug = false;

	public function init() {
		$this->set_block_description( __( 'List of events.', Plugin::get_text_domain() ) );
	}

	/**
	 * Set the attributes for the block
	 *
	 * @return array
	 */
	protected function block_attributes() {
		return [
			'blockId'              => [
				'type' => 'string',
			],
			'title'                => [
				'type'    => 'string',
				'default' => '',
			],
			'content'              => [
				'type'    => 'string',
				'default' => '',
			],
			'textAlignment'        => [
				'type'    => 'string',
				'default' => 'left',
			],
			'bgColor'              => [
				'type' => 'string',
			],
			'textColor'            => [
				'type' => 'string',
			],
			'bottomSpacing'        => [
				'type'    => 'boolean',
				'default' => false,
			],
			'topSpacing'           => [
				'type'    => 'boolean',
				'default' => false,
			],
			'htmlAnchor'           => [
				'type'    => 'string',
				'default' => '',
			],
			'className'            => [
				'type'    => 'string',
				'default' => '',
			],
			'wpquery'              => [
				'type'    => 'object',
				'default' => [
					'order'         => [
						'value' => 'desc',
						'label' => __( 'Descending', Plugin::get_text_domain() ),
					],
					'orderBy'       => [
						'value' => 'date',
						'label' => __( 'Date', Plugin::get_text_domain() ),
					],
					'numberOfItems' => 5,
					'offset'        => 0,
				],
				'query'   => [
					'order'         => [
						'type' => 'string',
					],
					'orderBy'       => [
						'type' => 'string',
					],
					'numberOfItems' => [
						'type' => 'number',
					],
					'offset'        => [
						'type' => 'number',
					],
				],
			],
			'displayAddToCalendar' => [
				'type'    => 'boolean',
				'default' => true,
			],
			'renderFromServer'     => [
				'type'    => 'boolean',
				'default' => false,
			],
		];
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
		if ( $this->get_attribute( 'renderFromServer' ) === true ) {
			return 'editor';
		}
		return 'block';
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
		$wpquery = $this->get_attribute( 'wpquery' );
		$args    = [
			'post_per_page' => isset( $wpquery['numberOfPosts'] ) ? $wpquery['numberOfPosts'] : 5,
			'offset'        => isset( $wpquery['offset'] ) ? $wpquery['offset'] : 0,
			'order'         => isset( $wpquery['order'] ) ? $wpquery['order'] : 'desc',
			'orderby'       => isset( $wpquery['orderby'] ) ? $wpquery['orderby'] : 'date',
		];

		if ( $args['orderby'] == 'date' ) {
			$args['meta_key'] = EventsMeta::get_meta_key( 'start' );
			$args['orderby']  = [ 'meta_value_num', 'date' ];
		}

		$events = DATA::get_events( $args );

		$model['events']      = $events;
		$model['eventsCount'] = count( $events );

		return $model;
	}

	/**
	 * set the classes for the block
	 *
	 * @param array $classes
	 * @return array
	 */
	protected function extend_block_classes( $classes ) {

		if ( $this->get_attribute( 'topSpacing' ) ) {
			$classes[] = 'has-spacing-top';
		}

		if ( $this->get_attribute( 'bottomSpacing' ) ) {
			$classes[] = 'has-spacing-bottom';
		}

		if ( $this->get_attribute( 'bgColor' ) ) {
			$classes[] = 'has-background-color';
		}

		return $classes;
	}

	/**
	 * set the styles for the block
	 *
	 * @param array $styles
	 * @return array
	 */
	protected function extend_block_styles( $styles ) {
		$block_class = '.' . $this->get_block_class_name();
		$new_styles  = [];

		$new_styles[] = [
			$block_class        => [
				'background-color' => $this->get_attribute( 'bgColor' ),
				'color'            => $this->get_attribute( 'textColor' ),
				'text-align'       => $this->get_attribute( 'textAlignment' ),
			],
			$block_class . ' a' => [
				'color' => $this->get_attribute( 'textColor' ),
			],
		];

		foreach ( $new_styles as $new_style ) {
			$styles = $styles + $new_style;
		}

		return $styles;
	}

}
