<?php
/**
 * Plugin Name:       Btb Events
 * Description:       Example static block scaffolded with Create Block tool.
 * Requires at least: 5.9
 * Requires PHP:      7.0
 * Version:           0.1.0
 * Author:            Birk Thestrup Blauner
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       btb-events
 *
 * @package           create-block
 */

namespace BTBEvents\Plugin;

use BTBEvents\Plugin\Plugin;

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function create_block_btb_events_block_init() {
	register_block_type( __DIR__ . '/build' );
}
add_action( 'init', 'create_block_btb_events_block_init' );

// Register plugin specific hooks
register_activation_hook( __FILE__, [ __NAMESPACE__ . '\Plugin', 'activate' ] );
register_deactivation_hook( __FILE__, [ __NAMESPACE__ . '\Plugin', 'deactivate' ] );

/**
 * Load the plugin
 *
 * @return void
 */
function plugin() {
	if ( class_exists( __NAMESPACE__ . '\Plugin' ) ) {
		return Plugin::instance();
	}
}

plugin();
