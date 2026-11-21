<?php
/**
 * Contact page template helpers.
 *
 * @package Sawerna
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function sawerna_contact_template_slug() {
	return 'contact-page.php';
}

function sawerna_contact_icon_google_maps() {
	return '<svg class="sawerna-contact-icon sawerna-contact-icon--google" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path class="sawerna-contact-icon__google-g" fill="currentColor" d="M21.35 11.1h-9.18v2.98h5.27c-.23 1.3-1.39 3.81-5.27 3.81-3.17 0-5.76-2.62-5.76-5.86s2.59-5.86 5.76-5.86c1.8 0 3.01.77 3.7 1.43l2.52-2.43C16.77 3.66 14.68 2.8 12.17 2.8 7.07 2.8 2.93 6.94 2.93 12.03s4.14 9.23 9.24 9.23c5.33 0 8.87-3.75 8.87-9.03 0-.61-.07-1.07-.15-1.53Z"/></svg>';
}

function sawerna_contact_icon_waze() {
	return '<svg class="sawerna-contact-icon sawerna-contact-icon--waze" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path class="sawerna-contact-icon__waze-body" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" d="M18.8 14.1c1-1.2 1.6-2.8 1.6-4.5 0-4-3.2-7.2-7.6-7.2-4.5 0-7.8 3.2-7.8 7.2v.7c0 .9-.6 1.6-1.5 1.8l-.8.2.4.8c.7 1.4 1.9 2.4 3.3 3 .5 1.5 2 2.7 3.7 2.7h4.5"/><circle class="sawerna-contact-icon__waze-wheel" cx="8.2" cy="18.2" r="1.7" fill="currentColor"/><circle class="sawerna-contact-icon__waze-wheel" cx="17.3" cy="18.2" r="1.7" fill="currentColor"/><circle class="sawerna-contact-icon__waze-detail" cx="9" cy="9.2" r="1" fill="currentColor"/><circle class="sawerna-contact-icon__waze-detail" cx="14.9" cy="9.2" r="1" fill="currentColor"/><path class="sawerna-contact-icon__waze-detail" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" d="M9.2 13c1.5 1.2 4.2 1.2 5.7 0"/></svg>';
}

function sawerna_contact_inline_format_allowed_tags() {
	return array(
		'strong' => array(),
		'b'      => array(),
		'em'     => array(),
		'i'      => array(),
	);
}

function sawerna_contact_sanitize_rich_textarea( $value ) {
	return wp_kses( (string) $value, sawerna_contact_inline_format_allowed_tags() );
}

function sawerna_contact_format_rich_text( $value ) {
	$value = sawerna_contact_sanitize_rich_textarea( $value );

	return preg_replace_callback(
		'/\*\*(.+?)\*\*/s',
		static function ( $matches ) {
			return '<strong>' . $matches[1] . '</strong>';
		},
		$value
	);
}

function sawerna_contact_format_contact_line( $line ) {
	$line = trim( (string) $line );

	if ( '' === $line ) {
		return '';
	}

	if ( preg_match( '/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', $line, $matches ) ) {
		$email  = sanitize_email( $matches[0] );
		$parts  = explode( $matches[0], $line, 2 );
		$before = isset( $parts[0] ) ? sawerna_contact_format_rich_text( $parts[0] ) : '';
		$after  = isset( $parts[1] ) ? sawerna_contact_format_rich_text( $parts[1] ) : '';

		return $before . '<a href="' . esc_url( 'mailto:' . $email ) . '">' . esc_html( $matches[0] ) . '</a>' . $after;
	}

	if ( preg_match( '/(\+?[0-9][0-9\s().-]{5,}[0-9])/', $line, $matches ) ) {
		$phone  = preg_replace( '/[^0-9+]/', '', $matches[0] );
		$parts  = explode( $matches[0], $line, 2 );
		$before = isset( $parts[0] ) ? sawerna_contact_format_rich_text( $parts[0] ) : '';
		$after  = isset( $parts[1] ) ? sawerna_contact_format_rich_text( $parts[1] ) : '';

		return $before . '<a href="' . esc_url( 'tel:' . $phone ) . '">' . esc_html( $matches[0] ) . '</a>' . $after;
	}

	return sawerna_contact_format_rich_text( $line );
}

function sawerna_contact_location_defaults() {
	return array(
		'image_id' => 0,
		'name'     => '',
		'address'  => '',
		'hours'    => '',
		'contacts' => '',
		'note'     => '',
		'maps_url' => '',
		'waze_url' => '',
	);
}

function sawerna_contact_dropdown_location_defaults() {
	return array(
		'title'   => '',
		'address' => '',
		'phone'   => '',
		'hours'   => '',
		'note'    => '',
	);
}

function sawerna_contact_person_defaults() {
	return array(
		'image_id' => 0,
		'name'     => '',
		'role'     => '',
		'email'    => '',
		'phone'    => '',
	);
}

function sawerna_contact_default_locations() {
	return array(
		array(
			'image_id' => 0,
			'name'     => __( 'Tallinn', 'sawerna' ),
			'address'  => __( 'Karamelli 2, Tallinn, 11314', 'sawerna' ),
			'hours'    => __( "**E-R** 11.00 - 19.00\n**L** 11.00 - 15.00\n**P** suletud", 'sawerna' ),
			'contacts' => __( "**E-mail:** info@sawerna.ee\n**Telefon:** +372 5687 8750", 'sawerna' ),
			'note'     => __( 'Auto parkimine otse ukse ees - **2 h tasuta**', 'sawerna' ),
			'maps_url' => '',
			'waze_url' => '',
		),
		array(
			'image_id' => 0,
			'name'     => __( 'Tartu', 'sawerna' ),
			'address'  => __( 'Riia 1, Tartu, Tartu Kaubamaja 2. korrus', 'sawerna' ),
			'hours'    => __( "**E-L** 10.00 - 21.00\n**P** 10.00 - 19.00", 'sawerna' ),
			'contacts' => __( "**E-mail:** info@sawerna.ee\n**Telefon:** +372 5687 8750", 'sawerna' ),
			'note'     => __( 'Kaubamaja klientidele **1 h tasuta parkimine** Kaubamaja parklas', 'sawerna' ),
			'maps_url' => '',
			'waze_url' => '',
		),
		array(
			'image_id' => 0,
			'name'     => __( 'Sawerna (Outlet)', 'sawerna' ),
			'address'  => __( 'Sawerna, Hannula, Savarna küla', 'sawerna' ),
			'hours'    => __( "**E-R** 10.00 - 15.00\n**L-P** kokkuleppel", 'sawerna' ),
			'contacts' => __( "**E-mail:** hendri@sawerna.ee\n**Telefon:** +372 5687 9844", 'sawerna' ),
			'note'     => __( 'Parkimine maja ees tasuta.', 'sawerna' ),
			'maps_url' => '',
			'waze_url' => '',
		),
	);
}

