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

namespace BTB\Events;

use BTB\Events\Plugin;

// Do not access this file directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require 'kint.phar';

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
} else {
	add_action(
		'admin_notices',
		function () {
			printf( '<div class="error"><p>Could not find vendor folder in %s</p></div>', dirname( __FILE__ ) );
		}
	);

	return;
}

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
