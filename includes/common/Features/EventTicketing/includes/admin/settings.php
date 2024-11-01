<?php

namespace Sugar_Calendar\AddOn\Ticketing\Admin\Settings;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use Sugar_Calendar\AddOn\Ticketing\Common\Functions as Functions;
use Sugar_Calendar\AddOn\Ticketing\Helpers\UI;
use Sugar_Calendar\AddOn\Ticketing\Settings as Settings;
use Sugar_Calendar\Helpers\Helpers;
use Sugar_Calendar\Helpers\WP;
use Sugar_Calendar\Admin\Area;

/**
 * Register page ids.
 *
 * @since 1.2.0
 *
 * @param string|null $page_id Current page id.
 */
function admin_area_current_page_id( $page_id ) {

	if ( $page_id === 'settings' ) {
		$section = $_GET['section'] ?? 'general'; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		switch ( $section ) {
			case 'payments':
				$page_id = 'settings_payments';
				break;

			case 'tickets':
				$page_id = 'settings_tickets';
				break;
		}
	}

	return $page_id;
}

/**
 * Register page classes.
 *
 * @since 1.2.0
 *
 * @return PageInterface[]
 */
function admin_area_pages( $pages ) {

	$pages['settings_payments'] = $pages['settings'];
	$pages['settings_tickets']  = $pages['settings'];

	return $pages;
}

/**
 * Return the URL used for the section.
 *
 * @since 1.0.0
 *
 * @param array $args
 *
 * @return string
 */
function get_section_url( $args = [] ) {

	// Parse arguments
	$r = wp_parse_args( $args, [
		'page'    => 'sc-settings',
		'section' => 'tickets',
	] );

	// Return the URL with query args
	return add_query_arg( $r, admin_url( 'admin.php' ) );
}

/**
 * Add the "Feeds" section to the settings sections array.
 *
 * @since 1.0.0
 *
 * @param array $sections
 *
 * @return array
 */
function add_section( $sections = [] ) {

	// Add "Tickets" main section
	$sections['payments'] = [
		'id'   => 'payments',
		'name' => esc_html__( 'Payments', 'sugar-calendar' ),
		'url'  => get_section_url(
			[
				'section' => 'payments',
			]
		),
		'func' => __NAMESPACE__ . '\\section',
	];

	// Add "Tickets" main section
	$sections['tickets'] = [
		'id'   => 'tickets',
		'name' => esc_html__( 'Tickets', 'sugar-calendar' ),
		'url'  => get_section_url(
			[
				'section' => 'tickets',
			]
		),
		'func' => __NAMESPACE__ . '\\section',
	];

	// Return sections
	return $sections;
}

/**
 * Add the "Feeds" section to the settings sections array.
 *
 * @since 1.0.0
 *
 * @param array $subsections
 *
 * @return array
 */
function add_subsection( $subsections = [] ) {

	// Add "Payments" subsection
	$subsections['payments']['payments'] = [
		'id'   => 'payments',
		'name' => esc_html__( 'Payments', 'sugar-calendar' ),
		'url'  => get_section_url( [ 'subsection' => 'main' ] ),
		'func' => __NAMESPACE__ . '\\payments_section',
	];

	// Add "Emails" subsection
	$subsections['tickets']['emails'] = [
		'id'   => 'emails',
		'name' => esc_html__( 'Emails', 'sugar-calendar' ),
		'url'  => get_section_url( [ 'subsection' => 'main' ] ),
		'func' => __NAMESPACE__ . '\\emails_section',
	];

	// Return sections
	return $subsections;
}

/**
 * Render the payments section
 *
 * @since 1.0.0
 */