function sawerna_contact_default_dropdown_locations() {
	return array(
		array(
			'title'   => __( 'Tallinna salong', 'sawerna' ),
			'address' => __( 'Karamelli 2 / Pärnu mnt 139b', 'sawerna' ),
			'phone'   => '+372 5687 8750',
			'hours'   => __( "E-R 11-19\nL 11-15\nP suletud", 'sawerna' ),
			'note'    => '',
		),
		array(
			'title'   => __( 'Tartu showroom', 'sawerna' ),
			'address' => __( 'Tartu Kaubamaja, 2. korrus, Riia 1', 'sawerna' ),
			'phone'   => '+372 514 7819',
			'hours'   => __( "E-L 10-21\nP 10-19", 'sawerna' ),
			'note'    => __( 'NB! Tegemist on iseteenindava showroom’iga, kus klient saab meie toodetega iseseisvalt tutvuda', 'sawerna' ),
		),
		array(
			'title'   => __( 'Sawerna salong & Outlet', 'sawerna' ),
			'address' => __( 'Hannula kinnistu, Saverna 63418', 'sawerna' ),
			'phone'   => '+372 5911 6104',
			'hours'   => __( "E-R 9-17\nL ja P kokkuleppel", 'sawerna' ),
			'note'    => '',
		),
	);
}

function sawerna_contact_default_people() {
	return array(
		array(
			'image_id' => 0,
			'name'     => __( 'Carl-Johannes Kinnunen', 'sawerna' ),
			'role'     => __( 'Projektimüük', 'sawerna' ),
			'email'    => 'carl@sawerna.ee',
			'phone'    => '+372 591 6104',
		),
		array(
			'image_id' => 0,
			'name'     => __( 'Kristjan Oja', 'sawerna' ),
			'role'     => __( 'Turundus', 'sawerna' ),
			'email'    => 'kristjan@sawerna.ee',
			'phone'    => '+372 5357 7414',
		),
		array(
			'image_id' => 0,
			'name'     => __( 'Armo Perekonnanimi', 'sawerna' ),
			'role'     => __( 'Ost, ladu ja tarned', 'sawerna' ),
			'email'    => 'armo@sawerna.ee',
			'phone'    => '+372 5911 604',
		),
		array(
			'image_id' => 0,
			'name'     => __( 'Roland Perekonnanimi', 'sawerna' ),
			'role'     => __( 'Tootmine', 'sawerna' ),
			'email'    => 'roland@sawerna.ee',
			'phone'    => '+372 5911 610',
		),
	);
}

function sawerna_contact_options_defaults() {
	return array(
		'hero_image_id'      => 0,
		'hero_side_image_id' => 0,
		'hero_subtitle'      => __( 'KONTAKT', 'sawerna' ),
		'hero_title'         => __( 'Leia endale sobiv Sawerna müügipunkt', 'sawerna' ),
		'hero_text'          => __( 'Leiame koos mööbli, mis sobib sinu koju ja elustiiliga. Tule salongi, kirjuta või helista meile.', 'sawerna' ),
		'locations'          => sawerna_contact_default_locations(),
		'dropdown_locations' => sawerna_contact_default_dropdown_locations(),
		'people_title'       => __( 'Oleme alati rõõmuga valmis vastama kõigile sinu küsimustele', 'sawerna' ),
		'people'             => sawerna_contact_default_people(),
	);
}

