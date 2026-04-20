<?php
/**
 * PHPUnit bootstrap (minimal WordPress stubs for pure helpers).
 *
 * @package BeerJournal
 */

define( 'ABSPATH', dirname( __DIR__ ) . '/' );

if ( ! function_exists( '__' ) ) {
	/**
	 * @param string $text Text.
	 * @param string $domain Domain.
	 * @return string
	 */
	function __( $text, $domain = 'default' ) { // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralTextDomain
		return $text;
	}
}

if ( ! function_exists( 'apply_filters' ) ) {
	/**
	 * @param string $hook Hook.
	 * @param mixed  $value Value.
	 * @return mixed
	 */
	function apply_filters( $hook, $value ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
		return $value;
	}
}

require_once dirname( __DIR__ ) . '/includes/functions.php';
