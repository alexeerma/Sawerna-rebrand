/**
 * Block editor notice for an HTML block that is used in menus.
 *
 * Enqueued by sawerna_html_block_usage_block_editor_notice()
 * (sawerna-core/includes/html-block-admin.php) with window.sawernaHtmlBlockUsage.
 */
( function ( wp, data ) {
	'use strict';

	if ( ! wp || ! wp.data || ! wp.domReady || ! data || ! data.message ) {
		return;
	}

	wp.domReady( function () {
		wp.data.dispatch( 'core/notices' ).createNotice( 'info', data.message, {
			id: 'sawerna-html-block-usage-notice',
			isDismissible: true,
		} );
	} );
}( window.wp, window.sawernaHtmlBlockUsage ) );
