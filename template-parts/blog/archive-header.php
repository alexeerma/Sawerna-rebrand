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
?>
<header class="sawerna-blog-archive__header">
	<nav class="sawerna-blog-archive__breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'sawerna' ); ?>">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Esileht', 'sawerna' ); ?></a>
		<span class="sawerna-blog-archive__breadcrumb-separator" aria-hidden="true">/</span>
		<a href="<?php echo esc_url( $blog_archive_url ); ?>"><?php echo esc_html( $blog_archive_label ); ?></a>
	</nav>

	<h1 class="sawerna-blog-archive__title"><?php echo esc_html( $blog_heading ); ?></h1>

	<?php if ( ! empty( $blog_categories ) ) : ?>
		<nav class="sawerna-blog-archive__filters" aria-label="<?php esc_attr_e( 'Blog categories', 'sawerna' ); ?>">
			<ul class="sawerna-blog-archive__filter-list">
				<li>
					<a
						class="sawerna-blog-archive__filter<?php echo $all_active ? ' is-active' : ''; ?>"
						href="<?php echo esc_url( $blog_archive_url ); ?>"
						<?php echo $all_active ? 'aria-current="page"' : ''; ?>
					><?php esc_html_e( 'Vaata kõiki', 'sawerna' ); ?></a>
				</li>
				<?php foreach ( $blog_categories as $category ) : ?>
					<?php $is_current = (int) $current_category === (int) $category->term_id; ?>
					<li>
						<a
							class="sawerna-blog-archive__filter<?php echo $is_current ? ' is-active' : ''; ?>"
							href="<?php echo esc_url( get_category_link( $category ) ); ?>"
							<?php echo $is_current ? 'aria-current="page"' : ''; ?>
						><?php echo esc_html( $category->name ); ?></a>
					</li>
				<?php endforeach; ?>
			</ul>
		</nav>
		<?php if ( ! $all_active ) : ?>
			<script>
			( function () {
				var list = document.querySelector( '.sawerna-blog-archive__filter-list' );
				var active = list && list.querySelector( '[aria-current="page"]' );
				if ( active && list.scrollWidth > list.clientWidth ) {
					list.scrollLeft = active.parentNode.offsetLeft - ( list.clientWidth - active.offsetWidth ) / 2;
				}
			}() );
			</script>
		<?php endif; ?>
	<?php endif; ?>
</header>