function payments_section() {

	// Prefetch useful values
	$pages        = get_pages();
	$is_sandbox   = Functions\is_sandbox();
	$is_constant  = defined( 'SC_GATEWAY_SANDBOX_MODE' ) && SC_GATEWAY_SANDBOX_MODE;
	$receipt_page = Settings\get_setting( 'receipt_page' );

	?>

	<?php

	UI::heading(
		[
			'title' => esc_html__( 'Currency', 'sugar-calendar' ),
		]
	);

	// Currency.
	$currencies       = Functions\get_currencies();
	$current_currency = Functions\get_currency();

	UI::select_input(
		[
			'id'          => 'sc_et_currency',
			'name'        => 'currency',
			'options'     => $currencies,
			'value'       => $current_currency,
			'label'       => esc_html__( 'Currency', 'sugar-calendar' ),
			'description' => esc_html__( 'Choose your currency. Note that some payment gateways have currency restrictions.', 'sugar-calendar' ),
		]
	);

	// Currency Symbol Position.
	$position  = Settings\get_setting( 'currency_position', 'before' );
	$positions = [
		'before' => esc_html__( 'Before ($10)', 'sugar-calendar' ),
		'after'  => esc_html__( 'After (10$)', 'sugar-calendar' ),
	];

	UI::select_input(
		[
			'id'          => 'sc_et_currency_position',
			'name'        => 'currency_position',
			'options'     => $positions,
			'value'       => $position,
			'label'       => esc_html__( 'Currency Symbol Position', 'sugar-calendar' ),
			'description' => esc_html__( 'Choose the location of the currency symbol.', 'sugar-calendar' ),
		]
	);

	// Thousands Separator.
	$thousands             = Settings\get_setting( 'thousands_separator', ',' );
	$thousands_description = sprintf( /* translators: %1$s - field description; %2$s - or.*/
		'%1$s <code>,</code> %2$s <code>.</code>.',
		esc_html__( 'The symbol to separate thousandths. Usually', 'sugar-calendar' ),
		esc_html__( 'or', 'sugar-calendar' ),
	);

	UI::text_input(
		[
			'id'          => 'sc_et_thousands_separator',
			'name'        => 'thousands_separator',
			'value'       => $thousands,
			'label'       => esc_html__( 'Thousands Separator', 'sugar-calendar' ),
			'description' => $thousands_description,
		]
	);

	// Decimal Separator.
	$decimal             = Settings\get_setting( 'decimal_separator', '.' );
	$decimal_description = sprintf( /* translators: %1$s - field description; %2$s - or.*/
		'%1$s <code>,</code> %2$s <code>.</code>.',
		esc_html__( 'The symbol to separate decimal points. Usually', 'sugar-calendar' ),
		esc_html__( 'or', 'sugar-calendar' ),
	);

	UI::text_input(
		[
			'id'          => 'sc_et_decimal_separator',
			'name'        => 'decimal_separator',
			'value'       => $decimal,
			'label'       => esc_html__( 'Decimal Separator', 'sugar-calendar' ),
			'description' => $decimal_description,
		]
	);

	UI::heading(
		[
			'title'       => esc_html__( 'Stripe', 'sugar-calendar' ),
			'description' => sprintf( /* translators: %1$s - Stripe connect description; %2$s - Stripe documentation URL; %3$s - Stripe documentation link. */
				'%1$s <a href="%2$s" target="_blank">%3$s</a>.',
				esc_html__( 'Easily collect credit card payments with Stripe. For getting started and more information, see our', 'sugar-calendar' ),
				esc_url(
					Helpers::get_utm_url(
						'https://sugarcalendar.com/docs/event-ticketing-addon/#connecting-stripe',
						[
							'content' => 'Stripe documentation',
							'medium'  => 'settings-payments',
						]
					)
				),
				esc_html__( 'Stripe documentation', 'sugar-calendar' )
			),
		]
	);

	// Test mode
	$test_mode_description = sprintf( /* translators: %1$s - test mode help; %2$s - link to Stripe dashboard; %3$s - link text. */
		'%1$s <a href="%2$s" target="_blank"> %3$s</a>.',
		esc_html__( 'While in test mode no live payments are processed. Be sure to enable Test Mode in your', 'sugar-calendar' ),
		esc_url( 'https://dashboard.stripe.com/' ),
		esc_html__( 'Stripe Dashboard', 'sugar-calendar' )
	);

	if ( $is_constant ) {
		$test_mode_description .= sprintf( /* translators: %1$s - test mode constant note; %2$s - constant. */
			'<br/>%1$s <code>SC_GATEWAY_SANDBOX_MODE</code> %2$s.',
			esc_html__( 'Note: Test Mode is currently enabled via the', 'sugar-calendar' ),
			esc_html__( 'constant', 'sugar-calendar' ),
		);
	}

	UI::toggle_control(
		[
			'id'          => 'sandbox',
			'name'        => 'sandbox',
			'value'       => $is_sandbox,
			'disabled'    => $is_constant,
			'label'       => esc_html__( 'Test Mode', 'sugar-calendar' ),
			'description' => $test_mode_description,
		]
	);

	// Stripe integration.
	UI::field_wrapper(
		[
			'label' => esc_html__( 'Connection Status', 'sugar-calendar' ),
			'id'    => 'stripe-connect',
		],
		display_stripe_connect_field( $is_sandbox )
	);

	$receipt_page_description = sprintf( /* translators: %1$s - field description; %2$s - configuration hints; %3$s - shortcode. */
		'%1$s.<br/>%2$s <code>[sc_event_tickets_receipt]</code> %3$s.',
		esc_html__( 'The page customers are sent to after completing their ticket purchase', 'sugar-calendar' ),
		esc_html__( 'This page must contain the', 'sugar-calendar' ),
		esc_html__( 'shortcode', 'sugar-calendar' ),

	);

	$receipt_pages = wp_list_pluck( $pages, 'post_title', 'ID' );

	if ( empty( $pages ) ) {
		$receipt_pages = [
			esc_html__( 'No pages found', 'sugar-calendar' ),
		];
	}

	UI::select_input(
		[
			'id'          => 'receipt_page',
			'name'        => 'receipt_page',
			'options'     => $receipt_pages,
			'value'       => $receipt_page,
			'label'       => esc_html__( 'Payment Success Page', 'sugar-calendar' ),
			'description' => $receipt_page_description,
		]
	);

	do_action( 'sc_et_settings_general_section_bottom' );
}

