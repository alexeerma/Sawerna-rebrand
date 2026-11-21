<?php
/**
 * Template part for displaying single blog posts.
 *
 * @package Sawerna
 */

$categories         = get_the_category();
$blog_archive_url   = sawerna_get_blog_archive_url();
$blog_archive_label = sawerna_get_blog_archive_label();
$post_url           = get_permalink();
$post_title         = get_the_title();
$post_excerpt       = has_excerpt() ? get_the_excerpt() : '';
$post_thumbnail_url = has_post_thumbnail() ? wp_get_attachment_image_url( get_post_thumbnail_id(), 'large' ) : '';
$previous_post      = get_previous_post();
$next_post          = get_next_post();
$pinterest_query    = array(
	'url'         => $post_url,
	'description' => $post_title,
);

if ( $post_thumbnail_url ) {
	$pinterest_query['media'] = $post_thumbnail_url;
}

$share_links = array(
	array(
		'label' => __( 'Facebook', 'sawerna' ),
		'url'   => add_query_arg(
			array(
				'u' => $post_url,
			),
			'https://www.facebook.com/sharer/sharer.php'
		),
		'icon'  => function_exists( 'sawerna_footer_icon_facebook' ) ? sawerna_footer_icon_facebook() : '',
	),
	array(
		'label' => __( 'Pinterest', 'sawerna' ),
		'url'   => add_query_arg(
			$pinterest_query,
			'https://pinterest.com/pin/create/button/'
		),
		'icon'  => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M12.04 2C6.61 2 4 5.89 4 10.13c0 2.59 1.47 5.82 3.82 6.85.36.16.31-.04.62-1.21.05-.19.03-.25-.13-.44-2.82-3.27-.55-9.98 5.09-9.98 8.17 0 6.64 11.39 1.42 11.39-1.34 0-2.34-1.11-2.02-2.47.39-1.62 1.15-3.37 1.15-4.54 0-2.96-4.43-2.52-4.43 1.4 0 1.2.41 2.01.41 2.01l-1.64 6.94c-.43 1.83.06 4.8.11 5.06.03.15.2.2.29.08.13-.17 1.72-2.47 2.19-4.27.17-.66.87-3.37.87-3.37.46.84 1.8 1.55 3.23 1.55 4.26 0 7.34-3.92 7.34-8.78C20.61 5.53 16.96 2 12.04 2z"/></svg>',
	),
	array(
		'label' => __( 'E-mail', 'sawerna' ),
		'url'   => 'mailto:?subject=' . rawurlencode( $post_title ) . '&body=' . rawurlencode( $post_url ),
		'icon'  => function_exists( 'sawerna_footer_icon_mail' ) ? sawerna_footer_icon_mail() : '',
	),
);
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'sawerna-blog-post' ); ?>>
	<div class="sawerna-blog-post__container">
		<nav class="sawerna-blog-post__breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'sawerna' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Esileht', 'sawerna' ); ?></a>
			<span class="sawerna-blog-post__breadcrumb-separator" aria-hidden="true">/</span>
			<a href="<?php echo esc_url( $blog_archive_url ); ?>"><?php echo esc_html( $blog_archive_label ); ?></a>
			<span class="sawerna-blog-post__breadcrumb-separator" aria-hidden="true">/</span>
			<span><?php echo esc_html( wp_trim_words( $post_title, 8, '...' ) ); ?></span>
		</nav>

		<header class="sawerna-blog-post__header">
			<?php if ( ! empty( $categories ) ) : ?>
				<div class="sawerna-blog-post__categories">
					<?php foreach ( $categories as $category ) : ?>
						<a class="sawerna-blog-post__category" href="<?php echo esc_url( get_category_link( $category ) ); ?>">
							<?php echo esc_html( $category->name ); ?>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<p class="sawerna-blog-post__meta">
				<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
			</p>

			<h1 class="sawerna-blog-post__title"><?php the_title(); ?></h1>

			<?php if ( '' !== trim( $post_excerpt ) ) : ?>
				<div class="sawerna-blog-post__excerpt">
					<?php echo wp_kses_post( wpautop( $post_excerpt ) ); ?>
				</div>
			<?php endif; ?>
		</header>

		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="sawerna-blog-post__hero">
				<?php
				the_post_thumbnail(
					'large',
					array(
						'class'    => 'sawerna-blog-post__hero-image',
						'loading'  => 'eager',
						'decoding' => 'async',
						'sizes'    => '(max-width: 767px) 100vw, 860px',
					)
				);
				?>
			</figure>
		<?php endif; ?>

		<div class="sawerna-blog-post__content entry-content">
			<?php
			the_content();

			wp_link_pages(
				array(
					'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'sawerna' ),
					'after'  => '</div>',
				)
			);
			?>
		</div>

		<section class="sawerna-blog-post__share" aria-label="<?php esc_attr_e( 'Share article', 'sawerna' ); ?>">
			<p class="sawerna-blog-post__share-title"><?php esc_html_e( 'Jaga seda artiklit', 'sawerna' ); ?></p>
			<div class="sawerna-blog-post__share-links">
				<?php foreach ( $share_links as $share_link ) : ?>
					<a
						class="sawerna-blog-post__share-link"
						href="<?php echo esc_url( $share_link['url'] ); ?>"
						target="_blank"
						rel="noopener noreferrer"
						aria-label="<?php echo esc_attr( $share_link['label'] ); ?>"
					>
						<?php echo $share_link['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</a>
				<?php endforeach; ?>
			</div>
		</section>

		<?php if ( $previous_post || $next_post ) : ?>
			<nav class="sawerna-blog-post__article-nav" aria-label="<?php esc_attr_e( 'Post navigation', 'sawerna' ); ?>">
				<?php if ( $previous_post ) : ?>
					<a class="sawerna-blog-post__article-nav-link" href="<?php echo esc_url( get_permalink( $previous_post ) ); ?>">
						<span class="sawerna-blog-post__article-nav-label"><?php esc_html_e( 'Eelmine artikkel', 'sawerna' ); ?></span>
						<span class="sawerna-blog-post__article-nav-title"><?php echo esc_html( get_the_title( $previous_post ) ); ?></span>
					</a>
				<?php endif; ?>

				<?php if ( $next_post ) : ?>
					<a class="sawerna-blog-post__article-nav-link" href="<?php echo esc_url( get_permalink( $next_post ) ); ?>">
						<span class="sawerna-blog-post__article-nav-label"><?php esc_html_e( 'Järgmine artikkel', 'sawerna' ); ?></span>
						<span class="sawerna-blog-post__article-nav-title"><?php echo esc_html( get_the_title( $next_post ) ); ?></span>
					</a>
				<?php endif; ?>
			</nav>
		<?php endif; ?>
	</div>
</article>
