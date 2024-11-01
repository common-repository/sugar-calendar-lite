<?php

namespace Sugar_Calendar\Block\Common;

use DateTimeImmutable;
use DateInterval;
use DatePeriod;
use Sugar_Calendar\Helper;
use Sugar_Calendar\Helpers;

/**
 * Abstract Block Class.
 *
 * @since 3.0.0
 * @since 3.1.0 Convert to an abstract class.
 */
abstract class AbstractBlock {

	/**
	 * Block Attributes.
	 *
	 * @since 3.0.0
	 *
	 * @var array
	 */
	protected $attributes;

	/**
	 * Block ID.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	private $block_id;

	/**
	 * Calendar Month.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	protected $cal_month;

	/**
	 * Calendar Year.
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	protected $cal_year;

	/**
	 * Calendar Day (Current).
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	protected $cal_day;

	/**
	 * Timestamp.
	 *
	 * @since 3.0.0
	 *
	 * @var false|int
	 */
	protected $timestamp;

	/**
	 * DateTime object in context.
	 *
	 * @since 3.0.0
	 *
	 * @var DateTimeImmutable
	 */
	protected $datetime;

	/**
	 * The visitor's timezone.
	 *
	 * @since 3.1.2
	 *
	 * @var \DateTimeZone|false
	 */
	private $visitor_timezone = null;

	/**
	 * Calendar view to render.
	 *
	 * @since 3.0.0
	 *
	 * @var InterfaceBaseView
	 */
	private $view;

	/**
	 * Whether the request is AJAX.
	 *
	 * @since 3.0.0
	 *
	 * @var bool
	 */
	private $is_ajax = null;

	/**
	 * The week period.
	 *
	 * @since 3.0.0
	 *
	 * @var DatePeriod
	 */
	private $week_period;

	/**
	 * Whether the block has events for the week.
	 *
	 * @since 3.1.0
	 *
	 * @var null|bool
	 */
	private $has_events_for_week = null;

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 *
	 * @param array $attributes Block attributes.
	 */
	public function __construct( $attributes ) {

		$this->attributes = $attributes;

		if (
			$this->is_ajax() &&
			! empty( $this->attributes['day'] ) &&
			! empty( $this->attributes['month'] ) &&
			! empty( $this->attributes['year'] )
		) {

			// We always consider the 1st day in monthly display.
			$day = $this->get_display_mode() === 'month' ? 1 : $this->attributes['day'];

			// If we're on AJAX, we used the passed date info to get the `$timestamp`.
			$timestamp = gmmktime( 0, 0, 0, $this->attributes['month'], $day, $this->attributes['year'] );
		}

		// In case this is the initial request or we failed to get the timestamp for the ajax.
		if ( empty( $timestamp ) ) {

			$timestamp = sugar_calendar_get_request_time();

			if ( $this->get_display_mode() === 'month' ) {
				$this->cal_month = gmdate( 'n', $timestamp );
				$this->cal_year  = gmdate( 'Y', $timestamp );
				$this->cal_day   = gmdate( 'j', $timestamp );
				$timestamp       = gmmktime( 0, 0, 0, $this->cal_month, 1, $this->cal_year );
			}
		}

		if (
			empty( $this->cal_month ) ||
			empty( $this->cal_year ) ||
			empty( $this->cal_day )
		) {
			$this->cal_month = gmdate( 'n', $timestamp );
			$this->cal_year  = gmdate( 'Y', $timestamp );
			$this->cal_day   = gmdate( 'j', $timestamp );
		}

		$this->timestamp = $timestamp;
		$this->datetime  = new DateTimeImmutable( gmdate( 'Y-m-d', $this->timestamp ) );

		$this->attempt_update_datetime_info();
	}

	/**
	 * Return the block HTML.
	 *
	 * @since 3.0.0
	 *
	 * @return false|string
	 */
	public function get_html() {

		ob_start();

		Template::load( 'base', $this );

		return ob_get_clean();
	}