function display_stripe_connect_field( $is_sandbox ) {

	$stripe_connect_url = add_query_arg( [
		'live_mode'         => urlencode( (int) ! $is_sandbox ),
		'state'             => urlencode( str_pad( wp_rand( wp_rand(), PHP_INT_MAX ), 100, wp_rand(), STR_PAD_BOTH ) ),
		'customer_site_url' => urlencode( admin_url( 'admin.php?page=sc-settings&section=payments' ) ),
	], 'https://sugarcalendar.com/?sc_gateway_connect_init=stripe_connect' );

	$stripe_disconnect_url = add_query_arg( [
		'sc-stripe-disconnect' => true,
		'page'                 => 'sc-settings',
		'section'              => 'tickets',
		'subsection'           => 'payments',
		'_wpnonce'             => wp_create_nonce( 'sc-stripe-connect-disconnect' ),
	], admin_url( 'admin.php' ) );

	$stripe_connect_account_id = get_option( 'sc_stripe_connect_account_id' );

	$test_text = _x( 'test', 'current value for sandbox mode', 'sugar-calendar' );
	$live_text = _x( 'live', 'current value for sandbox mode', 'sugar-calendar' );

	if ( true === $is_sandbox ) {
		$current_mode = $test_text;
	} else {
		$current_mode = $live_text;
	}

	ob_start();

	if ( empty( $stripe_connect_account_id ) || ! Functions\get_stripe_publishable_key() || ! Functions\get_stripe_secret_key() ):
		?>

        <a href="<?php echo esc_url_raw( $stripe_connect_url ); ?>" class="sugar-calendar-stripe-connect">
            <span><?php esc_html_e( 'Connect with', 'sugar-calendar' ); ?></span>
        </a>

        <p class="desc">
			<?php echo wp_kses(
				sprintf( /* translators: %1$s - test mode help; %2$s - link to Stripe dashboard; %3$s - link text; %4$s - test mode enable heads up. */
					'%1$s <a href="%2$s" target="_blank"> %3$s</a> %4$s.',
					esc_html__( 'Securely connect to Stripe with just a few clicks to begin accepting payments!', 'sugar-calendar' ),
					esc_url( 'https://dashboard.stripe.com/' ),
					esc_html__( 'Learn more', 'sugar-calendar' ),
					esc_html__( 'about connecting with Stripe', 'sugar-calendar' )
				),
				[
					'a' => [ 'href' => true, 'target' => true ],
				]
			);
			?>
        </p>

	<?php else: ?>

        <p class="description">
			<?php printf( esc_html__( 'Your Stripe account is connected in %s mode.', 'sugar-calendar' ), '<strong>' . $current_mode . '</strong>' ); ?>
            <a href="<?php echo esc_url_raw( $stripe_connect_url ); ?>" class="button button-primary"><?php esc_html_e( 'Reconnect', 'sugar-calendar' ); ?></a>
            <a href="<?php echo esc_url_raw( $stripe_disconnect_url ); ?>" class="button"><?php esc_html_e( 'Disconnect', 'sugar-calendar' ); ?></a>
        </p>

	<?php
	endif;

	return ob_get_clean();
}

