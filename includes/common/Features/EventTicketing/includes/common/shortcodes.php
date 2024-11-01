<?php
namespace Sugar_Calendar\AddOn\Ticketing\Shortcodes;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use Sugar_Calendar\AddOn\Ticketing\Common\Functions as Functions;
use Sugar_Calendar\AddOn\Ticketing\Settings as Settings;

/**
 * Outputs the content for the [sc_event_tickets_receipt] shortcode.
 *
 * @since 1.0.0
 *
 * @return string
 */
function receipt_shortcode() {
	if ( empty( $_GET['order_id'] ) ) {
		return '<div class="sc-et-error alert alert-danger" role="alert">' . esc_html__( 'No  order ID was specified.', 'sugar-calendar' ) . '</div>';
	}

	if ( empty( $_GET['email'] ) ) {
		return '<div class="sc-et-error alert alert-danger" role="alert">' . esc_html__( 'No purchaser email was specified.', 'sugar-calendar' ) . '</div>';
	}

	$order_id = absint( $_GET['order_id'] );
	$email    = sanitize_text_field( $_GET['email'] );

	$order = Functions\get_order( $order_id );

	if ( $email !== $order->email ) {
		return '<div class="sc-et-error alert alert-danger" role="alert">' . esc_html__( 'The specified email does not match the email on the requested order.', 'sugar-calendar' ) . '</div>';
	}

	// Event date/time
	$event      = sugar_calendar_get_event( $order->event_id );
	$start_date = $event->format_date( sc_get_date_format(), $event->start );
	$start_time = $event->format_date( sc_get_time_format(), $event->start );

	if ( ! empty( $_GET['sc-notice'] ) && ( 'email-sent' === $_GET['sc-notice'] ) ) {
		echo '<div class="alert alert-success" role="alert">' . esc_html__( 'Ticket emailed successfully.', 'sugar-calendar' ) . '</div>';
	}

	$page = Settings\get_setting( 'ticket_page' );
	$link = get_permalink( $page );
	$home = home_url();

	// TODO: Replace table markup
	ob_start(); ?>

	<div id="sc-event-ticketing-ticket-details">
		<table>
			<tr>
				<th><?php esc_html_e( 'Order #', 'sugar-calendar' ); ?></th>
				<th><?php esc_html_e( 'Purchaser', 'sugar-calendar' ); ?></th>
				<th><?php esc_html_e( 'Date', 'sugar-calendar' ); ?></th>
			</tr>
			<tr>
				<td><?php echo esc_html( $order->id ); ?></td>
				<td><?php echo esc_html( $order->first_name . ' ' . $order->last_name ) . ' (' . esc_html( $order->email ) . ')'; ?></td>
				<td><?php echo esc_html( $order->date_created ); ?></td>
			</tr>
			<tr>
				<th colspan="3"><?php esc_html_e( 'Amount', 'sugar-calendar' ); ?></th>
			</tr>
			<tr>
				<td colspan="3"><?php echo Functions\currency_filter( $order->total ); ?></td>
			</tr>
			<tr>
				<th colspan="3"><?php esc_html_e( 'Transaction ID', 'sugar-calendar' ); ?></th>
			</tr>
			<tr>
				<td colspan="3"><?php echo esc_html( $order->transaction_id ); ?></td>
			</tr>
			<tr>
				<th colspan="3"><?php esc_html_e( 'Status', 'sugar-calendar' ); ?></th>
			</tr>
			<tr>
				<td colspan="3"><?php echo Functions\order_status_label( $order->status ); ?></td>
			</tr>
			<tr>
				<th colspan="3"><?php esc_html_e( 'Location', 'sugar-calendar' ); ?></th>
			</tr>
			<tr>
				<td colspan="3"><?php echo get_event_meta( $event->id, 'location', true ); ?></td>
			</tr>
			<tr>
				<th colspan="3"><?php esc_html_e( 'Event', 'sugar-calendar' ); ?></th>
			</tr>
			<tr>
				<td><?php echo esc_html( $event->title ); ?></td>
				<td><?php printf( esc_html__( '%s at %s', 'sugar-calendar' ), $start_date, $start_time ); ?></td>
				<td><a href="<?php echo get_permalink( $event->object_id ); ?>"><?php esc_html_e( 'View event details', 'sugar-calendar' ); ?></a></td>
			</tr>
			<tr>
				<th colspan="3"><?php esc_html_e( 'Tickets', 'sugar-calendar' ); ?></th>
			</tr>
			<tr>
				<td colspan="3">
					<ul>
						<?php foreach ( Functions\get_order_tickets( $order->id ) as $ticket ) : ?>
							<li>
								<?php $attendee = Functions\get_attendee( $ticket->attendee_id ); ?>
								<div class="sc-event-ticketing-ticket-id"><?php printf( esc_html__( 'Ticket ID: %d', 'sugar-calendar' ), $ticket->id ); ?></div>
								<div class="sc-event-ticketing-ticket-code"><?php printf( esc_html__( 'Ticket Code: %s', 'sugar-calendar' ), $ticket->code ); ?></div>

								<?php if ( ! empty( $attendee ) ) : ?>

									<div class="sc-event-ticketing-attendee"><?php printf( esc_html__( 'For: %s', 'sugar-calendar' ), esc_html( $attendee->first_name . ' ' . $attendee->last_name ) . ', ' . esc_html( $attendee->email ) ); ?></div>
									<a href="<?php echo wp_nonce_url( add_query_arg( array( 'sc_et_action' => 'email_ticket', 'ticket_code' => $ticket->code ), $home ), $ticket->code ); ?>"><?php esc_html_e( 'Send via Email', 'sugar-calendar' ); ?></a>
									&nbsp;|&nbsp;

								<?php endif; ?>

								<a href="<?php echo wp_nonce_url( add_query_arg( array( 'sc_et_action' => 'print', 'ticket_code' => $ticket->code ), $home ), $ticket->code ); ?>" target="_blank"><?php esc_html_e( 'Print', 'sugar-calendar' ); ?></a>
								&nbsp;|&nbsp;<a href="<?php echo add_query_arg( array( 'order_id' => $order_id, 'ticket_code' => $ticket->code ), $link ); ?>"><?php esc_html_e( 'View', 'sugar-calendar' ); ?></a>
							</li>
						<?php endforeach; ?>
					</ul>
				</td>
			</tr>
		</table>
	</div>

	<?php

	return apply_filters( 'sc_event_tickets_ticket_shortcode_output', ob_get_clean() );
}

