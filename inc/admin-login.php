<?php
/**
 * WP admin login page branding and styles.
 *
 * @package Sawerna
 */

/**
 * Inject styles on the WP login page.
 */
function sawerna_admin_login_styles(): void {
	$logo_url = get_template_directory_uri() . '/assets/SVG/sawerna_logo.svg';
	?>
	<style>
		body.login {
			background: #f9f9f9;
		}

		body.login div#login {
			padding-top: 48px;
		}

		/* Logo */
		#login h1 a {
			background-image: url('<?php echo esc_url( $logo_url ); ?>');
			background-repeat: no-repeat;
			background-size: contain;
			background-position: center center;
			width: 200px;
			height: 30px;
			display: block;
			text-indent: -9999px;
			overflow: hidden;
			margin: 0 auto 28px;
		}

		/* Card */
		#loginform,
		#lostpasswordform {
			border: 1px solid #e5e5e5;
			border-radius: 0;
			box-shadow: none;
			padding: 32px 36px 28px;
			background: #fff;
		}

		/* Labels */
		.login label {
			color: #202020;
			font-size: 13px;
			font-weight: 700;
		}

		/* Inputs */
		.login input[type="text"],
		.login input[type="password"],
		.login input[type="email"] {
			border: 1px solid #dddddd;
			border-radius: 0;
			box-shadow: none;
			min-height: 44px;
			padding: 0 12px;
			font-size: 14px;
			color: #202020;
			background: #fff;
		}

		.login input[type="text"]:focus,
		.login input[type="password"]:focus,
		.login input[type="email"]:focus {
			border-color: #111;
			box-shadow: none;
			outline: none;
		}

		/* Submit button */
		.login .button-primary,
		.login #wp-submit {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			min-height: 44px;
			padding: 0 24px;
			background: #111;
			border: 1px solid #111;
			border-radius: 0;
			box-shadow: none;
			text-shadow: none;
			color: #fff;
			font-size: 13px;
			font-weight: 700;
			letter-spacing: 0.04em;
			text-transform: uppercase;
			transition: background 0.15s, color 0.15s;
		}

		.login .button-primary:hover,
		.login #wp-submit:hover,
		.login .button-primary:focus,
		.login #wp-submit:focus {
			background: #fff;
			border-color: #111;
			color: #111;
			box-shadow: none;
		}

		/* Nav links */
		.login #nav,
		.login #backtoblog {
			text-align: center;
		}

		.login #nav a,
		.login #backtoblog a {
			color: #777;
			text-decoration: underline;
			text-underline-offset: 3px;
			text-decoration-thickness: 1px;
		}

		.login #nav a:hover,
		.login #backtoblog a:hover {
			color: #111;
		}

		/* Error / info notices */
		#login_error,
		.login .message,
		.login .success {
			border-left-color: #111;
			box-shadow: none;
		}

		/* Remember me */
		.login .forgetmenot label {
			font-weight: 400;
			color: #5f5f5f;
		}
	</style>
	<?php
}
add_action( 'login_enqueue_scripts', 'sawerna_admin_login_styles' );

/**
 * Point the logo link to the site homepage.
 *
 * @return string
 */
function sawerna_admin_login_logo_url(): string {
	return home_url();
}
add_filter( 'login_headerurl', 'sawerna_admin_login_logo_url' );

/**
 * Replace the logo title/alt with the site name.
 *
 * @return string
 */
function sawerna_admin_login_logo_title(): string {
	return get_bloginfo( 'name' );
}
add_filter( 'login_headertext', 'sawerna_admin_login_logo_title' );