	/**
	 * Short version of the Block ID.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_block_id() {

		if ( ! empty( $this->block_id ) ) {
			return $this->block_id;
		}

		$this->block_id = '';

		if ( ! empty( $this->attributes['calendarId'] ) ) {
			$this->block_id = substr( $this->attributes['calendarId'], 0, 8 );
		} elseif ( ! empty( $this->attributes['blockId'] ) ) {
			$this->block_id = substr( $this->attributes['blockId'], 0, 8 );
		}

		return $this->block_id;
	}

	/**
	 * Get the default accent color.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_default_accent_color() {

		return $this->attributes['accentColor'];
	}

	/**
	 * Array containing the calendar IDs.
	 *
	 * @since 3.0.0
	 * @since 3.2.0 Support filtering by Block Settings.
	 *
	 * @return array
	 */
	public function get_calendars() {

		$attributes = $this->get_attributes();
		$calendars  = ! empty( $attributes['calendars'] ) ? $attributes['calendars'] : [];

		if ( empty( $calendars ) && ! empty( $attributes['calendarsFilter'] ) ) {
			return $attributes['calendarsFilter'];
		}

		return $calendars;
	}

	/**
	 * Set the view.
	 *
	 * @since 3.0.0
	 *
	 * @param InterfaceBaseView $view View.
	 */
	public function set_view( InterfaceBaseView $view ) {

		$this->view = $view;
	}

	/**
	 * Get the view.
	 *
	 * @since 3.0.0
	 *
	 * @return InterfaceBaseView
	 */
	public function get_view() {

		return $this->view;
	}

	/**
	 * Get the block attributes.
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public function get_attributes() {

		return $this->attributes;
	}

	/**
	 * Get the heading.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_heading() {

		return $this->get_view()->get_heading();
	}

	/**
	 * Get the additional heading.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_additional_heading() {

		if ( $this->get_display_mode() === 'month' ) {
			return $this->get_year();
		}

		return '';
	}

	/**
	 * Get the display mode.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_display_mode() {

		return ! empty( $this->attributes['display'] ) ? $this->attributes['display'] : '';
	}

	/**
	 * DateTime getter.
	 *
	 * @since 3.0.0
	 *
	 * @return DateTimeImmutable
	 */
	public function get_datetime() {

		return $this->datetime;
	}

	/**
	 * Set the DateTime.
	 *
	 * @since 3.0.0
	 *
	 * @param DateTimeImmutable $datetime New DateTime.
	 */
	public function set_datetime( $datetime ) {

		$this->datetime = $datetime;
	}

	/**
	 * Get the day without leading zero.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_day_num_without_zero() {

		return $this->cal_day;
	}

	/**
	 * Set the calendar day.
	 *
	 * @since 3.0.0
	 *
	 * @param string $cal_day New calendar day.
	 */
	public function set_day_num_without_zero( $cal_day ) {

		$this->cal_day = $cal_day;
	}

	/**
	 * Get the month number without the leading zero.
	 *
	 * @since 3.0.0
	 *
	 * @return int
	 */
	public function get_month_num_without_zero() {

		return absint( $this->cal_month );
	}

	/**
	 * Set the calendar month.
	 *
	 * @since 3.0.0
	 *
	 * @param string $cal_month New calendar month.
	 */
	public function set_month_num_without_zero( $cal_month ) {

		$this->cal_month = $cal_month;
	}

	/**
	 * Get the year of the calendar.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_year() {

		return $this->cal_year;
	}

	/**
	 * Set the calendar year.
	 *
	 * @since 3.0.0
	 *
	 * @param string $year New calendar year.
	 */
	public function set_year( $year ) {

		$this->cal_year = $year;
	}

	/**
	 * Get the timestamp.
	 *
	 * @since 3.0.0
	 *
	 * @return int
	 */
	public function get_timestamp() {

		return $this->timestamp;
	}

	/**
	 * Set the timestamp.
	 *
	 * @since 3.0.0
	 *
	 * @param int $timestamp New timestamp.
	 */
	public function set_timestamp( $timestamp ) {

		$this->timestamp = $timestamp;
	}

