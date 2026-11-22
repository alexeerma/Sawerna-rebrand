<?php
/**
 * Sawerna maintenance page (WordPress drop-in).
 *
 * WordPress shows wp-content/maintenance.php instead of its generic
 * "Briefly unavailable for scheduled maintenance" page while core, plugin or
 * theme updates run. It is loaded very early (before plugins, the theme and
 * the database), so it is fully self-contained: no WordPress functions,
 * inline styles and inline logo only.
 *
 * Source: themes/sawerna/maintenance/maintenance.php
 * Install: copy to wp-content/maintenance.php (see README.md, "Maintenance page").
 *
 * @package Sawerna
 */

// phpcs:disable WordPress.Security.ValidatedSanitizedInput, WordPress.Security.EscapeOutput, WordPress.WP.AlternativeFunctions, WordPress.DateTime

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Temporary unavailability: 503 + Retry-After, so search engines keep the indexed pages.
$sawerna_protocol = isset( $_SERVER['SERVER_PROTOCOL'] ) && in_array( $_SERVER['SERVER_PROTOCOL'], array( 'HTTP/1.0', 'HTTP/1.1', 'HTTP/2', 'HTTP/2.0', 'HTTP/3' ), true ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1';
if ( ! headers_sent() ) {
	header( $sawerna_protocol . ' 503 Service Unavailable', true, 503 );
	header( 'Retry-After: 120' );
	header( 'Content-Type: text/html; charset=utf-8' );
	header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );
	header( 'X-Robots-Tag: noindex' );
}

