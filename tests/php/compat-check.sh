#!/usr/bin/env bash
# PHP compatibility smoke test for the Sawerna theme + sawerna-core plugin.
# Run before upgrading PHP, WordPress or WooCommerce.
#
# 1) php -l on every first-party PHP file, with every PHP binary given in PHP_BINS
#    (default: `php` on PATH plus Local's PHP 8.4 if present).
# 2) PHPCompatibilityWP scan (testVersion from MIN_PHP, default 7.4-).
#
# One-time tool install (outside the theme, any folder, e.g. ~/.phpcompat):
#   mkdir -p ~/.phpcompat && cd ~/.phpcompat
#   composer config -n minimum-stability dev && composer config -n prefer-stable true
#   composer config -n allow-plugins.dealerdirect/phpcodesniffer-composer-installer true
#   composer require --dev -n squizlabs/php_codesniffer dealerdirect/phpcodesniffer-composer-installer \
#     "phpcompatibility/php-compatibility:^10.0@dev" "phpcompatibility/phpcompatibility-wp:^3.0@dev"
# (PHPCompatibility 9.3.x stable only knows up to PHP 7.4; 10.x is needed for PHP 8.x sniffs.)
#
# Usage:
#   PHPCS=~/.phpcompat/vendor/bin/phpcs ./compat-check.sh
#   MIN_PHP=8.2 PHP_BINS="php /path/to/php8.5" ./compat-check.sh
#
# Exit code is non-zero if any lint or scan fails.

set -u

HERE="$(cd "$(dirname "$0")" && pwd)"
THEME_DIR="$(cd "$HERE/../.." && pwd)"
CORE_DIR="$(cd "$THEME_DIR/../../plugins/sawerna-core" 2>/dev/null && pwd || true)"
MIN_PHP="${MIN_PHP:-7.4}"
PHPCS="${PHPCS:-$HOME/.phpcompat/vendor/bin/phpcs}"

if [ -z "${PHP_BINS:-}" ]; then
	PHP_BINS="php"
	LOCAL84="$HOME/Library/Application Support/Local/lightning-services/php-8.4.4+2/bin/darwin/bin/php"
	[ -x "$LOCAL84" ] && PHP_BINS="$PHP_BINS|$LOCAL84"
else
	PHP_BINS="$(echo "$PHP_BINS" | tr ' ' '|')"
fi

# Dead forks (not loaded by functions.php); still linted so they never break autoloaders.
FILES=()
while IFS= read -r -d '' f; do FILES+=("$f"); done < <(
	find "$THEME_DIR" ${CORE_DIR:+"$CORE_DIR"} -name '*.php' \
		-not -path '*/node_modules/*' -not -path '*/vendor/*' -not -path '*/tests/*' -print0
)

status=0
echo "Files: ${#FILES[@]} (theme: $THEME_DIR, core: ${CORE_DIR:-missing})"

IFS='|' read -r -a BINS <<< "$PHP_BINS"
for bin in "${BINS[@]}"; do
	if ! "$bin" -v >/dev/null 2>&1; then
		echo "SKIP lint: '$bin' not runnable"
		continue
	fi
	ver="$("$bin" -r 'echo PHP_VERSION;')"
	fails=0
	for f in "${FILES[@]}"; do
		out="$("$bin" -d error_reporting=-1 -d display_errors=1 -l "$f" 2>&1)"
		if ! echo "$out" | grep -q '^No syntax errors' || echo "$out" | grep -qi 'deprecated'; then
			echo "$out"
			fails=$((fails + 1))
		fi
	done
	echo "php -l ($ver): $fails problem file(s)"
	[ "$fails" -gt 0 ] && status=1
done

if [ -x "$PHPCS" ]; then
	echo "PHPCompatibilityWP, testVersion $MIN_PHP-"
	"$PHPCS" -p --standard=PHPCompatibilityWP --runtime-set testVersion "$MIN_PHP-" \
		--extensions=php --ignore='*/tests/*,*/vendor/*,*/node_modules/*' \
		"$THEME_DIR" ${CORE_DIR:+"$CORE_DIR"} || status=1
else
	echo "SKIP PHPCompatibility: phpcs not found at $PHPCS (see install notes in this file)"
	status=1
fi

[ "$status" -eq 0 ] && echo "OK" || echo "FAILED"
exit "$status"
