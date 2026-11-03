<?php
/**
 * Single product options admin page.
 *
 * Tabs:
 *  - Kangavalik  : global fabric banner text (sawerna_page_options['fabric_banner_text'])
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
		$existing['fabric_banner_text'] = isset( $_POST['fabric_banner_text'] )
			? sanitize_textarea_field( wp_unslash( $_POST['fabric_banner_text'] ) )
			: '';
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

	return $active ?: sawerna_jarelmaks_default_providers();
}

/**
 * Render the Single product options admin page.
 */
function sawerna_fabric_render_admin_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$options            = (array) get_option( 'sawerna_page_options', array() );
	$fabric_banner_text = isset( $options['fabric_banner_text'] ) ? (string) $options['fabric_banner_text'] : '';

	$stored_providers     = isset( $options['jarelmaks_providers'] ) && is_array( $options['jarelmaks_providers'] )
		? $options['jarelmaks_providers']
		: sawerna_jarelmaks_default_providers();
	$jarelmaks_disclaimer = isset( $options['jarelmaks_disclaimer'] ) ? (string) $options['jarelmaks_disclaimer'] : '';

	// Append one blank row so the user can add a new provider.
	$display_providers = array_merge( $stored_providers, array( array( 'label' => '', 'apr' => '', 'enabled' => '1' ) ) );

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'kangavalik';
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Single product options', 'sawerna' ); ?></h1>

		<?php if ( isset( $_GET['updated'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div class="notice notice-success is-dismissible">
				<p><?php esc_html_e( 'Settings saved.', 'sawerna' ); ?></p>
			</div>
		<?php endif; ?>

		<nav class="nav-tab-wrapper">
			<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'sawerna-fabric-options', 'tab' => 'kangavalik' ), admin_url( 'admin.php' ) ) ); ?>"
			   class="nav-tab<?php echo 'kangavalik' === $active_tab ? ' nav-tab-active' : ''; ?>">
				<?php esc_html_e( 'Kangavalik', 'sawerna' ); ?>
			</a>
			<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'sawerna-fabric-options', 'tab' => 'jarelmaks' ), admin_url( 'admin.php' ) ) ); ?>"
			   class="nav-tab<?php echo 'jarelmaks' === $active_tab ? ' nav-tab-active' : ''; ?>">
				<?php esc_html_e( 'Järelmaks', 'sawerna' ); ?>
			</a>
		</nav>

		<form method="post" action="">
			<?php wp_nonce_field( 'sawerna_single_product_options_save', 'sawerna_single_product_options_nonce' ); ?>
			<input type="hidden" name="active_tab" value="<?php echo esc_attr( $active_tab ); ?>">

			<?php if ( 'kangavalik' === $active_tab ) : ?>

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label for="fabric_banner_text"><?php esc_html_e( 'Kangavalik banner text', 'sawerna' ); ?></label>
						</th>
						<td>
							<textarea
								name="fabric_banner_text"
								id="fabric_banner_text"
								rows="5"
								class="large-text"
							><?php echo esc_textarea( $fabric_banner_text ); ?></textarea>
							<p class="description">
								<?php esc_html_e( 'Shown inside the Kangas selector drawer on products that have the banner enabled.', 'sawerna' ); ?>
							</p>
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
 * @param string $key     Option key.
 * @param string $default Default value.
 * @return string
 */
if ( ! function_exists( 'sawerna_get_page_option' ) ) {
	function sawerna_get_page_option( string $key, string $default = '' ): string {
		$options = (array) get_option( 'sawerna_page_options', array() );
		return isset( $options[ $key ] ) ? (string) $options[ $key ] : $default;
	}
}