/**
 * Render the emails section
 *
 * @since 1.0.0
 */
function emails_section() {

	// Settings.
	UI::heading(
		[
			'title' => esc_html__( 'Ticket Details', 'sugar-calendar' ),
		]
	);

	// Ticket Details Page.
	$pages                   = get_pages();
	$ticket_pages            = [ esc_html__( 'No pages found', 'sugar-calendar' ) ];
	$ticket_page             = 0;
	$ticket_page_description = sprintf( /* translators: %1$s - field description; %2$s - field directions; %3$s shortcode.*/
		'%1$s.<br>%2$s<code>[sc_event_tickets_details]</code> %3$s.',
		esc_html__( 'The page where customer will view the details of their ticket purchases', 'sugar-calendar' ),
		esc_html__( 'This page must contain the', 'sugar-calendar' ),
		esc_html__( 'shortcode', 'sugar-calendar' ),
	);

	if ( ! empty( $pages ) ) {
		$ticket_pages = wp_list_pluck( $pages, 'post_title', 'ID' );
		$ticket_page  = Settings\get_setting( 'ticket_page' );
	}

	UI::select_input(
		[
			'id'          => 'sc_et_ticket_page',
			'name'        => 'ticket_page',
			'options'     => $ticket_pages,
			'value'       => $ticket_page,
			'label'       => esc_html__( 'Ticket Details Page', 'sugar-calendar' ),
			'description' => $ticket_page_description,
		]
	);

	UI::heading(
		[
			'title' => esc_html__( 'Email Sender', 'sugar-calendar' ),
		]
	);

	// From Email Address.
	$from_email = Settings\get_setting( 'receipt_from_email' );

	UI::text_input(
		[
			'id'          => 'sc_et_email_from',
			'name'        => 'receipt_from_email',
			'value'       => $from_email,
			'placeholder' => get_bloginfo( 'admin_email' ),
			'label'       => esc_html__( 'From Email Address', 'sugar-calendar' ),
			'description' => esc_html__( 'The email address notifications are sent from.', 'sugar-calendar' ),
		]
	);

	// From Email Name.
	$from_name = Settings\get_setting( 'receipt_from_name' );

	UI::text_input(
		[
			'id'          => 'sc_et_receipt_email_name',
			'name'        => 'receipt_from_name',
			'value'       => $from_name,
			'placeholder' => get_bloginfo( 'name' ),
			'label'       => esc_html__( 'From Email Name', 'sugar-calendar' ),
			'description' => esc_html__( 'The person/business name email notifications are sent from.', 'sugar-calendar' ),
		]
	);

	UI::heading(
		[
			'title' => esc_html__( 'Order Receipt Email', 'sugar-calendar' ),
		]
	);

	// Order Receipt Subject.
	$subject = Settings\get_setting( 'receipt_subject' );

	UI::text_input(
		[
			'id'          => 'sc_et_receipt_email_subject',
			'name'        => 'receipt_subject',
			'value'       => $subject,
			'placeholder' => esc_html__( 'Ticket Purchase Receipt', 'sugar-calendar' ),
			'label'       => esc_html__( 'Order Receipt Subject', 'sugar-calendar' ),
			'description' => esc_html__( 'The subject line of emailed order receipts.', 'sugar-calendar' ),
		]
	);

	// Order Receipt Message.
	UI::field_wrapper(
		[
			'label' => esc_html__( 'Order Receipt Message', 'sugar-calendar' ),
			'id'    => 'receipt-message',
		],
		display_receipt_message_editor()
	);

	// Ticket Receipt Email.
	UI::heading(
		[
			'title' => esc_html__( 'Ticket Receipt Email', 'sugar-calendar' ),
		]
	);

	// Ticket Email Subject.
	$t_subject = Settings\get_setting( 'ticket_subject' );

	UI::text_input(
		[
			'id'          => 'sc_et_ticket_email_subject',
			'name'        => 'ticket_subject',
			'value'       => $t_subject,
			'placeholder' => esc_html__( 'Ticket Email Subject', 'sugar-calendar' ),
			'label'       => esc_html__( 'Ticket Email Subject', 'sugar-calendar' ),
			'description' => esc_html__( 'The subject line used when emailing a ticket to an attendee.', 'sugar-calendar' ),
		]
	);

	// Ticket Email Message.
	UI::field_wrapper(
		[
			'label' => esc_html__( 'Ticket Email Message', 'sugar-calendar' ),
			'id'    => 'ticket-message',
		],
		display_ticket_email_message_editor(),
	);

	do_action( 'sc_et_settings_emails_section_bottom' );
}

