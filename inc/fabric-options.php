<?php
/**
 * Single product options admin page.
 *
 * Tabs:
 *  - Kangavalik  : fabric card under the "Vali kattekangas" row (sawerna_page_options['fabric_banner_*']:
 *                  title, text, link_label, link_url, image_id). Per-product text override lives in
 *                  product meta _sawerna_fabric_banner_text (sawerna-core includes/single-product.php).
 *  - Järelmaks   : payment provider list     (sawerna_page_options['jarelmaks_providers'])
 *
 * @package Sawerna
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the "Single product options" submenu under Page options.
 */
function sawerna_fabric_register_admin_menu(): void {
	add_submenu_page(
		'sawerna-page-options',
		__( 'Single product options', 'sawerna' ),
		__( 'Single product options', 'sawerna' ),
		'manage_options',
		'sawerna-fabric-options',
		'sawerna_fabric_render_admin_page'
	);
}
add_action( 'admin_menu', 'sawerna_fabric_register_admin_menu' );

/**
 * Handle form submission on admin_init (all tabs saved together).
 */
function sawerna_fabric_handle_admin_save(): void {
	if ( ! is_admin() || ! isset( $_POST['sawerna_single_product_options_nonce'] ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( ! wp_verify_nonce(
		sanitize_text_field( wp_unslash( $_POST['sawerna_single_product_options_nonce'] ) ),
		'sawerna_single_product_options_save'
	) ) {
		return;
	}

	$active_tab = isset( $_POST['active_tab'] ) ? sanitize_key( $_POST['active_tab'] ) : 'kangavalik';
	$existing   = (array) get_option( 'sawerna_page_options', array() );

	if ( 'kangavalik' === $active_tab ) {
		// ── Kangavalik tab ────────────────────────────────────────────────────
		$existing['fabric_banner_title']      = isset( $_POST['fabric_banner_title'] )
			? sanitize_text_field( wp_unslash( $_POST['fabric_banner_title'] ) )
			: '';
		$existing['fabric_banner_text']       = isset( $_POST['fabric_banner_text'] )
			? sanitize_textarea_field( wp_unslash( $_POST['fabric_banner_text'] ) )
			: '';
		$existing['fabric_banner_link_label'] = isset( $_POST['fabric_banner_link_label'] )
			? sanitize_text_field( wp_unslash( $_POST['fabric_banner_link_label'] ) )
			: '';
		$existing['fabric_banner_link_url']   = isset( $_POST['fabric_banner_link_url'] )
			? esc_url_raw( trim( (string) wp_unslash( $_POST['fabric_banner_link_url'] ) ) ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- esc_url_raw sanitizes.
			: '';
		$image_id                             = isset( $_POST['fabric_banner_image_id'] ) ? absint( $_POST['fabric_banner_image_id'] ) : 0;
		$existing['fabric_banner_image_id']   = ( $image_id && wp_attachment_is_image( $image_id ) ) ? $image_id : 0;
	} elseif ( 'jarelmaks' === $active_tab ) {
		// ── Järelmaks tab ─────────────────────────────────────────────────────
		$raw_providers = isset( $_POST['jarelmaks_providers'] ) && is_array( $_POST['jarelmaks_providers'] )
			? $_POST['jarelmaks_providers'] // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			: array();

		$providers = array();
		foreach ( $raw_providers as $row ) {
			$label = isset( $row['label'] ) ? sanitize_text_field( wp_unslash( $row['label'] ) ) : '';
			if ( '' === $label ) {
				continue;
			}
			$apr     = isset( $row['apr'] ) ? (float) $row['apr'] : 0.0;
			$apr     = max( 0.0, min( 100.0, $apr ) );
			$enabled = ! empty( $row['enabled'] ) ? '1' : '0';

			$providers[] = array(
				'label'   => $label,
				'apr'     => $apr,
				'enabled' => $enabled,
			);
		}

		$existing['jarelmaks_providers']  = $providers;
		$existing['jarelmaks_disclaimer'] = isset( $_POST['jarelmaks_disclaimer'] )
			? sanitize_textarea_field( wp_unslash( $_POST['jarelmaks_disclaimer'] ) )
			: '';
	}

	update_option( 'sawerna_page_options', $existing );

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'    => 'sawerna-fabric-options',
				'tab'     => $active_tab,
				'updated' => '1',
			),
			admin_url( 'admin.php' )
		)
	);
	exit;
}
add_action( 'admin_init', 'sawerna_fabric_handle_admin_save' );