	/**
	 * Get the search term.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_search_term() {

		return ! empty( $this->attributes['search'] ) ? $this->attributes['search'] : '';
	}

	/**
	 * Whether the request is AJAX or not.
	 *
	 * @since 3.0.0
	 *
	 * @return bool
	 */
	public function is_ajax() {

		if ( ! is_null( $this->is_ajax ) ) {
			return $this->is_ajax;
		}

		$this->is_ajax = ! empty( $this->get_attributes()['from_ajax'] ) && $this->get_attributes()['from_ajax'];

		return $this->is_ajax;
	}

	/**
	 * Whether to render the display mode settings.
	 *
	 * @since 3.0.0
	 *
	 * @return bool
	 */
	public function should_render_display_mode_settings() {

		return ! empty( $this->get_attributes()['allowUserChangeDisplay'] ) && $this->get_attributes()['allowUserChangeDisplay'];
	}

	/**
	 * Get the calendars for the filter.
	 *
	 * @since 3.0.0
	 *
	 * @return \WP_Term[]
	 */
	public function get_calendars_for_filter() {

		$calendars = get_terms(
			[
				'taxonomy'   => 'sc_event_category',
				'hide_empty' => false,
			]
		);

		if ( empty( $calendars ) ) {
			return [];
		}

		// Display all calendars if no calendars are selected from block settings.
		if ( empty( $this->get_calendars() ) ) {
			return $calendars;
		}

		$selected_calendars = array_filter(
			$calendars,
			function( $calendar ) {
				return in_array( $calendar->term_id, $this->get_calendars(), true );
			}
		);

		// If only one calendar is selected, we don't need to display the filter.
		if ( count( $selected_calendars ) <= 1 ) {
			return [];
		}

		return $selected_calendars;
	}

	/**
	 * Get the classes for the block.
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	public function get_classes() {

		return [];
	}

	/**
	 * Get the events for the week.
	 *
	 * @since 3.1.0
	 * @since 3.1.2 Added support for visitor timezone conversion.
	 *
	 * @return Event[]
	 */
	public function get_week_events() {

		$events = [];

		// Let's create the calendar period.
		$calendar_period = $this->get_week_period();

		$start_period_range = $calendar_period->getStartDate();
		$end_period_range   = $calendar_period->getEndDate();

		if ( $this->get_visitor_timezone() ) {
			$start_period_range = $start_period_range->modify( '-1 day' );
			$end_period_range   = $end_period_range->modify( '+1 day' );
		}

		// Get all the events on the calendar period.
		$calendar_events = sc_get_events_for_calendar_with_custom_range(
			$start_period_range,
			$end_period_range,
			array_map( 'absint', $this->get_calendars() ),
			$this->get_search_term()
		);

		$this->has_events_for_week = ! empty( $calendar_events );

		// Let's build the calendar.
		foreach ( $calendar_period as $d ) {
			$events[ $d->format( 'Y-m-d' ) ] = Helper::filter_events_by_day(
				$calendar_events,
				$d->format( 'd' ),
				$d->format( 'm' ),
				$d->format( 'Y' ),
				$this->get_visitor_timezone()
			);
		}

		return $events;
	}

	/**
	 * Get the week period.
	 *
	 * @since 3.1.0
	 *
	 * @return DatePeriod
	 */
	public function get_week_period() {

		if ( ! empty( $this->week_period ) ) {
			return $this->week_period;
		}

		$start_period = $this->get_first_weekday();
		$end_period   = $start_period->add( new DateInterval( 'P6D' ) );

		// Let's create the calendar period.
		$this->week_period = new DatePeriod(
			$start_period,
			new DateInterval( 'P1D' ),
			$end_period->setTime( 23, 59, 59 )
		);

		return $this->week_period;
	}

