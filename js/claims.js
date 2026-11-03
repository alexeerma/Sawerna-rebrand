/* global sawernaClaims */
( function () {
	'use strict';

	var form        = document.getElementById( 'sawerna-claims-form' );
	var submit      = document.getElementById( 'sawerna-claims-submit' );
	var error       = document.getElementById( 'sawerna-claims-error' );
	var success     = document.getElementById( 'sawerna-claims-success' );
	var successMsg  = document.getElementById( 'sawerna-claims-success-message' );
	var fileInput   = document.getElementById( 'claim_attachment' );
	var fileName    = document.getElementById( 'sawerna-claims-file-name' );
	var filePlaceholder = document.getElementById( 'sawerna-claims-file-placeholder' );
	var fileClear   = document.getElementById( 'sawerna-claims-file-clear' );

	if ( ! form ) {
		return;
	}

	var allowedTypes = [ 'image/jpeg', 'image/png', 'image/webp', 'image/gif', 'application/pdf' ];

	function showError( message ) {
		error.textContent = message;
		error.removeAttribute( 'hidden' );
		error.scrollIntoView( { behavior: 'smooth', block: 'nearest' } );
	}

	function hideError() {
		error.setAttribute( 'hidden', '' );
		error.textContent = '';
	}

	function clearFile() {
		fileInput.value   = '';
		fileName.setAttribute( 'hidden', '' );
		fileClear.setAttribute( 'hidden', '' );
		filePlaceholder.removeAttribute( 'hidden' );
	}

	if ( fileInput ) {
		fileInput.addEventListener( 'change', function () {
			var file = fileInput.files[ 0 ];

			if ( ! file ) {
				clearFile();
				return;
			}

			if ( allowedTypes.indexOf( file.type ) === -1 ) {
				showError( sawernaClaims.i18n.fileType );
				clearFile();
				return;
			}

			if ( file.size > sawernaClaims.maxFileSize ) {
				showError( sawernaClaims.i18n.fileTooBig );
				clearFile();
				return;
			}

			hideError();
			fileName.textContent = file.name;
			fileName.removeAttribute( 'hidden' );
			fileClear.removeAttribute( 'hidden' );
			filePlaceholder.setAttribute( 'hidden', '' );
		} );

		fileClear.addEventListener( 'click', function ( e ) {
			e.preventDefault();
			clearFile();
		} );
	}

	form.addEventListener( 'submit', function ( e ) {
		e.preventDefault();
		hideError();

		var data = new FormData( form );
		data.append( 'action', 'sawerna_submit_claim' );
		data.append( 'nonce', sawernaClaims.nonce );
		if ( sawernaClaims.lang ) {
			data.append( 'lang', sawernaClaims.lang );
		}

		submit.disabled    = true;
		submit.textContent = sawernaClaims.i18n.sending;

		fetch( sawernaClaims.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body: data,
		} )
			.then( function ( response ) {
				return response.json();
			} )
			.then( function ( json ) {
				if ( json.success ) {
					form.setAttribute( 'hidden', '' );
					successMsg.textContent = json.data.message;
					success.removeAttribute( 'hidden' );
				} else {
					showError( json.data.message || sawernaClaims.i18n.errorGeneric );
					submit.disabled    = false;
					submit.textContent = sawernaClaims.i18n.submit;
				}
			} )
			.catch( function () {
				showError( sawernaClaims.i18n.errorGeneric );
				submit.disabled    = false;
				submit.textContent = sawernaClaims.i18n.submit;
			} );
	} );
} )();