function sawerna_contact_wpml_register_string( $name, $value ) {
	if ( ! is_string( $value ) || '' === trim( $value ) || ! has_action( 'wpml_register_single_string' ) ) {
		return;
	}

	do_action( 'wpml_register_single_string', 'sawerna-page-options', (string) $name, $value ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Third-party WPML hook.
}

function sawerna_contact_wpml_translate_string( $name, $value ) {
	if ( ! is_string( $value ) || '' === trim( $value ) || ! has_filter( 'wpml_translate_single_string' ) ) {
		return (string) $value;
	}

	return (string) apply_filters( 'wpml_translate_single_string', $value, 'sawerna-page-options', (string) $name ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Third-party WPML hook.
}

function sawerna_contact_sanitize_locations( $locations ) {
	if ( ! is_array( $locations ) ) {
		return array();
	}

	$sanitized = array();

	foreach ( $locations as $location ) {
		if ( ! is_array( $location ) ) {
			continue;
		}

		$item = wp_parse_args( $location, sawerna_contact_location_defaults() );
		$item = array(
			'image_id' => absint( $item['image_id'] ),
			'name'     => sanitize_text_field( $item['name'] ),
			'address'  => sanitize_textarea_field( $item['address'] ),
			'hours'    => sawerna_contact_sanitize_rich_textarea( $item['hours'] ),
			'contacts' => sawerna_contact_sanitize_rich_textarea( $item['contacts'] ),
			'note'     => sawerna_contact_sanitize_rich_textarea( $item['note'] ),
			'maps_url' => esc_url_raw( $item['maps_url'] ),
			'waze_url' => esc_url_raw( $item['waze_url'] ),
		);

		$text_values = array( $item['name'], $item['address'], $item['hours'], $item['contacts'], $item['note'], $item['maps_url'], $item['waze_url'] );

		if ( '' !== trim( implode( '', $text_values ) ) || $item['image_id'] > 0 ) {
			$sanitized[] = $item;
		}
	}

	return $sanitized;
}

function sawerna_contact_sanitize_dropdown_locations( $locations ) {
	if ( ! is_array( $locations ) ) {
		return array();
	}

	$sanitized = array();

	foreach ( $locations as $location ) {
		if ( ! is_array( $location ) ) {
			continue;
		}

		$item = wp_parse_args( $location, sawerna_contact_dropdown_location_defaults() );
		$item = array(
			'title'   => sanitize_text_field( $item['title'] ),
			'address' => sanitize_text_field( $item['address'] ),
			'phone'   => sanitize_text_field( $item['phone'] ),
			'hours'   => sanitize_textarea_field( $item['hours'] ),
			'note'    => sawerna_contact_sanitize_rich_textarea( $item['note'] ),
		);

		if ( '' !== trim( implode( '', $item ) ) ) {
			$sanitized[] = $item;
		}
	}

	return $sanitized;
}

function sawerna_contact_sanitize_people( $people ) {
	if ( ! is_array( $people ) ) {
		return array();
	}

	$sanitized = array();

	foreach ( $people as $person ) {
		if ( ! is_array( $person ) ) {
			continue;
		}

		$item = wp_parse_args( $person, sawerna_contact_person_defaults() );
		$item = array(
			'image_id' => absint( $item['image_id'] ),
			'name'     => sanitize_text_field( $item['name'] ),
			'role'     => sanitize_text_field( $item['role'] ),
			'email'    => sanitize_email( $item['email'] ),
			'phone'    => sanitize_text_field( $item['phone'] ),
		);

		$text_values = array( $item['name'], $item['role'], $item['email'], $item['phone'] );

		if ( '' !== trim( implode( '', $text_values ) ) || $item['image_id'] > 0 ) {
			$sanitized[] = $item;
		}

		if ( count( $sanitized ) >= 4 ) {
			break;
		}
	}

	return $sanitized;
}

function sawerna_contact_sanitize_options( $options ) {
	$options  = is_array( $options ) ? $options : array();
	$defaults = sawerna_contact_options_defaults();

	return array(
		'hero_image_id'      => absint( $options['hero_image_id'] ?? $defaults['hero_image_id'] ),
		'hero_side_image_id' => absint( $options['hero_side_image_id'] ?? $defaults['hero_side_image_id'] ),
		'hero_subtitle'      => sanitize_text_field( $options['hero_subtitle'] ?? $defaults['hero_subtitle'] ),
		'hero_title'         => sanitize_text_field( $options['hero_title'] ?? $defaults['hero_title'] ),
		'hero_text'          => sanitize_textarea_field( $options['hero_text'] ?? $defaults['hero_text'] ),
		'locations'          => sawerna_contact_sanitize_locations( $options['locations'] ?? $defaults['locations'] ),
		'dropdown_locations' => sawerna_contact_sanitize_dropdown_locations( $options['dropdown_locations'] ?? $defaults['dropdown_locations'] ),
		'people_title'       => sanitize_text_field( $options['people_title'] ?? $defaults['people_title'] ),
		'people'             => sawerna_contact_sanitize_people( $options['people'] ?? $defaults['people'] ),
	);
}

function sawerna_contact_register_wpml_option_strings( array $options ) {
	foreach ( array( 'hero_subtitle', 'hero_title', 'hero_text', 'people_title' ) as $field ) {
		sawerna_contact_wpml_register_string( 'contact_' . $field, (string) ( $options[ $field ] ?? '' ) );
	}

	if ( ! empty( $options['locations'] ) && is_array( $options['locations'] ) ) {
		foreach ( $options['locations'] as $index => $location ) {
			if ( ! is_array( $location ) ) {
				continue;
			}

			foreach ( array( 'name', 'address', 'hours', 'contacts', 'note' ) as $field ) {
				sawerna_contact_wpml_register_string( 'contact_locations_' . $index . '_' . $field, (string) ( $location[ $field ] ?? '' ) );
			}
		}
	}

	if ( ! empty( $options['dropdown_locations'] ) && is_array( $options['dropdown_locations'] ) ) {
		foreach ( $options['dropdown_locations'] as $index => $location ) {
			if ( ! is_array( $location ) ) {
				continue;
			}

			foreach ( array( 'title', 'address', 'phone', 'hours', 'note' ) as $field ) {
				sawerna_contact_wpml_register_string( 'contact_dropdown_locations_' . $index . '_' . $field, (string) ( $location[ $field ] ?? '' ) );
			}
		}
	}

	if ( ! empty( $options['people'] ) && is_array( $options['people'] ) ) {
		foreach ( $options['people'] as $index => $person ) {
			if ( ! is_array( $person ) ) {
				continue;
			}

			foreach ( array( 'name', 'role', 'email', 'phone' ) as $field ) {
				sawerna_contact_wpml_register_string( 'contact_people_' . $index . '_' . $field, (string) ( $person[ $field ] ?? '' ) );
			}
		}
	}
}

function sawerna_contact_translate_wpml_option_strings( array $options ) {
	foreach ( array( 'hero_subtitle', 'hero_title', 'hero_text', 'people_title' ) as $field ) {
		$options[ $field ] = sawerna_contact_wpml_translate_string( 'contact_' . $field, (string) ( $options[ $field ] ?? '' ) );
	}

	if ( ! empty( $options['locations'] ) && is_array( $options['locations'] ) ) {
		foreach ( $options['locations'] as $index => $location ) {
			if ( ! is_array( $location ) ) {
				continue;
			}

			foreach ( array( 'name', 'address', 'hours', 'contacts', 'note' ) as $field ) {
				$options['locations'][ $index ][ $field ] = sawerna_contact_wpml_translate_string( 'contact_locations_' . $index . '_' . $field, (string) ( $location[ $field ] ?? '' ) );
			}
		}
	}

	if ( ! empty( $options['dropdown_locations'] ) && is_array( $options['dropdown_locations'] ) ) {
		foreach ( $options['dropdown_locations'] as $index => $location ) {
			if ( ! is_array( $location ) ) {
				continue;
			}

			foreach ( array( 'title', 'address', 'phone', 'hours', 'note' ) as $field ) {
				$options['dropdown_locations'][ $index ][ $field ] = sawerna_contact_wpml_translate_string( 'contact_dropdown_locations_' . $index . '_' . $field, (string) ( $location[ $field ] ?? '' ) );
			}
		}
	}

	if ( ! empty( $options['people'] ) && is_array( $options['people'] ) ) {
		foreach ( $options['people'] as $index => $person ) {
			if ( ! is_array( $person ) ) {
				continue;
			}

			foreach ( array( 'name', 'role', 'email', 'phone' ) as $field ) {
				$options['people'][ $index ][ $field ] = sawerna_contact_wpml_translate_string( 'contact_people_' . $index . '_' . $field, (string) ( $person[ $field ] ?? '' ) );
			}
		}
	}

	return $options;
}

function sawerna_get_contact_options() {
	$stored   = get_option( 'sawerna_contact_options', array() );
	$defaults = sawerna_contact_options_defaults();
	$options  = sawerna_contact_sanitize_options( wp_parse_args( is_array( $stored ) ? $stored : array(), $defaults ) );

	if ( empty( $options['locations'] ) ) {
		$options['locations'] = sawerna_contact_sanitize_locations( $defaults['locations'] );
	}

	if ( empty( $options['dropdown_locations'] ) ) {
		$options['dropdown_locations'] = sawerna_contact_sanitize_dropdown_locations( $defaults['dropdown_locations'] );
	}

	if ( empty( $options['people'] ) ) {
		$options['people'] = sawerna_contact_sanitize_people( $defaults['people'] );
	}

	return sawerna_contact_translate_wpml_option_strings( $options );
}

function sawerna_contact_enqueue_assets() {
	if ( ! is_page_template( sawerna_contact_template_slug() ) ) {
		return;
	}

	$theme_version = defined( 'WP_DEBUG' ) && WP_DEBUG ? filemtime( get_template_directory() . '/style.css' ) : _S_VERSION;
	$css_path      = get_template_directory() . '/assets/css/contact.css';

	if ( file_exists( $css_path ) ) {
		$css_version = defined( 'WP_DEBUG' ) && WP_DEBUG ? filemtime( $css_path ) : $theme_version;
		wp_enqueue_style( 'sawerna-contact', get_template_directory_uri() . '/assets/css/contact.css', array( 'sawerna-style' ), $css_version );
	}
}
add_action( 'wp_enqueue_scripts', 'sawerna_contact_enqueue_assets' );

function sawerna_contact_register_admin_menu() {
	add_submenu_page(
		'sawerna-page-options',
		__( 'Contact Us', 'sawerna' ),
		__( 'Contact Us', 'sawerna' ),
		'manage_options',
		'sawerna-contact-options',
		'sawerna_contact_render_admin_page'
	);
}
add_action( 'admin_menu', 'sawerna_contact_register_admin_menu' );

function sawerna_contact_options_admin_assets( $hook_suffix ) {
	if ( false === strpos( (string) $hook_suffix, 'sawerna-contact-options' ) ) {
		return;
	}

	$theme_version = defined( 'WP_DEBUG' ) && WP_DEBUG ? filemtime( get_template_directory() . '/style.css' ) : _S_VERSION;
	$js_path       = get_template_directory() . '/js/admin-homepage.js';

	wp_enqueue_media();

	if ( file_exists( $js_path ) ) {
		$js_version = defined( 'WP_DEBUG' ) && WP_DEBUG ? filemtime( $js_path ) : $theme_version;
		wp_enqueue_script( 'sawerna-admin-homepage', get_template_directory_uri() . '/js/admin-homepage.js', array( 'jquery' ), $js_version, true );
	}
}
add_action( 'admin_enqueue_scripts', 'sawerna_contact_options_admin_assets' );

function sawerna_contact_handle_admin_save() {
	if ( ! is_admin() || ! isset( $_POST['sawerna_contact_options_nonce'] ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sawerna_contact_options_nonce'] ) ), 'sawerna_contact_options_save' ) ) {
		return;
	}

	$options = sawerna_contact_sanitize_options(
		array(
			'hero_image_id'      => isset( $_POST['hero_image_id'] ) ? wp_unslash( $_POST['hero_image_id'] ) : 0,
			'hero_side_image_id' => isset( $_POST['hero_side_image_id'] ) ? wp_unslash( $_POST['hero_side_image_id'] ) : 0,
			'hero_subtitle'      => isset( $_POST['hero_subtitle'] ) ? wp_unslash( $_POST['hero_subtitle'] ) : '',
			'hero_title'         => isset( $_POST['hero_title'] ) ? wp_unslash( $_POST['hero_title'] ) : '',
			'hero_text'          => isset( $_POST['hero_text'] ) ? wp_unslash( $_POST['hero_text'] ) : '',
			'locations'          => isset( $_POST['contact_locations'] ) ? wp_unslash( $_POST['contact_locations'] ) : array(),
			'dropdown_locations' => isset( $_POST['contact_dropdown_locations'] ) ? wp_unslash( $_POST['contact_dropdown_locations'] ) : array(),
			'people_title'       => isset( $_POST['people_title'] ) ? wp_unslash( $_POST['people_title'] ) : '',
			'people'             => isset( $_POST['contact_people'] ) ? wp_unslash( $_POST['contact_people'] ) : array(),
		)
	);

	sawerna_contact_register_wpml_option_strings( $options );
	update_option( 'sawerna_contact_options', $options );

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'    => 'sawerna-contact-options',
				'updated' => '1',
			),
			admin_url( 'admin.php' )
		)
	);
	exit;
}
add_action( 'admin_init', 'sawerna_contact_handle_admin_save' );

