/**
 * Xoom Addons — admin dashboard behaviour.
 *
 * Vanilla JS, no dependencies. Every request is batched and nonce signed.
 */
( function () {
	'use strict';

	var config = window.xoomAddonsAdmin || {};
	var strings = config.i18n || {};
	var DEBOUNCE = 350;

	/**
	 * Translate a string provided by wp_localize_script.
	 *
	 * @param {string} key      String key.
	 * @param {string} fallback Fallback text.
	 * @return {string} Localised string.
	 */
	function t( key, fallback ) {
		return strings[ key ] || fallback || key;
	}

	/**
	 * Append a value to an URLSearchParams instance, expanding arrays and objects.
	 *
	 * @param {URLSearchParams} body  Request body.
	 * @param {string}          key   Field name.
	 * @param {*}               value Field value.
	 */
	function append( body, key, value ) {
		if ( Array.isArray( value ) ) {
			value.forEach( function ( item, index ) {
				append( body, key + '[' + index + ']', item );
			} );
			return;
		}

		if ( value && 'object' === typeof value ) {
			Object.keys( value ).forEach( function ( childKey ) {
				append( body, key + '[' + childKey + ']', value[ childKey ] );
			} );
			return;
		}

		if ( 'boolean' === typeof value ) {
			body.append( key, value ? '1' : '0' );
			return;
		}

		if ( null === value || 'undefined' === typeof value ) {
			return;
		}

		body.append( key, String( value ) );
	}

	/**
	 * Perform a nonce signed admin-ajax request.
	 *
	 * @param {string} action Action suffix.
	 * @param {Object} data   Request payload.
	 * @return {Promise<Object>} Resolves with the response payload.
	 */
	function request( action, data ) {
		var body = new URLSearchParams();

		body.append( 'action', action );
		body.append( 'nonce', config.nonce || '' );

		Object.keys( data || {} ).forEach( function ( key ) {
			append( body, key, data[ key ] );
		} );

		return fetch( config.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
			body: body.toString(),
		} )
			.then( function ( response ) {
				return response.json();
			} )
			.then( function ( payload ) {
				if ( ! payload || ! payload.success ) {
					var message = payload && payload.data && payload.data.message ? payload.data.message : t( 'error' );
					throw new Error( message );
				}

				return payload.data;
			} );
	}

	/* ------------------------------------------------------------ Feedback */

	var statusRegion = document.querySelector( '[data-xoom-toasts]' );
	var toastTimer = null;

	/**
	 * Show a transient status message in the header.
	 *
	 * @param {string} message Message text.
	 * @param {string} type    `success`, `error` or `busy`.
	 */
	function notify( message, type ) {
		if ( ! statusRegion ) {
			return;
		}

		statusRegion.innerHTML = '';

		var toast = document.createElement( 'span' );
		toast.className = 'xoom-toast xoom-toast--' + ( type || 'success' );
		toast.textContent = message;
		statusRegion.appendChild( toast );

		if ( toastTimer ) {
			window.clearTimeout( toastTimer );
		}

		if ( 'busy' !== type ) {
			toastTimer = window.setTimeout( function () {
				statusRegion.innerHTML = '';
			}, 3200 );
		}
	}

	/**
	 * Toggle a button's busy state.
	 *
	 * @param {HTMLElement} element Target element.
	 * @param {boolean}     busy    Whether the element is busy.
	 */
	function setBusy( element, busy ) {
		if ( ! element ) {
			return;
		}

		element.classList.toggle( 'is-busy', !! busy );
		element.setAttribute( 'aria-busy', busy ? 'true' : 'false' );
	}

	/* ------------------------------------------------------------ Counters */

	/**
	 * Write fresh counts into the dashboard tiles.
	 *
	 * @param {string} scope  `widgets` or `modules`.
	 * @param {Object} counts Count map from the server.
	 */
	function applyCounts( scope, counts ) {
		if ( ! counts ) {
			return;
		}

		Object.keys( counts ).forEach( function ( key ) {
			var target = document.querySelector( '[data-xoom-stat="' + scope + '-' + key + '"]' );

			if ( target ) {
				target.textContent = counts[ key ];
			}
		} );
	}

	/* --------------------------------------------------------- Components */

	/**
	 * Reflect an enabled state onto a card and its toggle.
	 *
	 * @param {string}  scope   Component scope.
	 * @param {string}  id      Component id.
	 * @param {boolean} enabled New state.
	 */
	function paintComponent( scope, id, enabled ) {
		var input = document.querySelector( '[data-xoom-toggle][data-scope="' + scope + '"][data-id="' + id + '"]' );

		if ( ! input || input.disabled ) {
			return;
		}

		input.checked = enabled;
		input.setAttribute( 'aria-checked', enabled ? 'true' : 'false' );

		var card = input.closest( '[data-xoom-component]' );

		if ( ! card ) {
			return;
		}

		card.setAttribute( 'data-status', enabled ? 'active' : 'inactive' );
		card.classList.toggle( 'is-enabled', enabled );
		card.classList.toggle( 'is-disabled', ! enabled );
	}

	var pending = {};
	var flushTimers = {};

	/**
	 * Queue a state change and schedule a batched save.
	 *
	 * @param {string}  scope   Component scope.
	 * @param {string}  id      Component id.
	 * @param {boolean} enabled New state.
	 */
	function queueChange( scope, id, enabled ) {
		if ( ! pending[ scope ] ) {
			pending[ scope ] = {};
		}

		pending[ scope ][ id ] = enabled;

		if ( flushTimers[ scope ] ) {
			window.clearTimeout( flushTimers[ scope ] );
		}

		flushTimers[ scope ] = window.setTimeout( function () {
			flush( scope );
		}, DEBOUNCE );
	}

	/**
	 * Persist every queued change for a scope in one request.
	 *
	 * @param {string} scope Component scope.
	 */
	function flush( scope ) {
		var changes = pending[ scope ] || {};
		var ids = Object.keys( changes );
		var previous = {};

		pending[ scope ] = {};

		if ( ! ids.length ) {
			return;
		}

		ids.forEach( function ( id ) {
			previous[ id ] = ! changes[ id ];
		} );

		notify( t( 'saving' ), 'busy' );

		request( 'xoom_addons_sync', { scope: scope, status: changes } )
			.then( function ( data ) {
				notify( t( 'saved' ), 'success' );
				applyCounts( data.scope, data.counts );
			} )
			.catch( function ( error ) {
				notify( error.message, 'error' );
				ids.forEach( function ( id ) {
					paintComponent( scope, id, previous[ id ] );
				} );
				applyFilters();
			} );
	}

	document.addEventListener( 'change', function ( event ) {
		var input = event.target.closest( '[data-xoom-toggle]' );

		if ( ! input ) {
			return;
		}

		var scope = input.getAttribute( 'data-scope' );
		var id = input.getAttribute( 'data-id' );

		paintComponent( scope, id, input.checked );
		queueChange( scope, id, input.checked );
		applyFilters();
	} );

	/* --------------------------------------------------------------- Bulk */

	/**
	 * Collect the ids of every visible component in a scope.
	 *
	 * @param {string} scope Component scope.
	 * @return {string[]|null} Visible ids, or null when no grid is present.
	 */
	function visibleIds( scope ) {
		var cards = document.querySelectorAll( '[data-xoom-component][data-scope="' + scope + '"]' );

		if ( ! cards.length ) {
			return null;
		}

		var ids = [];

		cards.forEach( function ( card ) {
			if ( ! card.classList.contains( 'is-hidden' ) ) {
				ids.push( card.getAttribute( 'data-id' ) );
			}
		} );

		return ids;
	}

	document.addEventListener( 'click', function ( event ) {
		var button = event.target.closest( '[data-xoom-bulk]' );

		if ( ! button ) {
			return;
		}

		event.preventDefault();

		var scope = button.getAttribute( 'data-scope' );
		var enabled = 'enable' === button.getAttribute( 'data-xoom-bulk' );
		var ids = visibleIds( scope );

		if ( null === ids ) {
			// No grid on this screen: the action applies to every component.
			if ( ! window.confirm( t( 'confirmAll' ) ) ) {
				return;
			}
		} else if ( ! ids.length ) {
			notify( t( 'noResults' ), 'error' );
			return;
		} else if ( ids.length > 1 && ! window.confirm( t( 'confirmAll' ) ) ) {
			return;
		}

		setBusy( button, true );
		notify( t( 'saving' ), 'busy' );

		request( 'xoom_addons_bulk', { scope: scope, enabled: enabled, ids: ids || [] } )
			.then( function ( data ) {
				notify( t( 'saved' ), 'success' );
				applyCounts( data.scope, data.counts );

				if ( null !== ids ) {
					ids.forEach( function ( id ) {
						paintComponent( scope, id, enabled );
					} );
				}

				applyFilters();
			} )
			.catch( function ( error ) {
				notify( error.message, 'error' );
			} )
			.finally( function () {
				setBusy( button, false );
			} );
	} );

	/* ------------------------------------------------------------ Filters */

	var filters = {
		search: '',
		status: 'all',
		pkg: 'all',
		collection: '',
	};

	var searchTimer = null;

	/**
	 * Whether a card passes the active filters.
	 *
	 * @param {HTMLElement} card Component card.
	 * @return {boolean} Match result.
	 */
	function matches( card ) {
		if ( 'all' !== filters.status && card.getAttribute( 'data-status' ) !== filters.status ) {
			return false;
		}

		if ( 'all' !== filters.pkg && card.getAttribute( 'data-package' ) !== filters.pkg ) {
			return false;
		}

		if ( filters.collection ) {
			var categories = ( card.getAttribute( 'data-categories' ) || '' ).split( /\s+/ );

			if ( -1 === categories.indexOf( filters.collection ) ) {
				return false;
			}
		}

		if ( filters.search && -1 === ( card.getAttribute( 'data-tags' ) || '' ).indexOf( filters.search ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Apply the current filters to the grid.
	 */
	function applyFilters() {
		var cards = document.querySelectorAll( '[data-xoom-component]' );

		if ( ! cards.length ) {
			return;
		}

		var visible = 0;

		cards.forEach( function ( card ) {
			var match = matches( card );

			card.classList.toggle( 'is-hidden', ! match );

			if ( match ) {
				visible++;
			}
		} );

		var results = document.querySelector( '[data-xoom-results]' );

		if ( results ) {
			var template = 1 === visible ? t( 'resultsOne' ) : t( 'resultsMany' );
			results.textContent = template.replace( '%d', visible );
		}

		var empty = document.querySelector( '[data-xoom-empty]' );

		if ( empty ) {
			empty.hidden = visible > 0;
		}
	}

	/**
	 * Mark one button active inside its group.
	 *
	 * @param {NodeList} group   Buttons sharing an attribute.
	 * @param {Element}  current Selected button.
	 */
	function selectOne( group, current ) {
		group.forEach( function ( button ) {
			var active = button === current;

			button.classList.toggle( 'is-active', active );
			button.setAttribute( 'aria-pressed', active ? 'true' : 'false' );
		} );
	}

	var searchField = document.querySelector( '[data-xoom-search]' );

	if ( searchField ) {
		searchField.addEventListener( 'input', function () {
			if ( searchTimer ) {
				window.clearTimeout( searchTimer );
			}

			searchTimer = window.setTimeout( function () {
				filters.search = searchField.value.trim().toLowerCase();
				applyFilters();
			}, 120 );
		} );
	}

	var statusButtons = document.querySelectorAll( '[data-xoom-status]' );

	statusButtons.forEach( function ( button ) {
		button.addEventListener( 'click', function () {
			filters.status = button.getAttribute( 'data-xoom-status' );
			selectOne( statusButtons, button );
			applyFilters();
		} );
	} );

	var packageButtons = document.querySelectorAll( '[data-xoom-package]' );

	packageButtons.forEach( function ( button ) {
		button.addEventListener( 'click', function () {
			filters.pkg = button.getAttribute( 'data-xoom-package' );
			selectOne( packageButtons, button );
			applyFilters();
		} );
	} );

	var collectionButtons = document.querySelectorAll( '[data-xoom-filter]' );

	collectionButtons.forEach( function ( button ) {
		button.addEventListener( 'click', function () {
			filters.collection = button.getAttribute( 'data-xoom-filter' );
			selectOne( collectionButtons, button );
			applyFilters();
		} );
	} );

	applyFilters();

	/* ----------------------------------------------------------- Settings */

	/**
	 * Push server values back into a settings form.
	 *
	 * @param {HTMLElement} form   Settings form.
	 * @param {Object}      values Server response values.
	 */
	function syncFields( form, values ) {
		if ( ! form || ! values ) {
			return;
		}

		Object.keys( values ).forEach( function ( key ) {
			var field = form.querySelector( '[name="' + key + '"]' );

			if ( ! field ) {
				return;
			}

			if ( 'bool' === field.getAttribute( 'data-xoom-setting' ) ) {
				var raw = values[ key ];
				field.checked = true === raw || 1 === raw || '1' === String( raw );
				field.setAttribute( 'aria-checked', field.checked ? 'true' : 'false' );
			} else {
				field.value = values[ key ];
			}
		} );
	}

	document.addEventListener( 'submit', function ( event ) {
		var form = event.target.closest( '[data-xoom-settings-form]' );

		if ( ! form ) {
			return;
		}

		event.preventDefault();

		var group = form.getAttribute( 'data-group' );
		var values = {};

		form.querySelectorAll( '[data-xoom-setting]' ).forEach( function ( field ) {
			var name = field.getAttribute( 'name' );

			if ( ! name ) {
				return;
			}

			if ( 'bool' === field.getAttribute( 'data-xoom-setting' ) ) {
				values[ name ] = field.checked ? 1 : 0;
			} else {
				values[ name ] = field.value;
			}
		} );

		var submit = form.querySelector( 'button[type="submit"]' );

		setBusy( submit, true );
		notify( t( 'saving' ), 'busy' );

		request( 'xoom_addons_save_settings', { group: group, values: values } )
			.then( function ( data ) {
				notify( data.message || t( 'saved' ), 'success' );
				syncFields( form, data.values );
			} )
			.catch( function ( error ) {
				notify( error.message, 'error' );
			} )
			.finally( function () {
				setBusy( submit, false );
			} );
	} );

	document.addEventListener( 'click', function ( event ) {
		var button = event.target.closest( '[data-xoom-reset]' );

		if ( ! button ) {
			return;
		}

		event.preventDefault();

		if ( ! window.confirm( t( 'resetConfirm' ) ) ) {
			return;
		}

		var group = button.getAttribute( 'data-group' );

		setBusy( button, true );
		notify( t( 'saving' ), 'busy' );

		request( 'xoom_addons_reset_group', { group: group } )
			.then( function ( data ) {
				notify( data.message, 'success' );
				syncFields( button.closest( '.xoom-tabpanel' ).querySelector( '[data-xoom-settings-form]' ), data.values );
			} )
			.catch( function ( error ) {
				notify( error.message, 'error' );
			} )
			.finally( function () {
				setBusy( button, false );
			} );
	} );

	/* --------------------------------------------------------------- Tabs */

	var tabButtons = document.querySelectorAll( '[data-xoom-tab]' );

	/**
	 * Activate a settings tab.
	 *
	 * @param {Element} button Tab button.
	 */
	function activateTab( button ) {
		var group = button.getAttribute( 'data-xoom-tab' );

		tabButtons.forEach( function ( other ) {
			var active = other === button;

			other.classList.toggle( 'is-active', active );
			other.setAttribute( 'aria-selected', active ? 'true' : 'false' );
			other.tabIndex = active ? 0 : -1;
		} );

		document.querySelectorAll( '[data-xoom-tabpanel]' ).forEach( function ( panel ) {
			panel.hidden = panel.getAttribute( 'data-xoom-tabpanel' ) !== group;
		} );
	}

	var tabList = Array.prototype.slice.call( tabButtons );

	tabButtons.forEach( function ( button ) {
		button.addEventListener( 'click', function () {
			activateTab( button );
		} );

		button.addEventListener( 'keydown', function ( event ) {
			if ( -1 === [ 'ArrowRight', 'ArrowLeft', 'Home', 'End' ].indexOf( event.key ) ) {
				return;
			}

			event.preventDefault();

			var index = tabList.indexOf( button );

			if ( 'ArrowRight' === event.key ) {
				index = ( index + 1 ) % tabList.length;
			} else if ( 'ArrowLeft' === event.key ) {
				index = ( index - 1 + tabList.length ) % tabList.length;
			} else if ( 'Home' === event.key ) {
				index = 0;
			} else {
				index = tabList.length - 1;
			}

			tabList[ index ].focus();
			activateTab( tabList[ index ] );
		} );
	} );
}() );
