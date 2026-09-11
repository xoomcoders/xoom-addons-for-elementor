/**
 * Xoom Addons — shared frontend widget behaviour.
 *
 * Only loaded on pages that actually render a widget which depends on it
 * (currently the Counter).
 */
( function () {
	'use strict';

	var counters = document.querySelectorAll( '[data-xoom-counter]' );

	if ( ! counters.length ) {
		return;
	}

	var reduceMotion = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	/**
	 * Format a numeric value for display.
	 *
	 * @param {number}  value     Value to format.
	 * @param {boolean} separator Whether to group thousands.
	 * @return {string} Formatted value.
	 */
	function format( value, separator ) {
		var rounded = Math.round( value );

		return separator ? rounded.toLocaleString( 'en-US' ) : String( rounded );
	}

	/**
	 * Animate a single counter from its start to its end value.
	 *
	 * @param {HTMLElement} root Counter root element.
	 */
	function animate( root ) {
		var target = root.querySelector( '[data-xoom-counter-value]' );

		if ( ! target ) {
			return;
		}

		var start = parseFloat( root.getAttribute( 'data-start' ) ) || 0;
		var end = parseFloat( root.getAttribute( 'data-end' ) ) || 0;
		var duration = parseInt( root.getAttribute( 'data-duration' ), 10 ) || 1600;
		var separator = '1' === root.getAttribute( 'data-separator' );

		// The end value is already rendered server side; keep it when we cannot
		// animate so the figure is never wrong.
		if ( reduceMotion || 'function' !== typeof window.requestAnimationFrame ) {
			target.textContent = format( end, separator );
			return;
		}

		var startTime = null;

		/**
		 * Advance the animation.
		 *
		 * @param {number} timestamp Animation frame timestamp.
		 */
		function step( timestamp ) {
			if ( null === startTime ) {
				startTime = timestamp;
			}

			var progress = Math.min( ( timestamp - startTime ) / duration, 1 );
			var eased = 1 - Math.pow( 1 - progress, 3 );

			target.textContent = format( start + ( end - start ) * eased, separator );

			if ( progress < 1 ) {
				window.requestAnimationFrame( step );
			} else {
				target.textContent = format( end, separator );
			}
		}

		window.requestAnimationFrame( step );
	}

	if ( 'IntersectionObserver' in window ) {
		var observer = new IntersectionObserver(
			function ( entries ) {
				entries.forEach( function ( entry ) {
					if ( ! entry.isIntersecting ) {
						return;
					}

					observer.unobserve( entry.target );
					animate( entry.target );
				} );
			},
			{ threshold: 0.25 }
		);

		counters.forEach( function ( counter ) {
			observer.observe( counter );
		} );

		return;
	}

	counters.forEach( animate );
}() );
