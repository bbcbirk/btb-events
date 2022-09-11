<?php

namespace BTB\Events\Core\Blocks;

use BTB\Events\Plugin;

class Bootstrap {

	public function __construct() {
		$this->init();
	}

	/**
	 * Run core bootstrap hooks.
	 */
	public function init() {

		new EventTeaser\Block();
		new Calendar\Block();

	}

}
