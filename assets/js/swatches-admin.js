/**
 * Swatch admin: color picker + media uploader
 *
 * @package Sawerna
 */
/* global sawernaSwatchesAdmin, wp */
( function ( $ ) {
	'use strict';

	// -----------------------------------------------------------------------
	// Color picker
	// -----------------------------------------------------------------------

	function initColorPicker() {
		if ( ! $.fn.wpColorPicker || ! $( '.sawerna-color-picker' ).length ) {
			return;
		}

		$( '.sawerna-color-picker' ).wpColorPicker();
	}

	function syncSwatchTypeFields() {
		var $typeField = $( '#sawerna_attribute_swatch_type_term_screen' );
		if ( ! $typeField.length ) {
			return;
		}

		var activeType = $typeField.val() || 'select';
		$( '[data-swatch-field-type]' ).removeClass( 'is-active' );
		$( '[data-swatch-field-type="' + activeType + '"]' ).addClass( 'is-active' );
	}

	// -----------------------------------------------------------------------
	// Media uploader (image swatches)
	// -----------------------------------------------------------------------

	function initImageUploader() {
		$( document ).on( 'click', '.sawerna-swatch-upload-btn', function ( e ) {
			e.preventDefault();

			if ( ! wp || ! wp.media ) {
				return;
			}

			var $btn    = $( this );
			var $wrap   = $btn.closest( '.sawerna-swatch-image-uploader' );
			var $input  = $wrap.find( 'input[name="sawerna_swatch_image"]' );
			var $preview = $wrap.find( '.sawerna-swatch-image-preview' );

			var frame = wp.media( {
				title:    ( sawernaSwatchesAdmin && sawernaSwatchesAdmin.uploadTitle ) ? sawernaSwatchesAdmin.uploadTitle : 'Select swatch image',
				button:   { text: ( sawernaSwatchesAdmin && sawernaSwatchesAdmin.uploadButton ) ? sawernaSwatchesAdmin.uploadButton : 'Use this image' },
				multiple: false,
			} );

			frame.on( 'select', function () {
				var attachment = frame.state().get( 'selection' ).first().toJSON();
				var previewUrl = attachment.url;

				if ( attachment.sizes ) {
					if ( attachment.sizes.thumbnail && attachment.sizes.thumbnail.url ) {
						previewUrl = attachment.sizes.thumbnail.url;
					} else if ( attachment.sizes.medium && attachment.sizes.medium.url ) {
						previewUrl = attachment.sizes.medium.url;
					}
				}

				$input.val( attachment.id );
				$preview.html( '<img src="' + previewUrl + '" alt="" style="max-width:60px;">' );

				if ( ! $wrap.find( '.sawerna-swatch-remove-btn' ).length ) {
					$wrap.append( '<button type="button" class="button sawerna-swatch-remove-btn">Remove</button>' );
				}
			} );

			frame.open();
		} );

		$( document ).on( 'click', '.sawerna-swatch-remove-btn', function ( e ) {
			e.preventDefault();

			var $btn  = $( this );
			var $wrap = $btn.closest( '.sawerna-swatch-image-uploader' );

			$wrap.find( 'input[name="sawerna_swatch_image"]' ).val( '' );
			$wrap.find( '.sawerna-swatch-image-preview' ).html( '' );
			$btn.remove();
		} );
	}

	// -----------------------------------------------------------------------
	// Init on DOM ready
	// -----------------------------------------------------------------------

	$( function () {
		initColorPicker();
		initImageUploader();
		syncSwatchTypeFields();

		$( document ).on( 'change', '#sawerna_attribute_swatch_type_term_screen', function () {
			syncSwatchTypeFields();
		} );
	} );

}( jQuery ) );
