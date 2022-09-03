<?php

namespace BTBEvents\Plugin\Core;

use BTBEvents\Plugin\Plugin;

class Bootstrap {

	public function __construct() {
		$this->init();
	}

	/**
	 * Run core bootstrap hooks.
	 */
	public function init() {
		new PostTypes\Events();
	}

}