function sawerna_contact_render_location_fields( array $location, $index ) {
	$location = wp_parse_args( $location, sawerna_contact_location_defaults() );
	?>
	<div class="sawerna-home-admin__slide" data-home-slide-item="1">
		<div class="sawerna-home-admin__slide-head">
			<h4><?php echo esc_html( sprintf( /* translators: %s: location number. */ __( 'Location %s', 'sawerna' ), is_numeric( $index ) ? (int) $index + 1 : '' ) ); ?></h4>
			<button type="button" class="button-link-delete" data-home-slide-remove="1"><?php esc_html_e( 'Remove location', 'sawerna' ); ?></button>
		</div>

		<div class="sawerna-home-admin__columns">
			<div class="sawerna-home-admin__card">
				<?php sawerna_homepage_render_image_field( "contact_locations[{$index}][image_id]", (int) $location['image_id'], __( 'Store image', 'sawerna' ) ); ?>
				<p class="sawerna-home-options__field">
					<label for="sawerna-contact-location-name-<?php echo esc_attr( (string) $index ); ?>"><?php esc_html_e( 'Location name', 'sawerna' ); ?></label>
					<input id="sawerna-contact-location-name-<?php echo esc_attr( (string) $index ); ?>" class="regular-text widefat" type="text" name="contact_locations[<?php echo esc_attr( (string) $index ); ?>][name]" value="<?php echo esc_attr( (string) $location['name'] ); ?>">
				</p>
				<p class="sawerna-home-options__field">
					<label for="sawerna-contact-location-address-<?php echo esc_attr( (string) $index ); ?>"><?php esc_html_e( 'Address', 'sawerna' ); ?></label>
					<textarea id="sawerna-contact-location-address-<?php echo esc_attr( (string) $index ); ?>" class="widefat" rows="3" name="contact_locations[<?php echo esc_attr( (string) $index ); ?>][address]"><?php echo esc_textarea( (string) $location['address'] ); ?></textarea>
				</p>
			</div>

			<div class="sawerna-home-admin__card">
				<p class="sawerna-home-options__field">
					<label for="sawerna-contact-location-hours-<?php echo esc_attr( (string) $index ); ?>"><?php esc_html_e( 'Opening hours', 'sawerna' ); ?></label>
					<textarea id="sawerna-contact-location-hours-<?php echo esc_attr( (string) $index ); ?>" class="widefat" rows="4" name="contact_locations[<?php echo esc_attr( (string) $index ); ?>][hours]"><?php echo esc_textarea( (string) $location['hours'] ); ?></textarea>
					<span class="sawerna-home-options__hint"><?php esc_html_e( 'Use **bold text** or <strong>bold text</strong> for bold parts.', 'sawerna' ); ?></span>
				</p>
				<p class="sawerna-home-options__field">
					<label for="sawerna-contact-location-contacts-<?php echo esc_attr( (string) $index ); ?>"><?php esc_html_e( 'Contact details', 'sawerna' ); ?></label>
					<textarea id="sawerna-contact-location-contacts-<?php echo esc_attr( (string) $index ); ?>" class="widefat" rows="3" name="contact_locations[<?php echo esc_attr( (string) $index ); ?>][contacts]"><?php echo esc_textarea( (string) $location['contacts'] ); ?></textarea>
					<span class="sawerna-home-options__hint"><?php esc_html_e( 'Email addresses and phone numbers are linked automatically.', 'sawerna' ); ?></span>
				</p>
				<p class="sawerna-home-options__field">
					<label for="sawerna-contact-location-note-<?php echo esc_attr( (string) $index ); ?>"><?php esc_html_e( 'Extra info', 'sawerna' ); ?></label>
					<textarea id="sawerna-contact-location-note-<?php echo esc_attr( (string) $index ); ?>" class="widefat" rows="3" name="contact_locations[<?php echo esc_attr( (string) $index ); ?>][note]"><?php echo esc_textarea( (string) $location['note'] ); ?></textarea>
					<span class="sawerna-home-options__hint"><?php esc_html_e( 'Use **bold text** or <strong>bold text</strong> for bold parts.', 'sawerna' ); ?></span>
				</p>
				<p class="sawerna-home-options__field">
					<label for="sawerna-contact-location-maps-<?php echo esc_attr( (string) $index ); ?>"><?php esc_html_e( 'Google Maps URL', 'sawerna' ); ?></label>
					<input id="sawerna-contact-location-maps-<?php echo esc_attr( (string) $index ); ?>" class="regular-text widefat" type="url" name="contact_locations[<?php echo esc_attr( (string) $index ); ?>][maps_url]" value="<?php echo esc_attr( (string) $location['maps_url'] ); ?>">
				</p>
				<p class="sawerna-home-options__field">
					<label for="sawerna-contact-location-waze-<?php echo esc_attr( (string) $index ); ?>"><?php esc_html_e( 'Waze URL', 'sawerna' ); ?></label>
					<input id="sawerna-contact-location-waze-<?php echo esc_attr( (string) $index ); ?>" class="regular-text widefat" type="url" name="contact_locations[<?php echo esc_attr( (string) $index ); ?>][waze_url]" value="<?php echo esc_attr( (string) $location['waze_url'] ); ?>">
				</p>
			</div>
		</div>
	</div>
	<?php
}

