<?php
/**
 * Blog archive header.
 *
 * @package Sawerna
 */

$blog_archive_url   = sawerna_get_blog_archive_url();
$blog_archive_label = sawerna_get_blog_archive_label();
$blog_heading       = sawerna_get_blog_archive_heading();
$blog_categories    = sawerna_get_blog_archive_categories();
$current_category   = is_category() ? get_queried_object_id() : 0;
$all_active         = ! is_category();
$current_label      = __( 'Vaata kõiki', 'sawerna' );

if ( ! $all_active ) {
	foreach ( $blog_categories as $category ) {
		if ( (int) $current_category === (int) $category->term_id ) {
			$current_label = $category->name;
			break;
		}
	}
}
?>
<header class="sawerna-blog-archive__header">
	<nav class="sawerna-blog-archive__breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'sawerna' ); ?>">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Esileht', 'sawerna' ); ?></a>
		<span class="sawerna-blog-archive__breadcrumb-separator" aria-hidden="true">/</span>
		<a href="<?php echo esc_url( $blog_archive_url ); ?>"><?php echo esc_html( $blog_archive_label ); ?></a>
	</nav>

	<h1 class="sawerna-blog-archive__title"><?php echo esc_html( $blog_heading ); ?></h1>

	<?php if ( ! empty( $blog_categories ) ) : ?>
		<div class="sawerna-blog-archive__filters" aria-label="<?php esc_attr_e( 'Blog categories', 'sawerna' ); ?>">
			<a class="sawerna-blog-archive__filter<?php echo $all_active ? ' is-active' : ''; ?>" href="<?php echo esc_url( $blog_archive_url ); ?>">
				<?php esc_html_e( 'Vaata kõiki', 'sawerna' ); ?>
			</a>
			<?php foreach ( $blog_categories as $category ) : ?>
				<a
					class="sawerna-blog-archive__filter<?php echo (int) $current_category === (int) $category->term_id ? ' is-active' : ''; ?>"
					href="<?php echo esc_url( get_category_link( $category ) ); ?>"
				>
					<?php echo esc_html( $category->name ); ?>
				</a>
				<?php endforeach; ?>
		</div>

		<div class="sawerna-blog-archive__filters-mobile">
			<details class="sawerna-blog-archive__mobile-dropdown">
				<summary class="sawerna-blog-archive__mobile-summary">
					<span><?php echo esc_html( $current_label ); ?></span>
					<span class="sawerna-blog-archive__mobile-summary-icon" aria-hidden="true"></span>
				</summary>
				<div class="sawerna-blog-archive__mobile-panel" role="list">
					<a
						class="sawerna-blog-archive__mobile-option<?php echo $all_active ? ' is-active' : ''; ?>"
						href="<?php echo esc_url( $blog_archive_url ); ?>"
						role="listitem"
					>
						<?php esc_html_e( 'Vaata kõiki', 'sawerna' ); ?>
					</a>
					<?php foreach ( $blog_categories as $category ) : ?>
						<a
							class="sawerna-blog-archive__mobile-option<?php echo (int) $current_category === (int) $category->term_id ? ' is-active' : ''; ?>"
							href="<?php echo esc_url( get_category_link( $category ) ); ?>"
							role="listitem"
						>
							<?php echo esc_html( $category->name ); ?>
						</a>
					<?php endforeach; ?>
				</div>
			</details>
		</div>
	<?php endif; ?>
</header>