/**
 * Default providers used when none are stored yet.
 *
 * @return array<int, array{label: string, apr: float, enabled: string}>
 */
function sawerna_jarelmaks_default_providers(): array {
	return array(
		array(
			'label'   => 'Inbank – 0% intress',
			'apr'     => 0.0,
			'enabled' => '1',
		),
		array(
			'label'   => 'ESTO – ~24% aastaintress',
			'apr'     => 24.0,
			'enabled' => '1',
		),
	);
}

/**
 * Get the active (enabled) järelmaks providers. Falls back to defaults.
 *
 * @return array<int, array{label: string, apr: float}>
 */
function sawerna_get_jarelmaks_providers(): array {
	$options   = (array) get_option( 'sawerna_page_options', array() );
	$providers = isset( $options['jarelmaks_providers'] ) && is_array( $options['jarelmaks_providers'] )
		? $options['jarelmaks_providers']
		: sawerna_jarelmaks_default_providers();

	$active = array();
	foreach ( $providers as $p ) {
		if ( empty( $p['label'] ) ) {
			continue;
		}
		if ( isset( $p['enabled'] ) && '0' === (string) $p['enabled'] ) {
			continue;
		}
		$active[] = array(
			'label' => (string) $p['label'],
			'apr'   => (float) $p['apr'],
		);
	}

	return ! empty( $active ) ? $active : sawerna_jarelmaks_default_providers();
}

/**
 * Default fabric card copy, used when an option has never been saved.
 *
 * @return array{title: string, text: string, link_label: string}
 */
function sawerna_fabric_banner_defaults(): array {
	return array(
		'title'      => __( 'Rohkem kangaid ja värvitoone', 'sawerna' ),
		'text'       => __( 'Siin näed vaid osa kangavalikust.', 'sawerna' ),
		'link_label' => __( 'Küsi teisi kangaid', 'sawerna' ),
	);
}

/**
 * Default link target: the contact page (slug "kontakt"), translated via WPML when available.
 *
 * @return string Permalink or empty string when no contact page exists.
 */
function sawerna_fabric_banner_default_link_url(): string {
	$page = get_page_by_path( 'kontakt' );
	if ( ! $page instanceof WP_Post ) {
		return '';
	}
	$page_id = (int) apply_filters( 'wpml_object_id', $page->ID, 'page', true );
	$url     = get_permalink( $page_id );
	return $url ? (string) $url : '';
}

/**
 * Default card image shipped with the theme.
 *
 * @return array{src: string, srcset: string, width: int, height: int}
 */
function sawerna_fabric_banner_default_image(): array {
	$base = get_template_directory_uri() . '/assets/images/';
	return array(
		'src'    => $base . 'fabric-samples-600.webp',
		'srcset' => $base . 'fabric-samples-300.webp 300w, ' . $base . 'fabric-samples-600.webp 600w',
		'width'  => 600,
		'height' => 429,
	);
}

/**
 * Resolve everything the fabric card needs for one product.
 *
 * Returns null when the card is disabled for the product or has no title and no text.
 * Option keys that were never saved fall back to defaults; a saved empty link URL or
 * label hides the link.
 *
 * @param int $product_id Product ID.
 * @return array{title: string, text: string, link_label: string, link_url: string, image_id: int}|null
 */
