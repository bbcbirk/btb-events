<?php

namespace BTB\Events\Admin\EventCalendar;

use BTB\Events\Plugin;
use BTB\Events\Core\PostTypes\Events;

class EventCalendar {

	public function __construct() {
		$this->init();
	}

	public function init() {
		//Make an admin sub_menu page for a calendar view of the events
		add_action( 'admin_menu', [ $this, 'add_event_subpages' ] );
		add_action( 'btb_admin_calendar_view', [ $this, 'display_admin_calendar' ] );
		add_filter( 'btb_events_base-admin_options', [ $this, 'add_events_to_calendar_view' ] );
	}

	public function add_event_subpages() {
		add_submenu_page(
			'edit.php?post_type=' . Events::get_post_type(),
			__( 'Calendar View', Plugin::get_text_domain() ),
			__( 'Calendar', Plugin::get_text_domain() ),
			'manage_options',
			'calendar_view',
			[ __CLASS__, 'calendar_view_template' ]
		);
	}

	public static function calendar_view_template() {
		?>
		<div class="wrap">

			<h1 class="wp-heading-inline"><?php echo apply_filters( 'btb_admin_calendar_view_title', __( 'Calendar View', Plugin::get_text_domain() ) ); ?></h1>

			<hr class="wp-header-end">

			<?php do_action( 'btb_before_admin_calendar_view' ); ?>

			<?php do_action( 'btb_admin_calendar_view' ); ?>

			<?php do_action( 'btb_after_admin_calendar_view' ); ?>

		</div>
		<?php
	}

	public function display_admin_calendar() {
		echo '<div id="calendar_view"></div>';
	}

	public function add_events_to_calendar_view( $options = [] ) {
		$options['event_feed'] = get_rest_url( null, 'btbevents/v1/admin/calendar' );

		return $options;
	}

}