function display_receipt_message_editor() {

	$message = Settings\get_setting( 'receipt_message' );

	ob_start();

	wp_editor( stripslashes( $message ), 'sc_et_settings_receipt_message', [ 'textarea_name' => 'sugar-calendar[receipt_message]' ] );
	?>

    <p class="desc">
		<?php esc_html_e( 'The full message included in the emailed order receipts. The following dynamic placeholders can be used:', 'sugar-calendar' ); ?>
    </p>
    <dl class="sc-et-email-tags-list">

		<?php
		echo Functions\get_emails_tags_list( 'order' );
		echo Functions\get_emails_tags_list( 'event' );
		?>

    </dl>

	<?php
	return ob_get_clean();
}

function display_ticket_email_message_editor() {

	$t_message = Settings\get_setting( 'ticket_message' );

	ob_start();

	wp_editor( stripslashes( $t_message ), 'sc_et_settings_ticket_message', [ 'textarea_name' => 'sugar-calendar[ticket_message]' ] );
	?>

    <p class="description">
		<?php esc_html_e( 'The message sent when emailing a ticket to an attendee. The following dynamic placeholders can be used:', 'sugar-calendar' ); ?>
    </p>
    <dl class="sc-et-email-tags-list">

		<?php
		echo Functions\get_emails_tags_list( 'ticket' );
		echo Functions\get_emails_tags_list( 'event' );
		echo Functions\get_emails_tags_list( 'attendee' );
		?>

    </dl>

	<?php
	return ob_get_clean();
}

/**
 * Listens for Stripe Connect completion requests and saves the Stripe API keys.
 *
 * @since 1.0
 */
