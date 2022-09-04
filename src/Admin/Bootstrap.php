<?php

namespace BTB\Events\Admin;

use BTB\Events\Plugin;

class Bootstrap {

	public function __construct() {
		$this->init();
	}

	/**
	 * Run core bootstrap hooks.
	 */
	public function init() {
		new EventCalendar\EventCalendar();
	}

}
