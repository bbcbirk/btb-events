<?php

namespace BTB\Events\Core;

use BTB\Events\Plugin;
use BTB\Events\Core\PostTypes\Meta\EventsMeta;

class Bootstrap {

	public function __construct() {
		$this->init();
	}

	/**
	 * Run core bootstrap hooks.
	 */
	public function init() {
		( new Assets )->register();
		( new Assets )->load();
		new PostTypes\Events();
		new REST\Events();

		add_action( 'wp', [ $this, 'test' ] );
	}

	public function test() {
		//d( EventsMeta::get_meta_data( get_the_ID() ) );
	}

}