function process_stripe_connect_completion() {

	// Do not need to handle this request, bail.
	if (
		! isset( $_GET['state'] )
		|| ! isset( $_GET['sc_gateway_connect_completion'] )
		|| ( 'stripe_connect' !== $_GET['sc_gateway_connect_completion'] )
	) {
		return;
	}

	// Headers already sent, bail.
	if ( headers_sent() ) {
		return;
	}

	// Current user cannot handle this request, bail.
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$is_sandbox = Functions\is_sandbox();

	$sc_credentials_url = add_query_arg( [
		'live_mode'         => urlencode( (int) ! $is_sandbox ),
		'state'             => urlencode( sanitize_text_field( $_GET['state'] ) ),
		'customer_site_url' => urlencode( admin_url( 'admin.php?page=sc-settings&section=payments' ) ),
	], 'https://sugarcalendar.com/?sc_gateway_connect_credentials=stripe_connect' );

	$response = wp_remote_get( esc_url_raw( $sc_credentials_url ) );

	if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) || ! wp_remote_retrieve_body( $response ) ) {
		$message = '<p>' . sprintf( __( 'There was an error getting your Stripe credentials. Please <a href="%s">try again</a>. If you continue to have this problem, please contact support.', 'sugar-calendar' ), esc_url( admin_url( 'admin.php?page=sc-settings&section=payments' ) ) ) . '</p>';
		wp_die( $message );
	}

	$response = json_decode( $response['body'], true );
	$data     = $response['data'];

	if ( true === $is_sandbox ) {
		update_option( 'sc_stripe_test_publishable', sanitize_text_field( $data['publishable_key'] ), false );
		update_option( 'sc_stripe_test_secret', sanitize_text_field( $data['secret_key'] ), false );
	} else {
		update_option( 'sc_stripe_live_publishable', sanitize_text_field( $data['publishable_key'] ), false );
		update_option( 'sc_stripe_live_secret', sanitize_text_field( $data['secret_key'] ), false );
	}

	update_option( 'sc_stripe_connect_account_id', sanitize_text_field( $data['stripe_user_id'] ), false );

	$redirect = add_query_arg(
		[
			'page'    => 'sc-settings',
			'section' => 'payments',
		],
		admin_url( 'admin.php' )
	);

	// Redirect
	wp_safe_redirect( $redirect );
	exit;
}

/**
 * Listen for a disconnect URL.
 *
 * Clears out the `sc_stripe_connect_account_id` and the API keys.
 *
 * @since 1.0
 */
function process_stripe_disconnect() {

	// Do not need to handle this request, bail.
	if (
		! ( isset( $_GET['page'] ) && 'sc-settings' === $_GET['page'] ) ||
		! isset( $_GET['sc-stripe-disconnect'] )
	) {
		return;
	}

	// No nonce, bail.
	if ( ! isset( $_GET['_wpnonce'] ) ) {
		return;
	}

	// Current user cannot handle this request, bail.
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// Invalid nonce, bail.
	if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'sc-stripe-connect-disconnect' ) ) {
		return;
	}

	if ( Functions\is_sandbox() ) {
		update_option( 'sc_stripe_test_secret', false );
		update_option( 'sc_stripe_test_publishable', false );
	} else {
		update_option( 'sc_stripe_live_secret', false );
		update_option( 'sc_stripe_live_publishable', false );
	}

	update_option( 'sc_stripe_connect_account_id', false );

	$redirect = add_query_arg(
		[
			'page'    => 'sc-settings',
			'section' => 'payments',
		],
		admin_url( 'admin.php' )
	);

	// Redirect
	wp_safe_redirect( $redirect );
	exit;
}

/**
 * Filter post states, and add for ticketing specific page settings.
 *
 * @since 1.1.4
 *
 * @param array $states
 * @param mixed $post
 *
 * @return array
 */