function sawerna_contact_render_dropdown_location_fields( array $location, $index ) {
	$location = wp_parse_args( $location, sawerna_contact_dropdown_location_defaults() );
	?>
	<div class="sawerna-home-admin__slide" data-home-slide-item="1">
		<div class="sawerna-home-admin__slide-head">
			<h4><?php echo esc_html( sprintf( /* translators: %s: location number. */ __( 'Dropdown location %s', 'sawerna' ), is_numeric( $index ) ? (int) $index + 1 : '' ) ); ?></h4>
			<button type="button" class="button-link-delete" data-home-slide-remove="1"><?php esc_html_e( 'Remove dropdown location', 'sawerna' ); ?></button>
		</div>

		<div class="sawerna-home-admin__columns">
			<div class="sawerna-home-admin__card">
				<p class="sawerna-home-options__field">
					<label for="sawerna-contact-dropdown-title-<?php echo esc_attr( (string) $index ); ?>"><?php esc_html_e( 'Title', 'sawerna' ); ?></label>
					<input id="sawerna-contact-dropdown-title-<?php echo esc_attr( (string) $index ); ?>" class="regular-text widefat" type="text" name="contact_dropdown_locations[<?php echo esc_attr( (string) $index ); ?>][title]" value="<?php echo esc_attr( (string) $location['title'] ); ?>">
				</p>
				<p class="sawerna-home-options__field">
					<label for="sawerna-contact-dropdown-address-<?php echo esc_attr( (string) $index ); ?>"><?php esc_html_e( 'Address', 'sawerna' ); ?></label>
					<input id="sawerna-contact-dropdown-address-<?php echo esc_attr( (string) $index ); ?>" class="regular-text widefat" type="text" name="contact_dropdown_locations[<?php echo esc_attr( (string) $index ); ?>][address]" value="<?php echo esc_attr( (string) $location['address'] ); ?>">
				</p>
				<p class="sawerna-home-options__field">
					<label for="sawerna-contact-dropdown-phone-<?php echo esc_attr( (string) $index ); ?>"><?php esc_html_e( 'Phone', 'sawerna' ); ?></label>
					<input id="sawerna-contact-dropdown-phone-<?php echo esc_attr( (string) $index ); ?>" class="regular-text widefat" type="text" name="contact_dropdown_locations[<?php echo esc_attr( (string) $index ); ?>][phone]" value="<?php echo esc_attr( (string) $location['phone'] ); ?>">
				</p>
			</div>

			<div class="sawerna-home-admin__card">
				<p class="sawerna-home-options__field">
					<label for="sawerna-contact-dropdown-hours-<?php echo esc_attr( (string) $index ); ?>"><?php esc_html_e( 'Opening hours', 'sawerna' ); ?></label>
					<textarea id="sawerna-contact-dropdown-hours-<?php echo esc_attr( (string) $index ); ?>" class="widefat" rows="4" name="contact_dropdown_locations[<?php echo esc_attr( (string) $index ); ?>][hours]"><?php echo esc_textarea( (string) $location['hours'] ); ?></textarea>
					<span class="sawerna-home-options__hint"><?php esc_html_e( 'One line per row in the menu dropdown.', 'sawerna' ); ?></span>
				</p>
				<p class="sawerna-home-options__field">
					<label for="sawerna-contact-dropdown-note-<?php echo esc_attr( (string) $index ); ?>"><?php esc_html_e( 'Note', 'sawerna' ); ?></label>
					<textarea id="sawerna-contact-dropdown-note-<?php echo esc_attr( (string) $index ); ?>" class="widefat" rows="3" name="contact_dropdown_locations[<?php echo esc_attr( (string) $index ); ?>][note]"><?php echo esc_textarea( (string) $location['note'] ); ?></textarea>
					<span class="sawerna-home-options__hint"><?php esc_html_e( 'Optional. Use **bold text** or <strong>bold text</strong> for bold parts.', 'sawerna' ); ?></span>
				</p>
			</div>
		</div>
	</div>
	<?php
}