function sawerna_get_fabric_banner_data( int $product_id ): ?array {
	if ( '1' !== (string) get_post_meta( $product_id, '_sawerna_fabric_banner_enabled', true ) ) {
		return null;
	}

	$options  = (array) get_option( 'sawerna_page_options', array() );
	$defaults = sawerna_fabric_banner_defaults();

	$title      = array_key_exists( 'fabric_banner_title', $options ) ? (string) $options['fabric_banner_title'] : $defaults['title'];
	$text       = array_key_exists( 'fabric_banner_text', $options ) ? (string) $options['fabric_banner_text'] : $defaults['text'];
	$link_label = array_key_exists( 'fabric_banner_link_label', $options ) ? (string) $options['fabric_banner_link_label'] : $defaults['link_label'];
	$link_url   = array_key_exists( 'fabric_banner_link_url', $options ) ? (string) $options['fabric_banner_link_url'] : sawerna_fabric_banner_default_link_url();
	$image_id   = isset( $options['fabric_banner_image_id'] ) ? absint( $options['fabric_banner_image_id'] ) : 0;

	$product_text = trim( (string) get_post_meta( $product_id, '_sawerna_fabric_banner_text', true ) );
	if ( '' !== $product_text ) {
		$text = $product_text;
	}

	$title = trim( $title );
	$text  = trim( $text );
	if ( '' === $title && '' === $text ) {
		return null;
	}

	if ( '' === trim( $link_label ) || '' === trim( $link_url ) ) {
		$link_label = '';
		$link_url   = '';
	}

	return array(
		'title'      => $title,
		'text'       => $text,
		'link_label' => trim( $link_label ),
		'link_url'   => $link_url,
		'image_id'   => $image_id,
	);
}

/**
 * Load the media picker on the Single product options page.
 *
 * @param string $hook_suffix Current admin page hook.
 */
function sawerna_fabric_admin_enqueue( string $hook_suffix ): void {
	if ( false === strpos( $hook_suffix, 'sawerna-fabric-options' ) ) {
		return;
	}
	wp_enqueue_media();
	wp_register_script( 'sawerna-fabric-options-admin', false, array( 'jquery', 'media-editor' ), '1.0.0', true );
	wp_enqueue_script( 'sawerna-fabric-options-admin' );
	wp_add_inline_script(
		'sawerna-fabric-options-admin',
		"jQuery(function($){var frame;var \$wrap=$('[data-fabric-image-field]');if(!\$wrap.length){return;}var \$id=\$wrap.find('input[type=hidden]');var \$img=\$wrap.find('img');var \$remove=\$wrap.find('[data-fabric-image-remove]');var def=\$img.attr('data-default-src');\$wrap.on('click','[data-fabric-image-select]',function(e){e.preventDefault();if(!frame){frame=wp.media({title:\$(this).text(),library:{type:'image'},multiple:false});frame.on('select',function(){var a=frame.state().get('selection').first().toJSON();var u=(a.sizes&&a.sizes.medium)?a.sizes.medium.url:a.url;\$id.val(a.id);\$img.attr('src',u);\$remove.prop('hidden',false);});}frame.open();});\$remove.on('click',function(e){e.preventDefault();\$id.val('0');\$img.attr('src',def);\$remove.prop('hidden',true);});});"
	);
}
add_action( 'admin_enqueue_scripts', 'sawerna_fabric_admin_enqueue' );

/**
 * Render the Single product options admin page.
 */
