<?php

namespace Sugar_Calendar\AddOn\Ticketing\Admin\Metabox;

// Exit if accessed directly
use Sugar_Calendar\AddOn\Ticketing\Helpers\UI;

defined( 'ABSPATH' ) || exit;

/**
 * Register our Tickets metabox
 *
 * @since 1.0.0
 */
function metabox( $metabox ) {

	$metabox->add_section( [
		'id'       => 'tickets',
		'label'    => esc_html__( 'Tickets', 'sugar-calendar' ),
		'icon'     => 'tickets-alt',
		'order'    => 80,
		'callback' => __NAMESPACE__ . '\\metabox_section',
	] );
}

/**
 * Render the Tickets metabox section
 *
 * @since 1.0.0
 */
function metabox_section( $event = null ) {

	$enabled  = get_event_meta( $event->ID, 'tickets', true );
	$price    = get_event_meta( $event->ID, 'ticket_price', true );
	$quantity = get_event_meta( $event->ID, 'ticket_quantity', true );

	// Start an output buffer
	ob_start(); ?>

    <div class="sugar-calendar-metabox__field-row">
        <p class="desc"><?php esc_html_e( 'Configure the setting below to enable and sell tickets for this event.', 'sugar-calendar' ); ?></p>
    </div>

    <div class="sugar-calendar-metabox__field-row sugar-calendar-metabox__field-row--enable_tickets">
        <label for="enable_tickets"><?php esc_html_e( 'Ticket Sales', 'sugar-calendar' ); ?></label>
        <div class="sugar-calendar-metabox__field">
			<?php
			UI::toggle_control(
				[
					'id'            => 'enable_tickets',
					'name'          => 'enable_tickets',
					'value'         => $enabled,
					'toggle_labels' => [
						esc_html__( 'ON', 'sugar-calendar' ),
						esc_html__( 'OFF', 'sugar-calendar' ),
					],
				],
				true
			);
			?>
        </div>
    </div>

    <div class="sugar-calendar-metabox__field-row sugar-calendar-metabox__field-row--ticket_price">
        <label for="ticket_price"><?php esc_html_e( 'Ticket Price', 'sugar-calendar' ); ?></label>
        <div class="sugar-calendar-metabox__field">
            <input name="ticket_price" id="ticket_price" type="text" inputmode="numeric" autocomplete="off" placeholder="0.00" pattern="^[0-9]{1,18}([,.][0-9]{1,9})?$" data-lpignore="true" value="<?php echo esc_attr( $price ); ?>"/>
        </div>
    </div>

    <div class="sugar-calendar-metabox__field-row sugar-calendar-metabox__field-row--ticket_quantity">
        <label for="ticket_quantity"><?php esc_html_e( 'Capacity', 'sugar-calendar' ); ?></label>
        <div class="sugar-calendar-metabox__field">
            <input name="ticket_quantity" id="ticket_quantity" type="number" inputmode="numeric" autocomplete="off" min="0" step="1" placeholder="0" pattern="[0-9]" data-lpignore="true" value="<?php echo esc_attr( $quantity ); ?>"/>
        </div>
    </div>

	<?php
	do_action( 'sc_et_metabox_bottom', $event );

	// End & flush the current output buffer
	echo ob_get_clean();
}
