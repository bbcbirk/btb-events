<?php

namespace BTB\Events\Frontend;

use BTB\Events\Plugin;
use BTB\Events\Core\PostTypes\Meta\EventsMeta;
use BTB\Events\Core\Data\Events as DATA;

class Events {

	public function __construct() {
		$this->init();
	}

	/**
	 * Run core bootstrap hooks.
	 */
	public function init() {
		add_filter( 'template_redirect', [ $this, 'lock_private_events' ] );
		add_filter( 'the_content', [ $this, 'event_meta' ] );
	}

	//Prevent Access to private events
	public function lock_private_events() {
		global $post;

		if ( 'btb_events' === $post->post_type ) {
			$is_private = get_post_meta( get_the_ID(), EventsMeta::get_meta_key( 'is_public' ), true );
			if ( empty( $is_private ) ) {
				wp_safe_redirect( home_url() );
				exit();
			}
		}
	}

	//Add Event Meta before content
	public function event_meta( $content ) {
		global $post;

		if ( 'btb_events' === $post->post_type ) {
			$meta = $this->get_event_meta();

			return $meta . $content;

		}

		return $content;
	}

	public function get_event_meta() {
		$meta = EventsMeta::get_meta_data( get_the_ID() );
		ob_start();
		?>
		<div class="btb-event_info-table">

			<?php if ( isset( $meta[ EventsMeta::get_meta_key( 'artist' ) ] ) && ! empty( $meta[ EventsMeta::get_meta_key( 'artist' ) ] ) ) : ?>
			<div class="btb-event_info-table__artist btb-event_info-table__heading">
				<?php _e( 'Who', Plugin::get_text_domain() ); ?>
			</div>
			<div class="btb-event_info-table__artist btb-event_info-table__value">
				<?php echo esc_html( $meta[ EventsMeta::get_meta_key( 'artist' ) ] ); ?>
				<?php if ( isset( $meta[ EventsMeta::get_meta_key( 'featuring' ) ] ) && ! empty( $meta[ EventsMeta::get_meta_key( 'featuring' ) ] ) ) : ?>
				<div class="btb-event_info-table__featuring">
					<?php echo esc_html( $meta[ EventsMeta::get_meta_key( 'featuring' ) ] ); ?>
				</div>
				<?php endif; ?>
			</div>
			<?php endif; ?>

			<?php if ( isset( $meta[ EventsMeta::get_meta_key( 'start' ) ] ) && ! empty( $meta[ EventsMeta::get_meta_key( 'start' ) ] ) ) : ?>
			<div class="btb-event_info-table__when btb-event_info-table__heading">
				<?php _e( 'When', Plugin::get_text_domain() ); ?>
			</div>
			<div class="btb-event_info-table__when btb-event_info-table__value">
				<?php if ( isset( $meta[ EventsMeta::get_meta_key( 'end' ) ] ) && ! empty( $meta[ EventsMeta::get_meta_key( 'end' ) ] ) ) : ?>
					<?php if ( $meta[ EventsMeta::get_meta_key( 'end' ) ] < $meta[ EventsMeta::get_meta_key( 'start' ) ] ) : ?>
						<time datetime="<?php echo wp_date( 'Y-m-d H:i', $meta[ EventsMeta::get_meta_key( 'start' ) ] ); ?>"><?php echo wp_date( 'd-m-Y H:i', $meta[ EventsMeta::get_meta_key( 'start' ) ] ); ?></time>
					<?php else : ?>
						<?php if ( wp_date( 'd-m-Y', $meta[ EventsMeta::get_meta_key( 'start' ) ] ) === wp_date( 'd-m-Y', $meta[ EventsMeta::get_meta_key( 'end' ) ] ) ) : ?>
							<time datetime="<?php echo wp_date( 'Y-m-d H:i', $meta[ EventsMeta::get_meta_key( 'start' ) ] ); ?>"><?php echo wp_date( 'd-m-Y H:i', $meta[ EventsMeta::get_meta_key( 'start' ) ] ) . ' - ' . wp_date( 'H:i', $meta[ EventsMeta::get_meta_key( 'end' ) ] ); ?></time>
						<?php else : ?>
							<time datetime="<?php echo wp_date( 'Y-m-d H:i', $meta[ EventsMeta::get_meta_key( 'start' ) ] ); ?>"><?php echo wp_date( 'd-m-Y H:i', $meta[ EventsMeta::get_meta_key( 'start' ) ] ); ?></time> - <time datetime="<?php echo wp_date( 'Y-m-d H:i', $meta[ EventsMeta::get_meta_key( 'end' ) ] ); ?>"><?php echo wp_date( 'd-m-Y H:i', $meta[ EventsMeta::get_meta_key( 'end' ) ] ); ?></time>					
						<?php endif; ?>
					<?php endif; ?>
				<?php else : ?>
					<time datetime="<?php echo wp_date( 'Y-m-d H:i', $meta[ EventsMeta::get_meta_key( 'start' ) ] ); ?>"><?php echo wp_date( 'd-m-Y H:i', $meta[ EventsMeta::get_meta_key( 'start' ) ] ); ?></time>
				<?php endif; ?>
			</div>
			<?php endif; ?>

			<?php if ( isset( $meta[ EventsMeta::get_meta_key( 'location' ) ] ) && ! empty( $meta[ EventsMeta::get_meta_key( 'location' ) ] ) ) : ?>
			<div class="btb-event_info-table__where btb-event_info-table__heading">
				<?php _e( 'Where', Plugin::get_text_domain() ); ?>
			</div>
			<div class="btb-event_info-table__where btb-event_info-table__value">
				<?php echo '<a href="https://google.com/maps/search/' . urlencode_deep( $meta[ EventsMeta::get_meta_key( 'location' ) ] ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $meta[ EventsMeta::get_meta_key( 'location' ) ] ) . '</a>'; ?>
			</div>
			<?php endif; ?>

			<?php if ( isset( $meta[ EventsMeta::get_meta_key( 'price' ) ] ) && ! empty( $meta[ EventsMeta::get_meta_key( 'price' ) ] ) ) : ?>
			<div class="btb-event_info-table__price btb-event_info-table__heading">
				<?php _e( 'Ticket Price', Plugin::get_text_domain() ); ?>
			</div>
			<div class="btb-event_info-table__price btb-event_info-table__value">
				<?php echo esc_html( $meta[ EventsMeta::get_meta_key( 'price' ) ] ) . ' DKK'; ?>
			</div>
			<?php endif; ?>

			<?php if ( isset( $meta[ EventsMeta::get_meta_key( 'ticket_link' ) ] ) && ! empty( $meta[ EventsMeta::get_meta_key( 'ticket_link' ) ] ) ) : ?>
			<div class="btb-event_info-table__ticket-link btb-event_info-table__heading">
				<?php _e( 'Ticket Link', Plugin::get_text_domain() ); ?>
			</div>
			<div class="btb-event_info-table__ticket-link btb-event_info-table__value">
				<a href="<?php echo esc_url( $meta[ EventsMeta::get_meta_key( 'ticket_link' ) ] ); ?>" target="_blank" rel="noopener noreferrer">
				<?php if ( isset( $meta[ EventsMeta::get_meta_key( 'ticket_link_text' ) ] ) && ! empty( $meta[ EventsMeta::get_meta_key( 'ticket_link_text' ) ] ) ) : ?>
					<?php echo esc_html( $meta[ EventsMeta::get_meta_key( 'ticket_link_text' ) ] ); ?>
				<?php else : ?>
					<?php _e( 'Buy Tickets', Plugin::get_text_domain() ); ?>
				<?php endif; ?>
				</a>
			</div>
			<?php endif; ?>

			<?php if ( isset( $meta[ EventsMeta::get_meta_key( 'link' ) ] ) && ! empty( $meta[ EventsMeta::get_meta_key( 'link' ) ] ) ) : ?>
			<div class="btb-event_info-table__event-link btb-event_info-table__heading">
				<?php _e( 'Link to event', Plugin::get_text_domain() ); ?>
			</div>
			<div class="btb-event_info-table__event-link btb-event_info-table__value">
				<a href="<?php echo esc_url( $meta[ EventsMeta::get_meta_key( 'link' ) ] ); ?>" target="_blank" rel="noopener noreferrer">
				<?php if ( isset( $meta[ EventsMeta::get_meta_key( 'link_text' ) ] ) && ! empty( $meta[ EventsMeta::get_meta_key( 'link_text' ) ] ) ) : ?>
					<?php echo esc_html( $meta[ EventsMeta::get_meta_key( 'link_text' ) ] ); ?>
				<?php else : ?>
					<?php _e( 'Read More', Plugin::get_text_domain() ); ?>
				<?php endif; ?>
				</a>
			</div>
			<?php endif; ?>

			<?php if ( isset( $meta[ EventsMeta::get_meta_key( 'show_add_to_calendar' ) ] ) && ! empty( $meta[ EventsMeta::get_meta_key( 'show_add_to_calendar' ) ] ) ) : ?>
			<div class="btb-event_info-table__add_to_calendar btb-event_info-table__heading"></div>
			<div class="btb-event_info-table__add_to_calendar btb-event_info-table__value">
				<?php echo DATA::get_add_to_calendar_button( get_the_ID() ); ?>
			</div>
			<?php endif; ?>

		</div>
		<?php
		$data = ob_get_contents();
		ob_end_clean();
		return $data;
	}

}