function sawerna_fabric_render_admin_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$options             = (array) get_option( 'sawerna_page_options', array() );
	$fabric_defaults     = sawerna_fabric_banner_defaults();
	$fabric_banner_title = array_key_exists( 'fabric_banner_title', $options ) ? (string) $options['fabric_banner_title'] : $fabric_defaults['title'];
	$fabric_banner_text  = array_key_exists( 'fabric_banner_text', $options ) ? (string) $options['fabric_banner_text'] : $fabric_defaults['text'];
	$fabric_link_label   = array_key_exists( 'fabric_banner_link_label', $options ) ? (string) $options['fabric_banner_link_label'] : $fabric_defaults['link_label'];
	$fabric_link_url     = array_key_exists( 'fabric_banner_link_url', $options ) ? (string) $options['fabric_banner_link_url'] : sawerna_fabric_banner_default_link_url();
	$fabric_image_id     = isset( $options['fabric_banner_image_id'] ) ? absint( $options['fabric_banner_image_id'] ) : 0;
	$fabric_default_img  = sawerna_fabric_banner_default_image();
	$fabric_image_src    = $fabric_image_id ? (string) wp_get_attachment_image_url( $fabric_image_id, 'medium' ) : '';
	if ( '' === $fabric_image_src ) {
		$fabric_image_id  = 0;
		$fabric_image_src = $fabric_default_img['src'];
	}

	$stored_providers     = isset( $options['jarelmaks_providers'] ) && is_array( $options['jarelmaks_providers'] )
		? $options['jarelmaks_providers']
		: sawerna_jarelmaks_default_providers();
	$jarelmaks_disclaimer = isset( $options['jarelmaks_disclaimer'] ) ? (string) $options['jarelmaks_disclaimer'] : '';

	// Append one blank row so the user can add a new provider.
	$display_providers = array_merge(
		$stored_providers,
		array(
			array(
				'label'   => '',
				'apr'     => '',
				'enabled' => '1',
			),
		)
	);

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'kangavalik';

	$kangavalik_tab_url = add_query_arg(
		array(
			'page' => 'sawerna-fabric-options',
			'tab'  => 'kangavalik',
		),
		admin_url( 'admin.php' )
	);
	$jarelmaks_tab_url  = add_query_arg(
		array(
			'page' => 'sawerna-fabric-options',
			'tab'  => 'jarelmaks',
		),
		admin_url( 'admin.php' )
	);
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Single product options', 'sawerna' ); ?></h1>

		<?php if ( isset( $_GET['updated'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div class="notice notice-success is-dismissible">
				<p><?php esc_html_e( 'Settings saved.', 'sawerna' ); ?></p>
			</div>
		<?php endif; ?>

		<nav class="nav-tab-wrapper">
			<a href="<?php echo esc_url( $kangavalik_tab_url ); ?>"
			   class="nav-tab<?php echo 'kangavalik' === $active_tab ? ' nav-tab-active' : ''; ?>">
				<?php esc_html_e( 'Kangavalik', 'sawerna' ); ?>
			</a>
			<a href="<?php echo esc_url( $jarelmaks_tab_url ); ?>"
			   class="nav-tab<?php echo 'jarelmaks' === $active_tab ? ' nav-tab-active' : ''; ?>">
				<?php esc_html_e( 'Järelmaks', 'sawerna' ); ?>
			</a>
		</nav>

		<form method="post" action="">
			<?php wp_nonce_field( 'sawerna_single_product_options_save', 'sawerna_single_product_options_nonce' ); ?>
			<input type="hidden" name="active_tab" value="<?php echo esc_attr( $active_tab ); ?>">

			<?php if ( 'kangavalik' === $active_tab ) : ?>

				<p class="description" style="margin-top:20px;max-width:760px;">
					<?php esc_html_e( 'Card shown under the "Vali kattekangas" row on products where "Kangavalik banner" is checked (product edit → General). Title and text are required for the card to appear; empty link URL or label hides the link.', 'sawerna' ); ?>
				</p>

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label for="fabric_banner_title"><?php esc_html_e( 'Title', 'sawerna' ); ?></label>
						</th>
						<td>
							<input type="text" name="fabric_banner_title" id="fabric_banner_title" class="regular-text" value="<?php echo esc_attr( $fabric_banner_title ); ?>">
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="fabric_banner_text"><?php esc_html_e( 'Text', 'sawerna' ); ?></label>
						</th>
						<td>
							<textarea
								name="fabric_banner_text"
								id="fabric_banner_text"
								rows="3"
								class="large-text"
							><?php echo esc_textarea( $fabric_banner_text ); ?></textarea>
							<p class="description">
								<?php esc_html_e( 'Default text. A product can override it with its own "Kangavaliku tekst" (product edit → General).', 'sawerna' ); ?>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="fabric_banner_link_label"><?php esc_html_e( 'Link label', 'sawerna' ); ?></label>
						</th>
						<td>
							<input type="text" name="fabric_banner_link_label" id="fabric_banner_link_label" class="regular-text" value="<?php echo esc_attr( $fabric_link_label ); ?>">
							<p class="description"><?php esc_html_e( 'An arrow (→) is added automatically.', 'sawerna' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="fabric_banner_link_url"><?php esc_html_e( 'Link URL', 'sawerna' ); ?></label>
						</th>
						<td>
							<input type="url" name="fabric_banner_link_url" id="fabric_banner_link_url" class="regular-text code" value="<?php echo esc_attr( $fabric_link_url ); ?>" placeholder="https://">
							<p class="description"><?php esc_html_e( 'Defaults to the contact page. Leave empty to hide the link.', 'sawerna' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Image', 'sawerna' ); ?></th>
						<td data-fabric-image-field="1">
							<input type="hidden" name="fabric_banner_image_id" value="<?php echo esc_attr( (string) $fabric_image_id ); ?>">
							<img
								src="<?php echo esc_url( $fabric_image_src ); ?>"
								data-default-src="<?php echo esc_url( $fabric_default_img['src'] ); ?>"
								alt=""
								style="display:block;width:200px;height:auto;margin-bottom:8px;border-radius:8px;background:#f2efe9;"
							>
							<button type="button" class="button" data-fabric-image-select="1"><?php esc_html_e( 'Select image', 'sawerna' ); ?></button>
							<button type="button" class="button-link button-link-delete" data-fabric-image-remove="1"<?php echo $fabric_image_id ? '' : ' hidden'; ?>><?php esc_html_e( 'Use default image', 'sawerna' ); ?></button>
							<p class="description"><?php esc_html_e( 'Decorative image on the left of the card. Default: folded fabric samples from the theme.', 'sawerna' ); ?></p>
						</td>
					</tr>
				</table>

			<?php elseif ( 'jarelmaks' === $active_tab ) : ?>

				<p class="description" style="margin-top:20px;">
					<?php esc_html_e( 'Define the payment providers shown in the järelmaks calculator. Leave the name empty to remove a provider. APR is the annual interest rate in percent (0 = interest-free).', 'sawerna' ); ?>
				</p>

				<table class="wp-list-table widefat fixed striped" style="margin-top:16px;max-width:680px;">
					<thead>
						<tr>
							<th style="width:40px;"><?php esc_html_e( 'On', 'sawerna' ); ?></th>
							<th><?php esc_html_e( 'Display name', 'sawerna' ); ?></th>
							<th style="width:130px;"><?php esc_html_e( 'APR %', 'sawerna' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $display_providers as $i => $provider ) : ?>
							<tr>
								<td style="text-align:center;">
									<input
										type="checkbox"
										name="jarelmaks_providers[<?php echo esc_attr( (string) $i ); ?>][enabled]"
										value="1"
										<?php checked( ! isset( $provider['enabled'] ) || '0' !== (string) $provider['enabled'] ); ?>
									>
								</td>
								<td>
									<input
										type="text"
										name="jarelmaks_providers[<?php echo esc_attr( (string) $i ); ?>][label]"
										value="<?php echo esc_attr( isset( $provider['label'] ) ? (string) $provider['label'] : '' ); ?>"
										class="widefat"
										placeholder="<?php esc_attr_e( 'e.g. Inbank – 0% intress', 'sawerna' ); ?>"
									>
								</td>
								<td>
									<input
										type="number"
										name="jarelmaks_providers[<?php echo esc_attr( (string) $i ); ?>][apr]"
										value="<?php echo esc_attr( isset( $provider['apr'] ) ? (string) $provider['apr'] : '' ); ?>"
										min="0"
										max="100"
										step="0.01"
										class="small-text"
										placeholder="0"
									>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

				<table class="form-table" role="presentation" style="margin-top:24px;max-width:760px;">
					<tr>
						<th scope="row">
							<label for="jarelmaks_disclaimer"><?php esc_html_e( 'Disclaimer text', 'sawerna' ); ?></label>
						</th>
						<td>
							<textarea
								name="jarelmaks_disclaimer"
								id="jarelmaks_disclaimer"
								rows="3"
								class="large-text"
								placeholder="<?php esc_attr_e( 'Arvutus on ligikaudne. Täpne pakkumine sõltub laenuandja tingimustest ja krediidiotsusest.', 'sawerna' ); ?>"
							><?php echo esc_textarea( $jarelmaks_disclaimer ); ?></textarea>
							<p class="description"><?php esc_html_e( 'Shown below the monthly payment result. Leave empty to use the default text.', 'sawerna' ); ?></p>
						</td>
					</tr>
				</table>

			<?php endif; ?>

			<?php submit_button( __( 'Save', 'sawerna' ) ); ?>
		</form>
	</div>
	<?php
}

/**
 * Get a single value from sawerna_page_options.
 *
 * @param string $key           Option key.
 * @param string $default_value Default value.
 * @return string
 */
if ( ! function_exists( 'sawerna_get_page_option' ) ) {
	function sawerna_get_page_option( string $key, string $default_value = '' ): string {
		$options = (array) get_option( 'sawerna_page_options', array() );
		return isset( $options[ $key ] ) ? (string) $options[ $key ] : $default_value;
	}
}
