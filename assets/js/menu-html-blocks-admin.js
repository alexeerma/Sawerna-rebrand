/**
 * Appearance > Menus: keep the "Muuda plokki" link in sync with the
 * HTML block select of each menu item.
 *
 * Enqueued by sawerna_menu_html_blocks_admin_assets()
 * (sawerna-core/includes/menu-html-blocks.php). Each <option> carries its
 * edit URL in data-edit-url (empty when the user cannot edit that block).
 * Delegated, so menu items added via AJAX work too. No jQuery needed.
 */
( function () {
	'use strict';

	function sync( select ) {
		var field = select.closest( '.field-html-block' );
		var link = field ? field.querySelector( '.sawerna-html-block-edit-link' ) : null;
		var option = select.options[ select.selectedIndex ];
		var url = option ? option.getAttribute( 'data-edit-url' ) : '';

		if ( ! link ) {
			return;
		}

		if ( url ) {
			link.setAttribute( 'href', url );
			link.hidden = false;
		} else {
			link.removeAttribute( 'href' );
			link.hidden = true;
		}
	}

	document.addEventListener( 'change', function ( event ) {
		var target = event.target;
		if ( target && target.classList && target.classList.contains( 'edit-menu-item-html-block' ) ) {
			sync( target );
		}
	} );
}() );
