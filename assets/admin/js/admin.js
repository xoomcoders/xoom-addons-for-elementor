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

	/* -------------------------------------------------- Dialogs & pending */

	var shell = document.querySelector( '.xoom-shell' );

	/**
	 * Fill the `%s` placeholder of a localised template.
	 *
	 * @param {string} template Template containing `%s`.
	 * @param {string} value    Replacement value.
	 * @return {string} Filled string.
	 */
	function fill( template, value ) {
		return String( template ).replace( '%s', value );
	}

	/**
	 * Fill the `%d` placeholder of a localised template.
	 *
	 * @param {string} template Template containing `%d`.
	 * @param {number} value    Replacement value.
	 * @return {string} Filled string.
	 */
	function fillCount( template, value ) {
		return String( template ).replace( '%d', value );
	}

	/**
	 * The singular noun for a component scope.
	 *
	 * @param {string} scope Component scope.
	 * @return {string} Localised noun.
	 */
	function scopeNoun( scope ) {
		return 'modules' === scope ? t( 'singularExtensions', 'Extension' ) : t( 'singularWidgets', 'Widget' );
	}

	/**
	 * The plural noun for a component scope.
	 *
	 * @param {string} scope Component scope.
	 * @return {string} Localised noun.
	 */
	function scopePlural( scope ) {
		return 'modules' === scope ? t( 'nounExtensions', 'extensions' ) : t( 'nounWidgets', 'widgets' );
	}

	/**
	 * Keep an anchored popover inside the viewport.
	 *
	 * @param {HTMLElement} layer  Full screen layer.
	 * @param {HTMLElement} dialog Popover element.
	 * @param {HTMLElement} anchor Element to sit next to, when any.
	 */
	function positionPopover( layer, dialog, anchor ) {
		if ( ! anchor || ! anchor.getBoundingClientRect ) {
			return;
		}

		var rect = anchor.getBoundingClientRect();
		var box = dialog.getBoundingClientRect();
		var gap = 10;
		var pad = 12;
		var top = rect.bottom + gap;
		var left = rect.left + rect.width / 2 - box.width / 2;

		if ( top + box.height > window.innerHeight - pad ) {
			top = rect.top - box.height - gap;
		}

		top = Math.max( pad, Math.min( top, window.innerHeight - box.height - pad ) );
		left = Math.max( pad, Math.min( left, window.innerWidth - box.width - pad ) );

		dialog.style.top = top + 'px';
		dialog.style.left = left + 'px';
	}

	/**
	 * Open a confirmation popover and resolve with the user's choice.
	 *
	 * The dialog traps Tab between its own actions, confirms on Enter and
	 * dismisses on Escape or a click on the backdrop. Focus returns to the
	 * element that opened it once closed.
	 *
	 * @param {Object}      options          Dialog options.
	 * @param {string}      options.title    Heading text.
	 * @param {string}      [options.text]   Supporting copy.
	 * @param {string}      [options.confirm] Confirm button label.
	 * @param {string}      [options.cancel]  Cancel button label.
	 * @param {string}      [options.tone]    `danger` or `primary`.
	 * @param {HTMLElement} [options.anchor]  Element to anchor the popover to.
	 * @return {Promise<boolean>} Whether the user confirmed.
	 */
	function confirmPopover( options ) {
		options = options || {};

		return new Promise( function ( resolve ) {
			var danger = 'danger' === options.tone;
			var layer = document.createElement( 'div' );
			layer.className = 'xoom-popover-layer';

			var dialog = document.createElement( 'div' );
			dialog.className = 'xoom-popover' + ( options.anchor ? '' : ' is-centered' );
			dialog.setAttribute( 'role', 'dialog' );
			dialog.setAttribute( 'aria-modal', 'true' );
			dialog.setAttribute( 'aria-labelledby', 'xoom-popover-title' );

			var head = document.createElement( 'div' );
			head.className = 'xoom-popover__head';

			var icon = document.createElement( 'span' );
			icon.className = 'xoom-popover__icon' + ( danger ? ' is-danger' : '' );
			icon.setAttribute( 'aria-hidden', 'true' );
			icon.innerHTML = '<span class="dashicons ' + ( danger ? 'dashicons-warning' : 'dashicons-info-outline' ) + '"></span>';
			head.appendChild( icon );

			var copy = document.createElement( 'div' );
			copy.className = 'xoom-popover__copy';

			var title = document.createElement( 'p' );
			title.className = 'xoom-popover__title';
			title.id = 'xoom-popover-title';
			title.textContent = options.title || '';
			copy.appendChild( title );

			if ( options.text ) {
				var text = document.createElement( 'p' );
				text.className = 'xoom-popover__text';
				text.id = 'xoom-popover-text';
				text.textContent = options.text;
				copy.appendChild( text );
				dialog.setAttribute( 'aria-describedby', 'xoom-popover-text' );
			}

			head.appendChild( copy );
			dialog.appendChild( head );

			var actions = document.createElement( 'div' );
			actions.className = 'xoom-popover__actions';

			var cancel = document.createElement( 'button' );
			cancel.type = 'button';
			cancel.className = 'xoom-btn xoom-btn--ghost';
			cancel.textContent = options.cancel || t( 'cancel', 'Cancel' );

			var accept = document.createElement( 'button' );
			accept.type = 'button';
			accept.className = 'xoom-btn ' + ( danger ? 'xoom-btn--danger' : 'xoom-btn--primary' );
			accept.textContent = options.confirm || t( 'confirmLabel', 'Confirm' );

			actions.appendChild( cancel );
			actions.appendChild( accept );
			dialog.appendChild( actions );
			layer.appendChild( dialog );
			( shell || document.body ).appendChild( layer );

			positionPopover( layer, dialog, options.anchor );

			var previousFocus = document.activeElement;
			accept.focus();

			function reposition() {
				positionPopover( layer, dialog, options.anchor );
			}

			function close( result ) {
				window.removeEventListener( 'resize', reposition );
				window.removeEventListener( 'scroll', reposition, true );
				layer.removeEventListener( 'keydown', onKeydown );
				layer.removeEventListener( 'mousedown', onBackdrop );
				layer.remove();

				if ( previousFocus && previousFocus.focus ) {
					previousFocus.focus();
				}

				resolve( result );
			}

			function onBackdrop( event ) {
				if ( event.target === layer ) {
					close( false );
				}
			}

			function onKeydown( event ) {
				if ( 'Escape' === event.key ) {
					event.stopPropagation();
					close( false );
					return;
				}

				if ( 'Tab' !== event.key ) {
					return;
				}

				var items = [ cancel, accept ];
				var index = items.indexOf( document.activeElement );
				var next = event.shiftKey ? index - 1 : index + 1;

				if ( next < 0 ) {
					next = items.length - 1;
				} else if ( next >= items.length ) {
					next = 0;
				}

				event.preventDefault();
				items[ next ].focus();
			}

			cancel.addEventListener( 'click', function () {
				close( false );
			} );

			accept.addEventListener( 'click', function () {
				close( true );
			} );

			layer.addEventListener( 'mousedown', onBackdrop );
			layer.addEventListener( 'keydown', onKeydown );
			window.addEventListener( 'resize', reposition );
			window.addEventListener( 'scroll', reposition, true );
		} );
	}

	var pendingBar = null;
	var pendingState = null;

	/**
	 * Build the sticky pending-changes action bar.
	 *
	 * @return {HTMLElement} The bar element.
	 */
	function buildPendingBar() {
		var bar = document.createElement( 'div' );
		bar.className = 'xoom-pending';
		bar.setAttribute( 'role', 'region' );
		bar.setAttribute( 'aria-label', t( 'pendingRegion', 'Pending changes' ) );

		var inner = document.createElement( 'div' );
		inner.className = 'xoom-pending__inner';

		var icon = document.createElement( 'span' );
		icon.className = 'xoom-pending__icon';
		icon.setAttribute( 'aria-hidden', 'true' );
		icon.innerHTML = '<span class="dashicons dashicons-update-alt"></span>';

		var text = document.createElement( 'div' );
		text.className = 'xoom-pending__text';

		var title = document.createElement( 'strong' );
		title.className = 'xoom-pending__title';
		title.setAttribute( 'data-xoom-pending-title', '' );

		var desc = document.createElement( 'span' );
		desc.className = 'xoom-pending__desc';
		desc.setAttribute( 'data-xoom-pending-desc', '' );

		text.appendChild( title );
		text.appendChild( desc );

		var actions = document.createElement( 'div' );
		actions.className = 'xoom-pending__actions';

		var cancel = document.createElement( 'button' );
		cancel.type = 'button';
		cancel.className = 'xoom-btn xoom-btn--ghost';
		cancel.setAttribute( 'data-xoom-pending-cancel', '' );
		cancel.textContent = t( 'cancel', 'Cancel' );

		var save = document.createElement( 'button' );
		save.type = 'button';
		save.className = 'xoom-btn xoom-btn--primary';
		save.setAttribute( 'data-xoom-pending-save', '' );
		save.textContent = t( 'saveChanges', 'Save changes' );

		actions.appendChild( cancel );
		actions.appendChild( save );

		inner.appendChild( icon );
		inner.appendChild( text );
		inner.appendChild( actions );
		bar.appendChild( inner );

		cancel.addEventListener( 'click', function () {
			var button = pendingState ? pendingState.button : null;

			cancelPending();

			if ( button && button.focus ) {
				button.focus();
			}
		} );

		save.addEventListener( 'click', function () {
			savePending( save );
		} );

		return bar;
	}

	/**
	 * Reveal the pending bar and describe the staged change.
	 *
	 * @param {Object} state Pending state.
	 */
	function showPending( state ) {
		if ( ! pendingBar ) {
			pendingBar = buildPendingBar();
			( shell || document.body ).appendChild( pendingBar );
		}

		var title = pendingBar.querySelector( '[data-xoom-pending-title]' );
		var desc = pendingBar.querySelector( '[data-xoom-pending-desc]' );
		var count = state.ids ? state.ids.length : 0;

		if ( title ) {
			if ( ! state.ids ) {
				title.textContent = t( 'pendingAll', 'Apply to all components' );
			} else if ( 1 === count ) {
				title.textContent = t( 'pendingOne', '1 change pending' );
			} else {
				title.textContent = fillCount( t( 'pendingMany', '%d changes pending' ), count );
			}
		}

		if ( desc ) {
			desc.textContent = fill(
				state.enabled ? t( 'pendingEnable', 'Enable all %s' ) : t( 'pendingDisable', 'Disable all %s' ),
				scopePlural( state.scope )
			);
		}

		pendingBar.hidden = false;

		if ( shell ) {
			shell.classList.add( 'has-pending' );
		}

		var save = pendingBar.querySelector( '[data-xoom-pending-save]' );

		if ( save ) {
			save.focus();
		}
	}

	/**
	 * Discard the staged change and restore the previewed cards.
	 */
	function cancelPending() {
		if ( pendingState && pendingState.ids ) {
			pendingState.ids.forEach( function ( id ) {
				paintComponent( pendingState.scope, id, pendingState.previous[ id ] );
			} );
		}

		pendingState = null;

		hidePending();
	}

	/**
	 * Hide the pending bar and release the reserved space.
	 */
	function hidePending() {
		if ( pendingBar ) {
			pendingBar.hidden = true;
		}

		if ( shell ) {
			shell.classList.remove( 'has-pending' );
		}
	}

	/**
	 * Stage a set of changes for review in the pending bar.
	 *
	 * @param {string}      scope   Component scope.
	 * @param {boolean}     enabled Target state.
	 * @param {string[]|null} ids   Affected ids, or null for every component.
	 * @param {HTMLElement} button  The button that started the action.
	 */
	function stageChanges( scope, enabled, ids, button ) {
		cancelPending();

		var previous = {};

		if ( ids ) {
			ids.forEach( function ( id ) {
				previous[ id ] = ! enabled;
				paintComponent( scope, id, enabled );
			} );
		}

		pendingState = {
			scope: scope,
			enabled: enabled,
			ids: ids,
			previous: previous,
			button: button,
		};

		showPending( pendingState );
	}

	/**
	 * Persist the staged change.
	 *
	 * @param {HTMLElement} saveButton The bar's save button.
	 */
	function savePending( saveButton ) {
		var state = pendingState;

		if ( ! state ) {
			return;
		}

		setBusy( saveButton, true );
		notify( t( 'saving' ), 'busy' );

		request( 'xoom_addons_bulk', { scope: state.scope, enabled: state.enabled, ids: state.ids || [] } )
			.then( function ( data ) {
				var button = state.button;

				pendingState = null;
				hidePending();

				notify( t( 'saved', 'Changes saved.' ), 'success' );
				applyCounts( data.scope, data.counts );

				if ( state.ids ) {
					state.ids.forEach( function ( id ) {
						paintComponent( state.scope, id, state.enabled );
					} );
				}

				applyFilters();

				if ( button && button.focus ) {
					button.focus();
				}
			} )
			.catch( function ( error ) {
				cancelPending();
				notify( error.message, 'error' );
			} )
			.finally( function () {
				setBusy( saveButton, false );
			} );
	}

	document.addEventListener( 'keydown', function ( event ) {
		if ( 'Escape' !== event.key || ! pendingState ) {
			return;
		}

		var button = pendingState.button;

		cancelPending();

		if ( button && button.focus ) {
			button.focus();
		}
	} );

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
		var message;

		pending[ scope ] = {};

		if ( ! ids.length ) {
			return;
		}

		ids.forEach( function ( id ) {
			previous[ id ] = ! changes[ id ];
		} );

		if ( 1 < ids.length ) {
			message = t( 'saved', 'Changes saved.' );
		} else if ( changes[ ids[0] ] ) {
			message = fill( t( 'enabledNoun', '%s enabled.' ), scopeNoun( scope ) );
		} else {
			message = fill( t( 'disabledNoun', '%s disabled.' ), scopeNoun( scope ) );
		}

		notify( t( 'saving' ), 'busy' );

		request( 'xoom_addons_sync', { scope: scope, status: changes } )
			.then( function ( data ) {
				notify( message, 'success' );
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

	/**
	 * Whether a component is currently enabled.
	 *
	 * @param {string} scope Component scope.
	 * @param {string} id    Component id.
	 * @return {boolean} Current state.
	 */
	function isEnabled( scope, id ) {
		var input = document.querySelector( '[data-xoom-toggle][data-scope="' + scope + '"][data-id="' + id + '"]' );

		return ! input || input.checked;
	}

	/**
	 * Apply a bulk change straight away, without staging.
	 *
	 * @param {string}      scope   Component scope.
	 * @param {boolean}     enabled Target state.
	 * @param {string[]|null} ids   Affected ids, or null for every component.
	 * @param {HTMLElement} button  The button that started the action.
	 */
	function applyBulk( scope, enabled, ids, button ) {
		var message = enabled
			? fill( t( 'enabledNoun', '%s enabled.' ), scopeNoun( scope ) )
			: fill( t( 'disabledNoun', '%s disabled.' ), scopeNoun( scope ) );

		setBusy( button, true );
		notify( t( 'saving' ), 'busy' );

		request( 'xoom_addons_bulk', { scope: scope, enabled: enabled, ids: ids || [] } )
			.then( function ( data ) {
				notify( message, 'success' );
				applyCounts( data.scope, data.counts );

				if ( ids ) {
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
	}

	document.addEventListener( 'click', function ( event ) {
		var button = event.target.closest( '[data-xoom-bulk]' );

		if ( ! button ) {
			return;
		}

		event.preventDefault();

		var scope = button.getAttribute( 'data-scope' );
		var enabled = 'enable' === button.getAttribute( 'data-xoom-bulk' );
		var visible = visibleIds( scope );
		var changed = null;

		if ( visible && ! visible.length ) {
			notify( t( 'noResults' ), 'error' );
			return;
		}

		if ( visible ) {
			changed = visible.filter( function ( id ) {
				return isEnabled( scope, id ) !== enabled;
			} );
		}

		if ( changed && ! changed.length ) {
			notify(
				enabled
					? t( 'alreadyEnabled', 'Everything is already enabled.' )
					: t( 'alreadyDisabled', 'Everything is already disabled.' ),
				'success'
			);
			return;
		}

		if ( changed && 1 === changed.length ) {
			applyBulk( scope, enabled, changed, button );
			return;
		}

		// Several components change at once (or the whole catalog): stage the
		// change and let the pending bar confirm it.
		stageChanges( scope, enabled, changed, button );
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

		var group = button.getAttribute( 'data-group' );

		confirmPopover( {
			title: t( 'restoreTitle', 'Restore defaults?' ),
			text: t( 'restoreText', 'These settings will be reset to their default values. This cannot be undone.' ),
			confirm: t( 'restoreCta', 'Restore defaults' ),
			tone: 'danger',
			anchor: button,
		} ).then( function ( confirmed ) {
			if ( ! confirmed ) {
				return;
			}

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
