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
	// Selector panel: proportional size glyph for "W×H" label options
	// (replaces the large placeholder icon on options without a swatch image).
	// -----------------------------------------------------------------------

	var SIZE_PATTERN = /(\d+(?:[.,]\d+)?)\s*[×xX*]\s*(\d+(?:[.,]\d+)?)/;
	var GLYPH_MAX_PX = 32;
	var GLYPH_MIN_PX = 8;

	function parseSize( label ) {
		var match = SIZE_PATTERN.exec( label || '' );
		if ( ! match ) {
			return null;
		}

		var w = parseFloat( match[ 1 ].replace( ',', '.' ) );
		var h = parseFloat( match[ 2 ].replace( ',', '.' ) );

		return w > 0 && h > 0 ? { w: w, h: h } : null;
	}

	// Which top-down drawing fits the attribute: a bed/mattress, a sofa, or a plain outline.
	function sizeKind( key ) {
		key = String( key || '' ).toLowerCase();
		if ( /magamis|voodi|madrat|bed|mattress/.test( key ) ) {
			return 'bed';
		}
		if ( /diivan|sofa/.test( key ) ) {
			return 'sofa';
		}
		return 'plain';
	}

	function r1( n ) {
		return Math.round( n * 10 ) / 10;
	}

	function svgRect( x, y, w, h, rx, cls ) {
		return '<rect class="' + cls + '" x="' + r1( x ) + '" y="' + r1( y ) + '" width="' + r1( Math.max( w, 0.5 ) ) + '" height="' + r1( Math.max( h, 0.5 ) ) + '" rx="' + rx + '"/>';
	}

	function svgLine( x1, y1, x2, y2 ) {
		return '<line class="g-line" x1="' + r1( x1 ) + '" y1="' + r1( y1 ) + '" x2="' + r1( x2 ) + '" y2="' + r1( y2 ) + '"/>';
	}

	// Top-down line drawing in a fixed 40x40 box, scaled against the largest size in the group.
	function sizeGlyphSvg( kind, size, maxDim ) {
		var box = 40;
		var scale = GLYPH_MAX_PX / maxDim;
		var w = Math.max( GLYPH_MIN_PX, size.w * scale );
		var h = Math.max( GLYPH_MIN_PX, size.h * scale );
		var x = ( box - w ) / 2;
		var y = ( box - h ) / 2;
		var out = '';

		if ( 'bed' === kind ) {
			var head = 3;
			var pillows = size.w < 120 ? 1 : 2;
			var pad = 2;
			var pw = ( w - pad * ( pillows + 1 ) ) / pillows;
			var ph = Math.min( 5, Math.max( 3, h * 0.17 ) );
			out += svgRect( x, y + head - 0.5, w, h - head + 0.5, 2, 'g-body' );
			out += svgRect( x - 1, y, w + 2, head, 1, 'g-solid' );
			for ( var i = 0; i < pillows; i++ ) {
				out += svgRect( x + pad + i * ( pw + pad ), y + head + 1.5, pw, ph, 1.2, 'g-soft' );
			}
			out += svgLine( x + 1, y + head + ph + 4.5, x + w - 1, y + head + ph + 4.5 );
		} else if ( 'sofa' === kind ) {
			var back = Math.max( 3, h * 0.28 );
			var arm = Math.max( 2.5, w * 0.1 );
			var seats = size.w >= 180 ? 3 : 2;
			var sw = ( w - arm * 2 ) / seats;
			out += svgRect( x, y, w, h, 2, 'g-body' );
			out += svgRect( x, y, w, back, 1.5, 'g-solid' );
			out += svgRect( x, y, arm, h, 1.5, 'g-soft' );
			out += svgRect( x + w - arm, y, arm, h, 1.5, 'g-soft' );
			for ( var s = 1; s < seats; s++ ) {
				out += svgLine( x + arm + s * sw, y + back + 1.5, x + arm + s * sw, y + h - 1.5 );
			}
		} else {
			out += svgRect( x, y, w, h, 2, 'g-body' );
		}

		return '<svg viewBox="0 0 40 40" width="40" height="40" focusable="false">' + out + '</svg>';
	}

	function decorateSizeOptions() {
		document.querySelectorAll( '.sawerna-single-product__selector-options' ).forEach( function ( group ) {
			var items = [];
			var maxDim = 0;

			group.querySelectorAll( '.sawerna-single-product__selector-option' ).forEach( function ( btn ) {
				var media = btn.querySelector( '.sawerna-single-product__selector-option-media' );
				if ( ! media || ! media.querySelector( '.sawerna-single-product__selector-option-icon' ) || media.classList.contains( 'has-size-glyph' ) ) {
					return;
				}

				var size = parseSize( btn.getAttribute( 'data-option-label' ) );
				if ( ! size ) {
					return;
				}

				maxDim = Math.max( maxDim, size.w, size.h );
				items.push( { media: media, size: size, kind: sizeKind( btn.getAttribute( 'data-attribute-key' ) ) } );
			} );

			items.forEach( function ( item ) {
				var glyph = document.createElement( 'span' );
				glyph.className = 'sawerna-single-product__size-glyph sawerna-single-product__size-glyph--' + item.kind;
				glyph.setAttribute( 'aria-hidden', 'true' );
				glyph.innerHTML = sizeGlyphSvg( item.kind, item.size, maxDim );
				item.media.appendChild( glyph );
				item.media.classList.add( 'has-size-glyph' );
			} );
		} );
	}

	// -----------------------------------------------------------------------
	// Bootstrap
	// -----------------------------------------------------------------------

	function init() {
		decorateSizeOptions();
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
