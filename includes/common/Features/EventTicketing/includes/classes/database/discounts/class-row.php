<?php
/**
 * Tickets Row Class.
 *
 * @package     Sugar Calendar
 * @subpackage  Database\Schemas
 * @since       1.0
 */
namespace Sugar_Calendar\AddOn\Ticketing\Database;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use Sugar_Calendar\Database\Row;

/**
 * Discount Class
 *
 * @since 1.0.0
 */
final class Discount extends Row {

	/**
	 * Discount ID.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var int
	 */
	public $id;

}