function add_page_states( $states = [], $post = false ) {

	// Bail if not looking at pages
	if ( empty( $post ) || ( 'page' !== get_post_type( $post ) ) ) {
		return $states;
	}

	// Get options
	$receipt_page = (int) Settings\get_setting( 'receipt_page' );
	$ticket_page  = (int) Settings\get_setting( 'ticket_page' );

	// Receipt Page
	if ( $post->ID === $receipt_page ) {
		$states['sc_et_receipt'] = esc_html__( 'Ticket Receipt Page', 'sugar-calendar' );
	}

	// Ticket Page
	if ( $post->ID === $ticket_page ) {
		$states['sc_et_ticket'] = esc_html__( 'Ticket Details Page', 'sugar-calendar' );
	}

	// Return states
	return $states;
}

/**
 * Get setting names.
 *
 * @since 3.3.0
 *
 * @return array
 */
function get_setting_names() {
	return [
		'sandbox',
		'receipt_page',
		'currency',
		'currency_position',
		'thousands_separator',
		'decimal_separator',
		'ticket_page',
		'receipt_from_email',
		'receipt_from_name',
		'receipt_subject',
		'receipt_message',
		'ticket_subject',
		'ticket_message',
	];
}

/**
 * Save the settings.
 *
 * @since 2.2.4
 * @since 3.1.0 Loop through the data instead of the settings.
 * @since 3.3.0 Decouple setting field names.
 *
 * @param array $post_data Array containing the data to be saved.
 */
function handle_post( $post_data ) {

	// Work only in payments or tickets section.
	if (
		! isset( $_GET['section'] )
		||
		! in_array( $_GET['section'], [ 'payments', 'tickets' ], true )
	) {
		return;
	}

	$settings = get_setting_names();

	$options = get_option( 'sc_et_settings', [] );

	foreach ( $post_data as $key => $value ) {

		if ( ! in_array( $key, $settings, true ) ) {
			continue;
		}

		$options[ $key ] = sanitize_textarea_field( $value );
	}

	if ( ! empty( $post_data['currency'] ) ) {
		$options['sandbox'] = isset( $post_data['sandbox'] );
	}

	update_option( 'sc_et_settings', $options );

	WP::add_admin_notice( esc_html__( 'Settings saved.', 'sugar-calendar' ), WP::ADMIN_NOTICE_SUCCESS );
}

/**
 * Handle AJAX requests for saving settings.
 *
 * @since 3.3.0
 *
 * @return void
 */
function handle_post_ajax() {

	// Bail if not an AJAX request.
	if ( ! wp_doing_ajax() ) {
		return;
	}

	// Verify the nonce.
	if ( ! check_ajax_referer( Area::SLUG, 'nonce', false ) ) {
		wp_send_json_error(
			[
				'success' => false,
				'message' => esc_html__( 'Nonce verification failed.', 'sugar-calendar' ),
			]
		);
	}

	// Exit if no options are set.
	if (
		! isset( $_POST['options'] )
		||
		! is_array( $_POST['options'] )
		||
		empty( $_POST['options'] )
	) {
		wp_send_json_error(
			[
				'success' => false,
				'message' => esc_html__( 'No options were set.', 'sugar-calendar' ),
			]
		);
	}

	// Posted data.
	$posted_data = $_POST['options'];

	// Setting names.
	$setting_names = get_setting_names();

	// Current options.
	$options = get_option( 'sc_et_settings', [] );

	// Loop through the data and sanitize.
	foreach ( $posted_data as $key => $value ) {

		// Skip if not a setting.
		if ( ! in_array( $key, $setting_names, true ) ) {
			continue;
		}

		// Switch on the key.
		switch ( $key ) {

			case 'sandbox':
				$options[ $key ] = filter_var( $value, FILTER_VALIDATE_BOOLEAN );
				break;

			default:
				$options[ $key ] = sanitize_textarea_field( $value );
				break;
		}
	}

	// Save the settings.
	$is_settings_saved = update_option( 'sc_et_settings', $options );

	$response = [
		'success' => $is_settings_saved,
		'message' =>
			$is_settings_saved
			?
			esc_html__( 'Settings saved.', 'sugar-calendar' )
			:
			esc_html__( 'Settings not saved.', 'sugar-calendar' ),
	];

	// Return the response.
	wp_send_json( $response );
}
