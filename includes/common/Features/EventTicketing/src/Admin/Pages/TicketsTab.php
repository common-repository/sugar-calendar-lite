<?php

namespace Sugar_Calendar\AddOn\Ticketing\Admin\Pages;

use Sugar_Calendar\Helpers\WP;
use function Sugar_Calendar\AddOn\Ticketing\Common\Assets\get_url;

/**
 * Tickets page.
 *
 * @since 1.2.0
 */
class TicketsTab extends Tickets {

	/**
	 * Page tab slug.
	 *
	 * @since 1.2.0
	 *
	 * @return string
	 */
	public static function get_tab_slug() {

		return 'tickets';
	}

	/**
	 * Page title.
	 *
	 * @since 1.2.0
	 *
	 * @return string
	 */
	public static function get_title() {

		return esc_html__( 'Tickets', 'sugar-calendar' );
	}

	/**
	 * Register page hooks.
	 *
	 * @since 1.2.0
	 */
	public function hooks() {

		add_action( 'sugar_calendar_admin_area_enqueue_assets', [ $this, 'enqueue_assets' ] );
	}

	/**
	 * Display a tab's content.
	 *
	 * @since 1.2.0
	 */
	protected function display_tab() {

		$wp_list_table = new \Sugar_Calendar\AddOn\Ticketing\Admin\Tickets\List_Table();

		// Query for orders/tickets
		$wp_list_table->prepare_items();
		$wp_list_table->views();
		?>

        <form id="posts-filter" method="get">

			<?php $wp_list_table->search_box( 'Search', 'sc_event_tickets_search' ); ?>

            <input type="hidden" name="page" value="sc-event-ticketing"/>

			<?php $wp_list_table->display(); ?>

        </form>

		<?php
	}

	/**
	 * Enqueue assets.
	 *
	 * @since 1.2.0
	 *
	 * @return void
	 */
	public function enqueue_assets() {

		wp_enqueue_style(
			'sugar-calendar-ticketing-admin-tickets',
			get_url( 'css' ) . '/admin-tickets' . WP::asset_min() . '.css',
			[],
			SC_PLUGIN_VERSION
		);
	}
}
