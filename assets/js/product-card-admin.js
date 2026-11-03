/**
 * Product card admin controls.
 *
 * @package Sawerna
 */
/* global sawernaProductCardAdmin, wp */
( function ( $ ) {
	'use strict';

	$( function () {
		$( document ).on( 'click', '[data-product-admin-image-upload="1"]', function ( event ) {
			event.preventDefault();

			var $wrap = $( this ).closest( '[data-product-admin-image]' );
			var $input = $wrap.find( 'input[type="hidden"]' );
			var $preview = $wrap.find( '[data-product-admin-image-preview="1"]' );
			var $remove = $wrap.find( '[data-product-admin-image-remove="1"]' );

			var frame = wp.media( {
				title: ( sawernaProductCardAdmin && sawernaProductCardAdmin.uploadTitle ) ? sawernaProductCardAdmin.uploadTitle : 'Select archive interior image',
				button: {
					text: ( sawernaProductCardAdmin && sawernaProductCardAdmin.uploadButton ) ? sawernaProductCardAdmin.uploadButton : 'Use this image'
				},
				multiple: false
			} );

			frame.on( 'select', function () {
				var attachment = frame.state().get( 'selection' ).first().toJSON();
				var imageUrl = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;

				$input.val( attachment.id );
				$preview.html( '<img src="' + imageUrl + '" alt="" style="max-width:80px; height:auto;">' );
				$remove.show();
			} );

			frame.open();
		} );

		$( document ).on( 'click', '[data-product-admin-image-remove="1"]', function ( event ) {
			event.preventDefault();

			var $wrap = $( this ).closest( '[data-product-admin-image]' );
			$wrap.find( 'input[type="hidden"]' ).val( '' );
			$wrap.find( '[data-product-admin-image-preview="1"]' ).empty();
			$( this ).hide();
		} );
	} );
}( jQuery ) );
