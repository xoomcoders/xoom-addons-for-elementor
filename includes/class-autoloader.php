<?php
/**
 * PSR-style autoloader for the Xoom Addons namespace.
 *
 * Maps `Xoom_Addons\Foo_Bar` to `includes/class-foo-bar.php`, with dedicated
 * roots for shippable widgets (`widgets/`) and extensions (`modules/`).
 *
 * @package Xoom_Addons
 */

namespace Xoom_Addons;

defined( 'ABSPATH' ) || exit;

/**
 * Registers and resolves plugin classes on demand.
 */
class Autoloader {

	/**
	 * Namespace prefix handled by this loader.
	 */
	const PREFIX = 'Xoom_Addons\\';

	/**
	 * Register the autoloader with SPL.
	 *
	 * @return void
	 */
	public static function register() {
		spl_autoload_register( array( __CLASS__, 'autoload' ) );
	}

	/**
	 * Resolve a fully qualified class name to a file and include it.
	 *
	 * @param string $class Fully qualified class name.
	 * @return void
	 */
	public static function autoload( $class ) {
		if ( 0 !== strpos( $class, self::PREFIX ) ) {
			return;
		}

		$relative = substr( $class, strlen( self::PREFIX ) );
		$parts    = explode( '\\', $relative );
		$class    = array_pop( $parts );

		$base = 'includes/';
		$dir  = '';

		if ( ! empty( $parts ) ) {
			$root = array_shift( $parts );

			if ( 'Widgets' === $root ) {
				$base = 'widgets/';
			} elseif ( 'Modules' === $root ) {
				$base = 'modules/';
			} else {
				$dir = self::pathify( $root ) . '/';
			}

			foreach ( $parts as $part ) {
				$dir .= self::pathify( $part ) . '/';
			}
		}

		$file = XOOM_ADDONS_PATH . $base . $dir . 'class-' . self::pathify( $class ) . '.php';

		if ( is_readable( $file ) ) {
			require_once $file;
		}
	}

	/**
	 * Convert a class segment to its hyphenated, lowercase file form.
	 *
	 * @param string $name Class or namespace segment.
	 * @return string
	 */
	private static function pathify( $name ) {
		return strtolower( str_replace( '_', '-', $name ) );
	}
}
