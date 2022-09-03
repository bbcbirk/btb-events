<?php

namespace BTBEvents\Plugin\Core;

use BTBEvents\Plugin\Plugin;
use BTBEvents\Plugin\Core\PostTypes\Meta\EventsMeta;

class Bootstrap {

	public function __construct() {
		$this->init();
	}

	/**
	 * Run core bootstrap hooks.
	 */
	public function init() {
		new PostTypes\Events();

		add_action( 'wp', [ $this, 'test' ] );
	}

	public function test() {
		//d( EventsMeta::get_meta_data( get_the_ID() ) );
	}

}