// URL path to the theme fonts, derived from the content directory (also works in a subdirectory install).
$sawerna_fonts = '';
if ( defined( 'WP_CONTENT_DIR' ) && ! empty( $_SERVER['DOCUMENT_ROOT'] ) ) {
	$sawerna_root    = rtrim( str_replace( '\\', '/', (string) realpath( $_SERVER['DOCUMENT_ROOT'] ) ), '/' );
	$sawerna_content = str_replace( '\\', '/', (string) realpath( WP_CONTENT_DIR ) );
	if ( '' !== $sawerna_root && 0 === strpos( $sawerna_content, $sawerna_root ) ) {
		$sawerna_fonts = preg_replace( '#[^A-Za-z0-9_./-]#', '', substr( $sawerna_content, strlen( $sawerna_root ) ) . '/themes/sawerna/assets/fonts/' );
	}
}
?><!DOCTYPE html>
<html lang="et">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<meta http-equiv="refresh" content="60">
<title>Sawerna · Hooldustööd</title>
<style>
<?php if ( '' !== $sawerna_fonts ) : ?>
@font-face { font-family: 'Inter'; font-weight: 400; font-display: swap; src: url('<?php echo $sawerna_fonts; ?>Inter_18pt-Regular.woff2') format('woff2'); }
@font-face { font-family: 'Inter'; font-weight: 500; font-display: swap; src: url('<?php echo $sawerna_fonts; ?>Inter_18pt-Medium.woff2') format('woff2'); }
@font-face { font-family: 'Inter'; font-weight: 600; font-display: swap; src: url('<?php echo $sawerna_fonts; ?>Inter_18pt-SemiBold.woff2') format('woff2'); }
<?php endif; ?>
:root { --text: #111; --heading: #2f2f2a; --muted: #706a63; --accent: #a8a06f; --dark: #38371b; --bg: #f2efe9; --line: #e4ddd2; }
* { box-sizing: border-box; }
html, body { height: 100%; }
body { margin: 0; display: flex; flex-direction: column; min-height: 100%; background: var(--bg); color: var(--text); font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", sans-serif; -webkit-font-smoothing: antialiased; }
.bar { height: 6px; background: var(--dark); }
main { flex: 1; display: flex; align-items: center; justify-content: center; padding: 48px 20px; }
.card { width: 100%; max-width: 620px; padding: 56px 48px 48px; background: #fff; text-align: center; }
.logo { display: block; width: 188px; max-width: 70%; margin: 0 auto 40px; color: #000; }
.logo svg { display: block; width: 100%; height: auto; fill: currentColor; }
.eyebrow { margin: 0 0 14px; color: var(--accent); font-size: 13px; font-weight: 600; letter-spacing: 0.14em; text-transform: uppercase; }
h1 { margin: 0 0 16px; color: var(--heading); font-size: clamp(26px, 4vw, 34px); font-weight: 600; line-height: 1.15; }
p { margin: 0; font-size: 16px; line-height: 1.6; color: var(--muted); }
.lead { max-width: 440px; margin: 0 auto; }
.status { display: inline-flex; align-items: center; gap: 10px; margin: 28px 0 32px; font-size: 14px; color: var(--muted); }
.dot { width: 8px; height: 8px; border-radius: 50%; background: var(--accent); animation: pulse 1.6s ease-in-out infinite; }
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.3; } }
.contact { padding-top: 28px; border-top: 1px solid var(--line); display: flex; flex-wrap: wrap; justify-content: center; gap: 12px; }
.btn { display: inline-flex; align-items: center; justify-content: center; min-height: 44px; padding: 0 22px; border-radius: 999px; font-size: 15px; font-weight: 600; text-decoration: none; transition: transform 0.2s ease, background 0.2s ease; }
.btn-dark { background: var(--dark); color: #fff; }
.btn-dark:hover { background: #24230f; transform: translateY(-1px); }
.btn-line { border: 1px solid var(--dark); color: var(--dark); }
.btn-line:hover { background: var(--dark); color: #fff; transform: translateY(-1px); }
.btn:focus-visible { outline: 2px solid var(--accent); outline-offset: 3px; }
.en { margin-top: 28px; font-size: 13px; }
footer { padding: 20px; text-align: center; font-size: 13px; color: var(--muted); }
@media (max-width: 520px) { .card { padding: 40px 24px 32px; } .btn { width: 100%; } }
@media (prefers-reduced-motion: reduce) { .dot { animation: none; } .btn { transition: none; } }
</style>
</head>
<body>
<div class="bar" aria-hidden="true"></div>
<main>
	<div class="card">
		<span class="logo"><svg role="img" aria-label="Sawerna" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 187 27.84">
  <defs>
    <style>
      .cls-1 {
        fill-rule: evenodd;
      }
    </style>
  </defs>
  <g>
    <path class="cls-1" d="M36.01,15.99l-4.06-9.5-3.86,9.5h7.92ZM118.01,1.69v10.95h1.38c.83,0,1.74-.09,2.51-.43.56-.25,1.02-.61,1.36-1.13.32-.49.49-1.05.58-1.62.1-.63.12-1.27.12-1.9,0-.49-.01-.98-.05-1.46-.03-.42-.08-.83-.16-1.24-.12-.64-.33-1.3-.71-1.85-.35-.49-.8-.84-1.36-1.06-.68-.27-1.46-.35-2.19-.35-.5,0-.99.07-1.49.1ZM173.72,15.99l-4.09-9.5-3.88,9.5h7.97ZM0,26.83v-8.18h.7v.07c.08.38.17.75.28,1.12.14.51.31,1.01.52,1.5.49,1.18,1.16,2.3,2.06,3.2.85.85,1.82,1.53,2.98,1.87.42.12.86.2,1.29.23.38.03.76.03,1.14.03,1.88,0,3.68-.55,4.9-2.06.43-.53.75-1.15.96-1.8.17-.52.27-1.09.27-1.63,0-1.68-.81-2.76-2.23-3.56-.35-.2-.72-.37-1.09-.52-.4-.17-.81-.31-1.23-.45-.88-.29-1.78-.54-2.67-.82-.91-.28-1.82-.58-2.69-.97-.84-.38-1.65-.84-2.36-1.44-1.68-1.43-2.31-3.32-2.31-5.49,0-2.01.96-3.87,2.42-5.21C4.64,1.19,6.87.43,9.15.43c.7,0,1.39.07,2.07.21.54.11,1.06.27,1.57.46.21.08.42.16.63.25.18.07.37.15.55.21.28.1.6.2.9.2.6,0,.71-.16.91-.7l.02-.06h.72v7.02h-.62l-.02-.06c-.28-.83-.55-1.66-.89-2.47-.32-.77-.71-1.53-1.22-2.2-1.18-1.55-2.74-2.18-4.67-2.18-1.53,0-3.09.41-4.27,1.43-.86.75-1.48,1.82-1.48,2.98,0,1.65.95,2.66,2.32,3.42.37.2.75.38,1.14.54.42.17.85.33,1.28.48.92.32,1.86.59,2.79.89.94.3,1.89.63,2.8,1.03.86.39,1.71.85,2.45,1.44,1.66,1.34,2.41,3.07,2.41,5.19,0,2.38-1.01,4.59-2.75,6.21-.9.84-1.96,1.48-3.11,1.92-1.27.49-2.63.71-3.99.71-.73,0-1.47-.09-2.19-.22-.69-.12-1.36-.29-2.03-.47-.56-.15-1.11-.32-1.66-.47-.2-.06-.41-.11-.62-.15-.14-.03-.29-.06-.43-.06-.22,0-.46.02-.64.15-.18.14-.29.41-.37.62l-.02.06h-.72ZM23.19,26.14L33.8,0h1.08l11.22,26.14h3.09v.69h-12.15v-.69h3.27l-4.05-9.47h-8.43l-3.88,9.47h3.12v.69h-6.95v-.69h3.06ZM53.52,1.7l6.28,18.71,3.65-10.55-2.73-8.15h-1.94v-.69h10.76v.69h-3.25l6.31,18.74,6.45-18.74h-3.01v-.69h6.69v.69h-2.89l-9.09,26.14h-1.16l-5.77-16.98-5.91,16.98h-1.16L47.99,1.7h-2.96v-.69h11.04v.69h-2.55ZM92.33,1.7v10.94h1.91c1.41,0,2.72-.35,3.68-1.44.43-.49.74-1.07.98-1.67.28-.7.48-1.45.63-2.19l.02-.07h.63v11.38h-.63l-.02-.07c-.15-.68-.34-1.36-.58-2.01-.23-.59-.52-1.17-.9-1.68-.86-1.12-2.02-1.58-3.41-1.58h-2.3v12.82h5.76c4.42,0,7-3.28,7.93-7.31l.02-.07h.63v8.07h-22.96v-.69h3.28V1.7h-3.28v-.69h21.5v6.34h-.62l-.02-.06c-.39-1.23-.94-2.4-1.77-3.39-1.27-1.49-2.93-2.2-4.88-2.2h-5.58ZM112.63,26.14V1.7h-3.3v-.69h5.5c1.98,0,3.97-.11,5.95-.11,3.94,0,8.87,1.47,8.87,6.25,0,3.86-3.78,5.39-7.06,5.67v.02c.53.09,1.06.19,1.59.29.5.1.99.2,1.48.34.84.22,1.69.52,2.41,1,.71.47,1.24,1.07,1.62,1.84.47.96.67,2.07.75,3.12.05.68.1,1.35.15,2.03.04.53.08,1.07.13,1.6.04.41.08.82.14,1.23.04.3.09.6.17.89.09.34.23.73.51.96.26.2.6.23.92.23.43,0,.8-.15,1.14-.4.3-.22.54-.5.8-.76l.06-.06.49.45-.07.07c-.47.48-.89.92-1.5,1.23-.35.18-.73.29-1.12.36-.54.1-1.1.13-1.64.13-1.92,0-3.79-.5-4.79-2.27-.36-.64-.56-1.37-.68-2.09-.11-.68-.15-1.37-.18-2.06-.04-.95-.08-1.91-.2-2.85-.1-.82-.26-1.68-.6-2.44-.3-.69-.73-1.28-1.37-1.69-.75-.49-1.67-.64-2.55-.64h-2.27v12.82h3.3v.69h-11.98v-.69h3.3ZM155.83,18.05V1.7h-3.3v-.69h7.35v.69h-3.29v25.13h-1.11l-16.05-22.99v22.3h3.3v.69h-7.35v-.69h3.3V1.7h-3.3v-.69h8.59l11.86,17.04ZM160.82,26.14L171.5,0h1.09l11.29,26.14h3.11v.69h-12.23v-.69h3.29l-4.08-9.47h-8.48l-3.9,9.47h3.15v.69h-6.99v-.69h3.08Z"></path>
  </g>
</svg></span>
		<p class="eyebrow">Hooldustööd</p>
		<h1>Teeme e-poes väikest uuendust</h1>
		<p class="lead">Sawerna e-pood on paari minuti pärast taas avatud. Vabandame ebamugavuste pärast.</p>
		<div class="status" role="status"><span class="dot" aria-hidden="true"></span>Leht värskendub automaatselt</div>
		<div class="contact">
			<a class="btn btn-dark" href="tel:+37256878750">Helista +372 5687 8750</a>
			<a class="btn btn-line" href="mailto:info@sawerna.ee">info@sawerna.ee</a>
		</div>
		<p class="en" lang="en">We are performing a short update. Please check back in a few minutes.</p>
	</div>
</main>
<footer>© <?php echo (int) gmdate( 'Y' ); ?> Sawerna OÜ</footer>
</body>
</html>