function sawerna_contact_render_person_fields( array $person, $index ) {
	$person = wp_parse_args( $person, sawerna_contact_person_defaults() );
	?>
	<div class="sawerna-home-admin__slide">
		<div class="sawerna-home-admin__slide-head">
			<h4><?php echo esc_html( sprintf( /* translators: %s: card number. */ __( 'Person card %s', 'sawerna' ), is_numeric( $index ) ? (int) $index + 1 : '' ) ); ?></h4>
		</div>

		<div class="sawerna-home-admin__columns">
			<div class="sawerna-home-admin__card">
				<?php sawerna_homepage_render_image_field( "contact_people[{$index}][image_id]", (int) $person['image_id'], __( 'Person image', 'sawerna' ) ); ?>
			</div>

			<div class="sawerna-home-admin__card">
				<p class="sawerna-home-options__field">
					<label for="sawerna-contact-person-name-<?php echo esc_attr( (string) $index ); ?>"><?php esc_html_e( 'Name', 'sawerna' ); ?></label>
					<input id="sawerna-contact-person-name-<?php echo esc_attr( (string) $index ); ?>" class="regular-text widefat" type="text" name="contact_people[<?php echo esc_attr( (string) $index ); ?>][name]" value="<?php echo esc_attr( (string) $person['name'] ); ?>">
				</p>
				<p class="sawerna-home-options__field">
					<label for="sawerna-contact-person-role-<?php echo esc_attr( (string) $index ); ?>"><?php esc_html_e( 'Role', 'sawerna' ); ?></label>
					<input id="sawerna-contact-person-role-<?php echo esc_attr( (string) $index ); ?>" class="regular-text widefat" type="text" name="contact_people[<?php echo esc_attr( (string) $index ); ?>][role]" value="<?php echo esc_attr( (string) $person['role'] ); ?>">
				</p>
				<p class="sawerna-home-options__field">
					<label for="sawerna-contact-person-email-<?php echo esc_attr( (string) $index ); ?>"><?php esc_html_e( 'Email', 'sawerna' ); ?></label>
					<input id="sawerna-contact-person-email-<?php echo esc_attr( (string) $index ); ?>" class="regular-text widefat" type="email" name="contact_people[<?php echo esc_attr( (string) $index ); ?>][email]" value="<?php echo esc_attr( (string) $person['email'] ); ?>">
				</p>
				<p class="sawerna-home-options__field">
					<label for="sawerna-contact-person-phone-<?php echo esc_attr( (string) $index ); ?>"><?php esc_html_e( 'Phone', 'sawerna' ); ?></label>
					<input id="sawerna-contact-person-phone-<?php echo esc_attr( (string) $index ); ?>" class="regular-text widefat" type="text" name="contact_people[<?php echo esc_attr( (string) $index ); ?>][phone]" value="<?php echo esc_attr( (string) $person['phone'] ); ?>">
				</p>
			</div>
		</div>
	</div>
	<?php
}

