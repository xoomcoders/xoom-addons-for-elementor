/**
 * Reading progress indicator.
 */
( function () {
	'use strict';

	var bar = document.querySelector( '[data-xoom-progress]' );

	if ( ! bar ) {
		return;
	}

	var ticking = false;

	/**
	 * Update the bar to match the current scroll position.
	 */
	function update() {
		ticking = false;

		var doc = document.documentElement;
		var scrollable = doc.scrollHeight - doc.clientHeight;
		var progress = scrollable > 0 ? doc.scrollTop / scrollable : 0;

		bar.style.width = Math.min( Math.max( progress * 100, 0 ), 100 ) + '%';
	}

	/**
	 * Schedule an update on the next animation frame.
	 */
	function requestUpdate() {
		if ( ticking ) {
			return;
		}

		ticking = true;
		window.requestAnimationFrame( update );
	}

	window.addEventListener( 'scroll', requestUpdate, { passive: true } );
	window.addEventListener( 'resize', requestUpdate, { passive: true } );

	update();
}() );
