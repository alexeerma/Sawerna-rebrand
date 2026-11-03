/**
 * Frontend swatch interaction — drives WooCommerce's native variation system.
 *
 * When a swatch button is clicked:
 *   1. Update the hidden <select> that WooCommerce variation.js listens to.
 *   2. Trigger a 'change' event on that select so WooCommerce updates the UI
 *      (price, stock, images, add-to-cart state) automatically.
 *   3. Toggle the is-selected state on the visible buttons.
 *
 * @package Sawerna
 */
( function () {
	'use strict';

	// -----------------------------------------------------------------------
	// Swatch click handler
	// -----------------------------------------------------------------------

	function handleSwatchClick( event ) {
		var button = event.target.closest( '.sawerna-swatch' );
		if ( ! button ) {
			return;
		}

		var container = button.closest( '.sawerna-swatches' );
		if ( ! container ) {
			return;
		}

		var attributeName = container.getAttribute( 'data-attribute' );
		if ( ! attributeName ) {
			return;
		}

		var value = button.getAttribute( 'data-value' ) || '';

		// Toggle: clicking the already-selected swatch deselects it.
		if ( button.classList.contains( 'is-selected' ) ) {
			value = '';
		}

		// Update visible button states.
		container.querySelectorAll( '.sawerna-swatch' ).forEach( function ( btn ) {
			var pressed = btn.getAttribute( 'data-value' ) === value && value !== '';
			btn.classList.toggle( 'is-selected', pressed );
			btn.setAttribute( 'aria-pressed', pressed ? 'true' : 'false' );
		} );

		// Sync the hidden <select> and trigger WooCommerce.
		var select = document.querySelector( 'select[name="' + attributeName + '"], select.sawerna-swatches-select[data-attribute_name="' + attributeName + '"]' );
		if ( select ) {
			select.value = value;
			select.dispatchEvent( new Event( 'change', { bubbles: true } ) );
		}
	}

	// -----------------------------------------------------------------------
	// Sync swatch buttons when WooCommerce resets variations
	// (e.g. "Clear" link clicked or page load with pre-selected values)
	// -----------------------------------------------------------------------

	function syncFromSelect( select ) {
		if ( ! select ) {
			return;
		}

		var attributeName = select.getAttribute( 'data-attribute_name' ) || select.getAttribute( 'name' );
		if ( ! attributeName ) {
			return;
		}

		var container = document.querySelector( '.sawerna-swatches[data-attribute="' + attributeName + '"]' );
		if ( ! container ) {
			return;
		}

		var currentValue = select.value || '';

		container.querySelectorAll( '.sawerna-swatch' ).forEach( function ( btn ) {
			var pressed = btn.getAttribute( 'data-value' ) === currentValue && currentValue !== '';
			btn.classList.toggle( 'is-selected', pressed );
			btn.setAttribute( 'aria-pressed', pressed ? 'true' : 'false' );
		} );
	}

	function syncAllSelects() {
		document.querySelectorAll( '.sawerna-swatches-select' ).forEach( syncFromSelect );
	}

	// -----------------------------------------------------------------------
	// Update disabled states based on WooCommerce variation availability
	// -----------------------------------------------------------------------

	function updateDisabledStates() {
		document.querySelectorAll( '.sawerna-swatches' ).forEach( function ( container ) {
			var attributeName = container.getAttribute( 'data-attribute' );
			if ( ! attributeName ) {
				return;
			}

			var select = document.querySelector(
				'.sawerna-swatches-select[data-attribute_name="' + attributeName + '"]'
			);

			if ( ! select ) {
				return;
			}

			var availableValues = Array.from( select.options ).map( function ( opt ) {
				return opt.value;
			} );

			container.querySelectorAll( '.sawerna-swatch' ).forEach( function ( btn ) {
				var value   = btn.getAttribute( 'data-value' ) || '';
				var enabled = availableValues.indexOf( value ) !== -1 || value === '';
				btn.classList.toggle( 'is-disabled', ! enabled );
				btn.disabled = ! enabled;
			} );
		} );
	}

	// -----------------------------------------------------------------------
	// Bootstrap
	// -----------------------------------------------------------------------

	function init() {
		document.addEventListener( 'click', handleSwatchClick );

		// WooCommerce fires woocommerce_variation_has_changed and
		// woocommerce_update_variation_values on the form when it resets or
		// updates. Listen to those via jQuery events.
		var form = document.querySelector( 'form.variations_form' );
		if ( ! form ) {
			return;
		}

		// Use jQuery if available (WooCommerce depends on it anyway).
		if ( typeof jQuery !== 'undefined' ) {
			jQuery( form ).on( 'reset_data woocommerce_update_variation_values', function () {
				syncAllSelects();
				updateDisabledStates();
			} );

			// Also sync after WooCommerce finishes found_variation.
			jQuery( form ).on( 'found_variation', function () {
				syncAllSelects();
			} );
		}

		// Initial sync on load (handles pre-selected values from URL / cart).
		syncAllSelects();
		updateDisabledStates();
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}

}() );