function sawerna_contact_render_admin_page() {
	$options = sawerna_get_contact_options();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Contact page options', 'sawerna' ); ?></h1>

		<?php if ( isset( $_GET['updated'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only flag for the post-save admin notice. ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Contact page options saved.', 'sawerna' ); ?></p></div>
		<?php endif; ?>

		<style>
			.sawerna-home-options__grid { display:grid; gap:24px; max-width:1100px; }
			.sawerna-home-options__card { background:#fff; border:1px solid #dcdcde; border-radius:12px; padding:24px; }
			.sawerna-home-options__card h2 { margin:0 0 12px; font-size:20px; }
			.sawerna-home-options__field { margin:0 0 18px; }
			.sawerna-home-options__field label { display:block; margin:0 0 8px; font-weight:600; }
			.sawerna-home-options__hint { margin:6px 0 0; color:#646970; }
			.sawerna-home-admin__field { margin: 0 0 16px; }
			.sawerna-home-admin__field label,
			.sawerna-home-admin__card h5,
			.sawerna-home-admin__slide-head h4 { display: block; margin: 0 0 8px; font-weight: 600; }
			.sawerna-home-admin__slides { display: grid; gap: 18px; }
			.sawerna-home-admin__slide { border: 1px solid #dcdcde; border-radius: 12px; padding: 18px; background: #fff; }
			.sawerna-home-admin__slide-head { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 18px; }
			.sawerna-home-admin__columns { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; }
			.sawerna-home-admin__card { border: 1px solid #e2e4e7; border-radius: 10px; padding: 16px; background: #f6f7f7; }
			.sawerna-home-admin__image-preview { margin-bottom: 10px; }
			.sawerna-home-admin__image-preview img { display: block; width: 100%; max-width: 240px; border-radius: 8px; height: auto; }
			.sawerna-home-admin__image-actions { display: flex; flex-wrap: wrap; gap: 8px; }
			.sawerna-home-admin__footer { margin-top: 18px; }
			@media (max-width: 960px) {
				.sawerna-home-admin__columns { grid-template-columns: 1fr; }
			}
		</style>

		<form method="post" action="">
			<?php wp_nonce_field( 'sawerna_contact_options_save', 'sawerna_contact_options_nonce' ); ?>

			<div class="sawerna-home-options__grid">
				<section class="sawerna-home-options__card">
					<h2><?php esc_html_e( 'Contact hero', 'sawerna' ); ?></h2>
					<?php sawerna_homepage_render_image_field( 'hero_image_id', (int) $options['hero_image_id'], __( 'Hero background image', 'sawerna' ) ); ?>
					<?php sawerna_homepage_render_image_field( 'hero_side_image_id', (int) $options['hero_side_image_id'], __( 'Hero right image', 'sawerna' ) ); ?>
					<p class="sawerna-home-options__field">
						<label for="hero_subtitle"><?php esc_html_e( 'Subheader', 'sawerna' ); ?></label>
						<input id="hero_subtitle" class="regular-text widefat" type="text" name="hero_subtitle" value="<?php echo esc_attr( (string) $options['hero_subtitle'] ); ?>">
					</p>
					<p class="sawerna-home-options__field">
						<label for="hero_title"><?php esc_html_e( 'Title', 'sawerna' ); ?></label>
						<input id="hero_title" class="regular-text widefat" type="text" name="hero_title" value="<?php echo esc_attr( (string) $options['hero_title'] ); ?>">
					</p>
					<p class="sawerna-home-options__field">
						<label for="hero_text"><?php esc_html_e( 'Subtext', 'sawerna' ); ?></label>
						<textarea id="hero_text" class="widefat" rows="4" name="hero_text"><?php echo esc_textarea( (string) $options['hero_text'] ); ?></textarea>
					</p>
				</section>

				<section class="sawerna-home-options__card">
					<h2><?php esc_html_e( 'Contact locations', 'sawerna' ); ?></h2>
					<p class="sawerna-home-options__hint"><?php esc_html_e( 'Add the physical stores shown on the Contact page. Text fields are registered for WPML when you save.', 'sawerna' ); ?></p>

					<div id="sawerna-contact-locations-root" class="sawerna-home-admin__slides" data-home-slides-root="1">
						<?php foreach ( $options['locations'] as $index => $location ) : ?>
							<?php sawerna_contact_render_location_fields( $location, $index ); ?>
						<?php endforeach; ?>
					</div>

					<p class="sawerna-home-admin__footer">
						<button type="button" class="button button-primary" data-home-slide-add="1" data-home-slides-root-target="sawerna-contact-locations-root" data-home-template-id="sawerna-contact-location-template"><?php esc_html_e( 'Add location', 'sawerna' ); ?></button>
					</p>

					<template id="sawerna-contact-location-template">
						<?php sawerna_contact_render_location_fields( sawerna_contact_location_defaults(), '__INDEX__' ); ?>
					</template>
				</section>

				<section class="sawerna-home-options__card">
					<h2><?php esc_html_e( 'Header contact dropdown', 'sawerna' ); ?></h2>
					<p class="sawerna-home-options__hint"><?php esc_html_e( 'Edit the compact contact data used by [sawerna_contact_dropdown] inside the header menu HTML block.', 'sawerna' ); ?></p>

					<div id="sawerna-contact-dropdown-locations-root" class="sawerna-home-admin__slides" data-home-slides-root="1">
						<?php foreach ( $options['dropdown_locations'] as $index => $location ) : ?>
							<?php sawerna_contact_render_dropdown_location_fields( $location, $index ); ?>
						<?php endforeach; ?>
					</div>

					<p class="sawerna-home-admin__footer">
						<button type="button" class="button button-primary" data-home-slide-add="1" data-home-slides-root-target="sawerna-contact-dropdown-locations-root" data-home-template-id="sawerna-contact-dropdown-location-template"><?php esc_html_e( 'Add dropdown location', 'sawerna' ); ?></button>
					</p>

					<template id="sawerna-contact-dropdown-location-template">
						<?php sawerna_contact_render_dropdown_location_fields( sawerna_contact_dropdown_location_defaults(), '__INDEX__' ); ?>
					</template>
				</section>

				<section class="sawerna-home-options__card">
					<h2><?php esc_html_e( 'Contact people cards', 'sawerna' ); ?></h2>
					<p class="sawerna-home-options__hint"><?php esc_html_e( 'Edit the four people cards shown below the store locations on the Contact page.', 'sawerna' ); ?></p>

					<p class="sawerna-home-options__field">
						<label for="people_title"><?php esc_html_e( 'Section title', 'sawerna' ); ?></label>
						<input id="people_title" class="regular-text widefat" type="text" name="people_title" value="<?php echo esc_attr( (string) $options['people_title'] ); ?>">
					</p>

					<div class="sawerna-home-admin__slides">
						<?php for ( $index = 0; $index < 4; $index++ ) : ?>
							<?php sawerna_contact_render_person_fields( $options['people'][ $index ] ?? sawerna_contact_person_defaults(), $index ); ?>
						<?php endfor; ?>
					</div>
				</section>
			</div>

			<?php submit_button( __( 'Save Contact Options', 'sawerna' ) ); ?>
		</form>
	</div>
	<?php
}

/**
 * Split a textarea value into non-empty display lines.
 *
 * @param string $value Textarea value.
 * @return string[]
 */
function sawerna_contact_dropdown_lines( $value ) {
	return array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) $value ) ) ) );
}

/**
 * Convert a phone number to a tel: URL value.
 *
 * @param string $phone Phone number.
 * @return string
 */
function sawerna_contact_dropdown_phone_href( $phone ) {
	return preg_replace( '/[^0-9+]/', '', (string) $phone );
}

/**
 * Format a contact dropdown location title.
 *
 * @param string $title Location title.
 * @return string
 */
function sawerna_contact_dropdown_format_title( $title ) {
	$title = esc_html( (string) $title );

	return preg_replace( '/\b(showroom|outlet)\b/i', '<em>$1</em>', $title );
}

/**
 * Render multiline text for the contact dropdown.
 *
 * @param string[] $lines Text lines.
 * @return void
 */
function sawerna_contact_dropdown_render_lines( array $lines ) {
	foreach ( $lines as $line ) {
		echo '<span>' . wp_kses_post( sawerna_contact_format_rich_text( $line ) ) . '</span>';
	}
}

/**
 * Render the contact dropdown shortcode.
 *
 * Usage: [sawerna_contact_dropdown]
 *
 * @param array<string, mixed> $atts Shortcode attributes.
 * @return string
 */
function sawerna_contact_dropdown_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'limit'    => 3,
			'more_url' => '/kontakt/',
			'note'     => __( 'Tehtud ausalt ja hingega!', 'sawerna' ),
		),
		$atts,
		'sawerna_contact_dropdown'
	);

	$options   = sawerna_get_contact_options();
	$locations = ! empty( $options['dropdown_locations'] ) && is_array( $options['dropdown_locations'] ) ? $options['dropdown_locations'] : array();
	$locations = array_slice( $locations, 0, max( 1, absint( $atts['limit'] ) ) );

	if ( empty( $locations ) ) {
		return '';
	}

	ob_start();
	?>
	<section class="sawerna-contact-dropdown" aria-label="<?php esc_attr_e( 'Salongide kontaktid', 'sawerna' ); ?>">
		<div class="sawerna-contact-dropdown__grid">
			<?php foreach ( $locations as $location ) : ?>
				<?php
				$location   = wp_parse_args( is_array( $location ) ? $location : array(), sawerna_contact_dropdown_location_defaults() );
				$address    = trim( (string) $location['address'] );
				$hour_lines = sawerna_contact_dropdown_lines( $location['hours'] );
				$note_lines = sawerna_contact_dropdown_lines( $location['note'] );
				$phone      = trim( (string) $location['phone'] );
				?>
				<article class="sawerna-contact-dropdown__card">
					<?php if ( '' !== trim( (string) $location['title'] ) ) : ?>
						<h3><?php echo wp_kses_post( sawerna_contact_dropdown_format_title( (string) $location['title'] ) ); ?></h3>
					<?php endif; ?>

					<dl>
						<?php if ( '' !== $address ) : ?>
							<div>
								<dt><?php esc_html_e( 'Aadress:', 'sawerna' ); ?></dt>
								<dd><?php echo esc_html( $address ); ?></dd>
							</div>
						<?php endif; ?>

						<?php if ( '' !== $phone ) : ?>
							<div>
								<dt><?php esc_html_e( 'Telefon:', 'sawerna' ); ?></dt>
								<dd>
									<a href="<?php echo esc_url( 'tel:' . sawerna_contact_dropdown_phone_href( $phone ) ); ?>">
										<?php echo esc_html( $phone ); ?>
									</a>
								</dd>
							</div>
						<?php endif; ?>

						<?php if ( ! empty( $hour_lines ) ) : ?>
							<div>
								<dt><?php esc_html_e( 'Lahtiolekuajad:', 'sawerna' ); ?></dt>
								<dd><?php sawerna_contact_dropdown_render_lines( $hour_lines ); ?></dd>
							</div>
						<?php endif; ?>

						<?php if ( ! empty( $note_lines ) ) : ?>
							<div>
								<dt><?php esc_html_e( 'Märkus:', 'sawerna' ); ?></dt>
								<dd><?php sawerna_contact_dropdown_render_lines( $note_lines ); ?></dd>
							</div>
						<?php endif; ?>
					</dl>
				</article>
			<?php endforeach; ?>
		</div>

		<div class="sawerna-contact-dropdown__footer">
			<?php
			$note_svg_path = get_template_directory() . '/assets/SVG/Asset 24.svg';
			$note_svg_url  = get_template_directory_uri() . '/assets/SVG/Asset%2024.svg';
			?>
			<?php if ( file_exists( $note_svg_path ) ) : ?>
				<img class="sawerna-contact-dropdown__note" src="<?php echo esc_url( $note_svg_url ); ?>" alt="<?php echo esc_attr( (string) $atts['note'] ); ?>" loading="lazy" decoding="async">
			<?php elseif ( '' !== trim( (string) $atts['note'] ) ) : ?>
				<p class="sawerna-contact-dropdown__note"><?php echo esc_html( (string) $atts['note'] ); ?></p>
			<?php endif; ?>

			<a class="sawerna-contact-dropdown__more" href="<?php echo esc_url( (string) $atts['more_url'] ); ?>">
				<span><?php esc_html_e( 'Näita rohkem kontakte', 'sawerna' ); ?></span>
				<span aria-hidden="true">→</span>
			</a>
		</div>
	</section>
	<?php

	return (string) ob_get_clean();
}
add_shortcode( 'sawerna_contact_dropdown', 'sawerna_contact_dropdown_shortcode' );
