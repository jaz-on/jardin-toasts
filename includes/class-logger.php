<?php
/**
 * File-based logging for Jardin Toasts.
 *
 * @package JardinToasts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class JB_Logger
 */
class JB_Logger {

	public const LEVEL_ERROR   = 'ERROR';
	public const LEVEL_WARNING = 'WARNING';
	public const LEVEL_INFO    = 'INFO';
	public const LEVEL_DEBUG   = 'DEBUG';

	/**
	 * Log a message.
	 *
	 * @param string $level Level constant.
	 * @param string $message Message.
	 * @return void
	 */
	public static function log( $level, $message ) {
		$dir = jb_get_log_directory();
		if ( ! $dir ) {
			return;
		}
		$debug = (bool) get_option( 'jb_debug_mode', false );
		if ( self::LEVEL_DEBUG === $level && ! $debug ) {
			return;
		}
		$file  = $dir . 'jardin-toasts-' . gmdate( 'Y-m-d' ) . '.log';
		$line  = sprintf( "[%s] %s: %s\n", gmdate( 'Y-m-d H:i:s' ), $level, $message );
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		file_put_contents( $file, $line, FILE_APPEND | LOCK_EX );
	}

	/**
	 * Convenience methods.
	 *
	 * @param string $message Message.
	 * @return void
	 */
	public static function error( $message ) {
		self::log( self::LEVEL_ERROR, $message );
	}

	/**
	 * Warning log.
	 *
	 * @param string $message Message.
	 * @return void
	 */
	public static function warning( $message ) {
		self::log( self::LEVEL_WARNING, $message );
	}

	/**
	 * Info log.
	 *
	 * @param string $message Message.
	 * @return void
	 */
	public static function info( $message ) {
		self::log( self::LEVEL_INFO, $message );
	}

	/**
	 * Debug log.
	 *
	 * @param string $message Message.
	 * @return void
	 */
	public static function debug( $message ) {
		self::log( self::LEVEL_DEBUG, $message );
	}

	/**
	 * Read last N lines from today's log (for admin viewer).
	 *
	 * @param int $lines Max lines.
	 * @return string
	 */
	public static function tail_today( $lines = 200 ) {
		$dir = jb_get_log_directory();
		if ( ! $dir ) {
			return '';
		}
		$file = $dir . 'jardin-toasts-' . gmdate( 'Y-m-d' ) . '.log';
		if ( ! is_readable( $file ) ) {
			$legacy = $dir . 'jardin-beer-' . gmdate( 'Y-m-d' ) . '.log';
			if ( is_readable( $legacy ) ) {
				$file = $legacy;
			} else {
				return '';
			}
		}
		$content = file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		if ( ! is_string( $content ) || '' === $content ) {
			return '';
		}
		$all = explode( "\n", $content );
		$all = array_filter( $all );
		$slice = array_slice( $all, -1 * absint( $lines ) );
		return implode( "\n", $slice );
	}
}