/**
 * Outputs the content for the [sc_event_tickets_details] shortcode.
 *
 * @since 1.0.0
 *
 * @return string
 */
function ticket_shortcode() {

	if ( empty( $_GET['order_id'] ) ) {
		return '<div class="sc-et-error alert alert-danger" role="alert">' . esc_html__( 'No order ID was specified.', 'sugar-calendar' ) . '</div>';
	}

	if ( empty( $_GET['ticket_code'] ) ) {
		return '<div class="sc-et-error alert alert-danger" role="alert">' . esc_html__( 'No ticket code was specified.', 'sugar-calendar' ) . '</div>';
	}

	$order_id    = absint( $_GET['order_id'] );
	$ticket_code = sanitize_text_field( $_GET['ticket_code'] );

	$order  = Functions\get_order( $order_id );
	$ticket = Functions\get_ticket_by_code( $ticket_code );

	// Event date/time
	$event      = sugar_calendar_get_event( $ticket->event_id );
	$start_date = $event->format_date( sc_get_date_format(), $event->start );
	$start_time = $event->format_date( sc_get_time_format(), $event->start );

	$attendee = ! empty( $ticket->attendee_id )
		? Functions\get_attendee( $ticket->attendee_id )
		: false;

	// TODO: Replace table markup
	ob_start(); ?>

	<div id="sc-event-ticketing-ticket-details">
		<h3><?php echo $event->title; ?></h3>
		<h4><?php printf( esc_html__( '%s at %s', 'sugar-calendar' ), $start_date, $start_time ); ?></h4>
		<table>
			<tr>
				<th><?php esc_html_e( 'Ticket #',  'sugar-calendar' ); ?></th>
				<th><?php esc_html_e( 'Purchaser', 'sugar-calendar' ); ?></th>
				<th><?php esc_html_e( 'Code',      'sugar-calendar' ); ?></th>
			</tr>
			<tr>
				<td><?php echo esc_html( $ticket->id ); ?></td>
				<td><?php echo esc_html( $order->first_name . ' ' . $order->last_name ); ?></td>
				<td><?php echo esc_html( $ticket->code ); ?></td>
			</tr>
			<tr>
				<th colspan="3"><?php esc_html_e( 'Location', 'sugar-calendar' ); ?></th>
			</tr>
			<tr>
				<td colspan="3"><?php echo get_event_meta( $event->id, 'location', true ); ?></td>
			</tr>
			<?php if ( ! empty( $attendee ) ) : ?>
				<tr>
					<th colspan="3"><?php esc_html_e( 'Attendee', 'sugar-calendar' ); ?></th>
				</tr>
				<tr>
					<td colspan="3"><?php echo esc_html( $attendee->first_name . ' ' . $attendee->last_name ); ?></td>
				</tr>
			<?php endif; ?>
			<tr>
				<th colspan="3"><?php esc_html_e( 'Event Details', 'sugar-calendar' ); ?></th>
			</tr>
			<tr>
				<td colspan="3"><a href="<?php echo get_permalink( $event->object_id ); ?>"><?php esc_html_e( 'View event details', 'sugar-calendar' ); ?></a></td>
			</tr>
		</table>
	</div>

	<?php

	return apply_filters( 'sc_event_tickets_ticket_shortcode_output', ob_get_clean() );
}
