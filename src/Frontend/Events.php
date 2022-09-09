<?php

namespace BTB\Events\Frontend;

use BTB\Events\Plugin;
use BTB\Events\Core\PostTypes\Meta\EventsMeta;

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
		<table>
			<tbody>

				<?php if ( isset( $meta[ EventsMeta::get_meta_key( 'start' ) ] ) && ! empty( $meta[ EventsMeta::get_meta_key( 'start' ) ] ) ) : ?>
				<tr>
					<th>
						<?php _e( 'Start', Plugin::get_text_domain() ); ?>
					</th>
					<td>
						<?php echo wp_date( 'Y/m/d h:i', $meta[ EventsMeta::get_meta_key( 'start' ) ] ); ?>
					</td>
				</tr>
				<?php endif; ?>

				<?php if ( isset( $meta[ EventsMeta::get_meta_key( 'end' ) ] ) && ! empty( $meta[ EventsMeta::get_meta_key( 'end' ) ] ) ) : ?>
				<tr>
					<th>
						<?php _e( 'End', Plugin::get_text_domain() ); ?>
					</th>
					<td>
						<?php echo wp_date( 'Y/m/d h:i', $meta[ EventsMeta::get_meta_key( 'end' ) ] ); ?>
					</td>
				</tr>
				<?php endif; ?>

				<?php if ( isset( $meta[ EventsMeta::get_meta_key( 'location' ) ] ) && ! empty( $meta[ EventsMeta::get_meta_key( 'location' ) ] ) ) : ?>
				<tr>
					<th>
						<?php _e( 'Location', Plugin::get_text_domain() ); ?>
					</th>
					<td>
						<?php echo '<a href="https://google.com/maps/search/' . urlencode_deep( $meta[ EventsMeta::get_meta_key( 'location' ) ] ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $meta[ EventsMeta::get_meta_key( 'location' ) ] ) . '</a>'; ?>
					</td>
				</tr>
				<?php endif; ?>

				<?php if ( isset( $meta[ EventsMeta::get_meta_key( 'artist' ) ] ) && ! empty( $meta[ EventsMeta::get_meta_key( 'artist' ) ] ) ) : ?>
				<tr>
					<th>
						<?php _e( 'Artist', Plugin::get_text_domain() ); ?>
					</th>
					<td>
						<?php echo esc_html( $meta[ EventsMeta::get_meta_key( 'artist' ) ] ); ?>
					</td>
				</tr>
				<?php endif; ?>

				<?php if ( isset( $meta[ EventsMeta::get_meta_key( 'featuring' ) ] ) && ! empty( $meta[ EventsMeta::get_meta_key( 'featuring' ) ] ) ) : ?>
				<tr>
					<th>
						<?php _e( 'Featuring', Plugin::get_text_domain() ); ?>
					</th>
					<td>
						<?php echo esc_html( $meta[ EventsMeta::get_meta_key( 'featuring' ) ] ); ?>
					</td>
				</tr>
				<?php endif; ?>

				<?php if ( isset( $meta[ EventsMeta::get_meta_key( 'price' ) ] ) && ! empty( $meta[ EventsMeta::get_meta_key( 'price' ) ] ) ) : ?>
				<tr>
					<th>
						<?php _e( 'Ticket Price', Plugin::get_text_domain() ); ?>
					</th>
					<td>
						<?php echo esc_html( $meta[ EventsMeta::get_meta_key( 'price' ) ] ) . 'DKK'; ?>
					</td>
				</tr>
				<?php endif; ?>

				<?php if ( isset( $meta[ EventsMeta::get_meta_key( 'link' ) ] ) && ! empty( $meta[ EventsMeta::get_meta_key( 'link' ) ] ) ) : ?>
				<tr>
					<th>
						<?php _e( 'Link to event', Plugin::get_text_domain() ); ?>
					</th>
					<td>
						<a href="<?php echo esc_url( $meta[ EventsMeta::get_meta_key( 'link' ) ] ); ?>">
						<?php if ( isset( $meta[ EventsMeta::get_meta_key( 'link_text' ) ] ) && ! empty( $meta[ EventsMeta::get_meta_key( 'link_text' ) ] ) ) : ?>
							<?php echo esc_html( $meta[ EventsMeta::get_meta_key( 'link_text' ) ] ); ?>
						<?php else : ?>
							<?php _e( 'Read More', Plugin::get_text_domain() ); ?>
						<?php endif; ?>
						</a>
					</td>
				</tr>
				<?php endif; ?>

			</tbody>
		</table>
		<?php
		$data = ob_get_contents();
		ob_end_clean();
		return $data;
	}

}
