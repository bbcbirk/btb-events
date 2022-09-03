<?php

namespace BTBEvents\Plugin\Frontend;

use BTBEvents\Plugin\Plugin;

class Bootstrap {

	public function __construct() {
		$this->init();
	}

	/**
	 * Run core bootstrap hooks.
	 */
	public function init() {
		new Events();
	}

}
