<?php

namespace Sugar_Calendar\Block\Calendar\CalendarView\Month;

use DateInterval;
use DatePeriod;
use DateTimeImmutable;
use Exception;
use Sugar_Calendar\Block\Calendar\CalendarView\Block;
use Sugar_Calendar\Block\Common\InterfaceBaseView;
use Sugar_Calendar\Block\Common\InterfaceView;
use Sugar_Calendar\Block\Common\Template;
use Sugar_Calendar\Helper;

/**
 * Class Month.
 *
 * Base class of the "Month" view.
 *
 * @since 3.0.0
 */
class Month implements InterfaceBaseView, InterfaceView {

	/**
	 * Block object.
	 *
	 * @since 3.0.0
	 *
	 * @var Block
	 */
	private $block;

	/**
	 * Array containing the date as the key
	 * and the events as the value.
	 *
	 * @since 3.0.0
	 *
	 * @var array
	 */
	private $calendar_data;

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 *
	 * @param Block $block Block object.
	 */
	public function __construct( $block ) {

		$this->block = $block;
	}

	/**
	 * Get the data for the calendar.
	 *
	 * @since 3.0.0
	 * @since 3.1.2 Added support for visitor timezone conversion.
	 *
	 * @return array
	 *
	 * @throws Exception When the date for the calendar was not created.
	 */
	public function get_calendar_data() {

		if ( ! empty( $this->calendar_data ) ) {
			return $this->calendar_data;
		}

		// First let's check if we have offset from the previous month.
		$previous_month_offset = sc_get_calendar_day_offset( $this->block->get_timestamp() );

		if ( ! empty( $previous_month_offset ) ) {
			// Let's get the offset day.
			$offset_day = strtotime(
				sprintf( '-%d days', absint( $previous_month_offset ) ),
				$this->block->get_timestamp()
			);

			$start_period = new DateTimeImmutable( gmdate( 'Y-m-d', $offset_day ) );
		} else {
			$start_period = new DateTimeImmutable( gmdate( 'Y-m-d', $this->block->get_timestamp() ) );
		}

		// Get last day of the current cal month.
		$last_day_time = strtotime( 'last day of this month', $this->block->get_timestamp() );
		$last_day_date = gmdate( 'Y-m-d', $last_day_time );
		// Then lets get the last day of the week relative to the current month's last day.
		$last_day_week = get_weekstartend( $last_day_date, sc_get_week_start_day() );

		// Let's get the end period.
		$end_period = new DateTimeImmutable( gmdate( 'Y-m-d 23:59:59', $last_day_week['end'] ) );

		// Let's create the calendar period.
		$calendar_period = new DatePeriod(
			$start_period,
			new DateInterval( 'P1D' ),
			$end_period
		);

		if ( $this->get_block()->should_not_load_events() ) {
			$calendar_events = [];
		} else {
			$start_period_range = $start_period;
			$end_period_range   = $end_period;

			if ( $this->get_block()->get_visitor_timezone() ) {
				$start_period_range = $start_period_range->modify( '-1 day' );
				$end_period_range   = $end_period_range->modify( '+1 day' );
			}

			// Get all the events on the calendar period.
			$calendar_events = sc_get_events_for_calendar_with_custom_range(
				$start_period_range,
				$end_period_range,
				! empty( $this->block->get_calendars() ) ? array_map( 'absint', $this->block->get_calendars() ) : [],
				$this->block->get_search_term()
			);
		}

		// Let's build the calendar.
		foreach ( $calendar_period as $d ) {
			$this->calendar_data[ $d->format( 'Y-m-d' ) ] = Helper::filter_events_by_day(
				$calendar_events,
				$d->format( 'd' ),
				$d->format( 'm' ),
				$d->format( 'Y' ),
				$this->get_block()->get_visitor_timezone()
			);
		}

		return $this->calendar_data;
	}

	/**
	 * Render the calendar view.
	 *
	 * This method is different with `self::base_render()` method because
	 * this method is also used via AJAX.
	 *
	 * @since 3.0.0
	 *
	 * @throws Exception When the date for the calendar was not created.
	 */
	public function render() {

		$cal_info = [
			'month'       => $this->block->get_month_num_without_zero(),
			'year'        => $this->block->get_year(),
			'from_ajax'   => $this->block->is_ajax(),
			'accentColor' => ! empty( $this->block->get_attributes()['accentColor'] ) ? $this->block->get_attributes()['accentColor'] : '',
		];

		foreach ( array_chunk( $this->get_calendar_data(), 7, true ) as $cal_data ) {

			$calendar_week = new Week( $cal_data, $cal_info );

			$calendar_week->render();
		}
	}

	/**
	 * Render the base view.
	 *
	 * @since 3.0.0
	 */
	public function render_base() {

		Template::load( 'month.base', $this );
	}

	/**
	 * Get the month string.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_month_string() {

		global $wp_locale;

		return $wp_locale->get_month( $this->block->get_month_num_without_zero() );
	}

	/**
	 * Get the heading of the month view.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function get_heading() {

		return $this->get_month_string();
	}

	/**
	 * Get the block object.
	 *
	 * @since 3.0.0
	 *
	 * @return Block
	 */
	public function get_block() {

		return $this->block;
	}
}
