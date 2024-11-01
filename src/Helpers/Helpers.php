<?php

namespace Sugar_Calendar\Helpers;

use Sugar_Calendar\Plugin;

/**
 * Class with all the misc helper functions that don't belong elsewhere.
 *
 * @since 3.0.0
 */
class Helpers {

	/**
	 * Get the default user agent.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public static function get_default_user_agent() {

		$license_type = Plugin::instance()->get_license_type();

		return 'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' ) . '; SugarCalendar/' . $license_type . '-' . SC_PLUGIN_VERSION;
	}

	/**
	 * Get UTM URL.
	 *
	 * @since 3.0.0
	 *
	 * @param string       $url Base url.
	 * @param array|string $utm Array of UTM params, or if string provided - utm_content URL parameter.
	 *
	 * @return string
	 */
	public static function get_utm_url( $url, $utm ) { // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh

		// Defaults.
		$source   = 'WordPress';
		$medium   = 'plugin-settings';
		$campaign = Plugin::instance()->is_pro() ? 'plugin' : 'liteplugin';
		$content  = 'general';
		$locale   = get_user_locale();

		if ( is_array( $utm ) ) {
			if ( isset( $utm['source'] ) ) {
				$source = $utm['source'];
			}
			if ( isset( $utm['medium'] ) ) {
				$medium = $utm['medium'];
			}
			if ( isset( $utm['campaign'] ) ) {
				$campaign = $utm['campaign'];
			}
			if ( isset( $utm['content'] ) ) {
				$content = $utm['content'];
			}
			if ( isset( $utm['locale'] ) ) {
				$locale = $utm['locale'];
			}
		} elseif ( is_string( $utm ) ) {
			$content = $utm;
		}

		$query_args = [
			'utm_source'   => esc_attr( rawurlencode( $source ) ),
			'utm_medium'   => esc_attr( rawurlencode( $medium ) ),
			'utm_campaign' => esc_attr( rawurlencode( $campaign ) ),
			'utm_locale'   => esc_attr( sanitize_key( $locale ) ),
		];

		if ( ! empty( $content ) ) {
			$query_args['utm_content'] = esc_attr( rawurlencode( $content ) );
		}

		return add_query_arg( $query_args, $url );
	}

	/**
	 * Upgrade link used within the various admin pages.
	 *
	 * @since 3.0.0
	 *
	 * @param array|string $utm Array of UTM params, or if string provided - utm_content URL parameter.
	 *
	 * @return string
	 */
	public static function get_upgrade_link( $utm ) {

		$url = self::get_utm_url( 'https://sugarcalendar.com/lite-upgrade/', $utm );

		/**
		 * Filters upgrade link.
		 *
		 * @since 3.0.0
		 *
		 * @param string $url Upgrade link.
		 */
		return apply_filters( 'sugar_calendar_get_upgrade_link', $url ); // phpcs:ignore WPForms.PHP.ValidateHooks.InvalidHookName
	}

	/**
	 * Import Plugin_Upgrader class from core.
	 *
	 * @since 3.0.0
	 */
	public static function include_plugin_upgrader() {

		/** \WP_Upgrader class */
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

		/** \Plugin_Upgrader class */
		require_once ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php';
	}

	/**
	 * Whether the current request is a WP CLI request.
	 *
	 * @since 3.0.0
	 */
	public static function is_wp_cli() {

		return defined( 'WP_CLI' ) && WP_CLI;
	}
}