	/**
	 * Get the first day of the calendar week.
	 *
	 * This method does not necessary return the first day of the month, this is so
	 * we also display the previous month's offset day in the calendar.
	 *
	 * @since 3.1.0
	 * @since 3.3.0 Fixed issue where `$start_period` isn't computed because of non-english `$weekday`.
	 *
	 * @return DateTimeImmutable
	 *
	 * @throws \Exception
	 */
	public function get_first_weekday() {

		$start_period      = $this->get_datetime();
		$sc_week_start_day = (int) sc_get_week_start_day();
		$day_date_weekday  = (int) $start_period->format( 'w' );
		$weekday           = Helpers::get_english_weekday_by_number( $sc_week_start_day );

		if ( $weekday && $sc_week_start_day !== $day_date_weekday ) {

			$start_period = $start_period->modify(
				sprintf(
					'last %s',
					$weekday
				)
			);
		}

		return $start_period;
	}

	/**
	 * Attempt to update the block object's datetime info if the
	 * action is provided.
	 *
	 * @since 3.1.0
	 */
	public function attempt_update_datetime_info() { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		if ( empty( $this->get_attributes()['action'] ) ) {
			return;
		}

		$action = $this->get_attributes()['action'];

		switch ( $action ) {
			case 'previous_day':
				$datetime = $this->get_datetime()->modify( '-1 day' );
				break;

			case 'next_day':
				$datetime = $this->get_datetime()->modify( '+1 day' );
				break;

			case 'previous_week':
				$datetime = $this->get_datetime()->modify( '-1 week' );
				break;

			case 'next_week':
				$datetime = $this->get_datetime()->modify( '+1 week' );
				break;

			case 'previous_month':
				$datetime = $this->get_datetime()->modify( '-1 month' );
				break;

			case 'next_month':
				$datetime = $this->get_datetime()->modify( '+1 month' );
				break;

			default:
				return; // phpcs:ignore WPForms.Formatting.EmptyLineBeforeReturn.AddEmptyLineBeforeReturnStatement
		}

		$this->set_day_num_without_zero( $datetime->format( 'j' ) );
		$this->set_month_num_without_zero( $datetime->format( 'n' ) );
		$this->set_year( $datetime->format( 'Y' ) );
		$this->set_timestamp( $datetime->format( 'U' ) );
		$this->set_datetime( $datetime );
	}

	/**
	 * Returns whether the block has events for the week or not.
	 *
	 * @since 3.1.0
	 *
	 * @return bool|null Returns `null` if we are not aware if there are events for the week or not.
	 *                   Otherwise it returns `bool`.
	 */
	public function has_events_in_week() {

		return $this->has_events_for_week;
	}

	/**
	 * Get the visitor's timezone.
	 *
	 * @since 3.1.2
	 *
	 * @return \DateTimeZone|false Returns `false` if visitor timezone is not enabled or visitor timezone conversion
	 *                             is not enabled. Otherwise, returns the visitor's timezone.
	 */
	public function get_visitor_timezone() {

		$timezone = false;

		if ( ! is_null( $this->visitor_timezone ) ) {
			$timezone = $this->visitor_timezone;
		} elseif (
			! empty( $this->get_attributes()['visitor_tz_convert'] ) &&
			$this->get_attributes()['visitor_tz_convert'] === 1 &&
			! empty( $this->get_attributes()['visitor_tz'] )
		) {
			$timezone = timezone_open( $this->get_attributes()['visitor_tz'] );
		}

		/**
		 * Filter the visitor's timezone.
		 *
		 * @since 3.1.2
		 *
		 * @param \DateTimeZone|false $timezone Visitor's timezone.
		 */
		return apply_filters( 'sugar_calendar_blocks_get_visitor_timezone', $timezone ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
	}

	/**
	 * Whether to load events or not.
	 *
	 * @since 3.1.2
	 *
	 * @return bool
	 */
	public function should_not_load_events() {

		if ( empty( $this->get_attributes()['should_not_load_events'] ) ) {
			return false;
		}

		return boolval( $this->get_attributes()['should_not_load_events'] );
	}

	/**
	 * Get the display options.
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	abstract public function get_display_options();

	/**
	 * Get the current pagination text.
	 *
	 * @since 3.1.0
	 *
	 * @return string
	 */
	abstract public function get_current_pagination_display();
}
