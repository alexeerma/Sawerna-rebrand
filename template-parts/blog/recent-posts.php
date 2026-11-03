<?php
/**
 * Recent posts section for single blog posts.
 *
 * @package Sawerna
 */

$recent_posts = get_posts(
	array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => 4,
		'post__not_in'        => array( get_the_ID() ),
		'ignore_sticky_posts' => true,
	)
);

if ( empty( $recent_posts ) ) {
	return;
}
?>
<section class="sawerna-blog-post__recent">
	<div class="sawerna-blog-post__container">
		<h2 class="sawerna-blog-post__recent-title"><?php esc_html_e( 'Viimased postitused', 'sawerna' ); ?></h2>
		<div class="sawerna-blog-post__recent-list">
			<?php foreach ( $recent_posts as $recent_post ) : ?>
				<article class="sawerna-blog-post__recent-item">
					<a class="sawerna-blog-post__recent-link" href="<?php echo esc_url( get_permalink( $recent_post ) ); ?>">
						<span class="sawerna-blog-post__recent-media" aria-hidden="true">
							<?php
							if ( has_post_thumbnail( $recent_post ) ) {
								echo get_the_post_thumbnail(
									$recent_post,
									'thumbnail',
									array(
										'class'    => 'sawerna-blog-post__recent-image',
										'loading'  => 'lazy',
										'decoding' => 'async',
									)
								); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							}
							?>
						</span>
						<span class="sawerna-blog-post__recent-copy">
							<span class="sawerna-blog-post__recent-date"><?php echo esc_html( get_the_date( '', $recent_post ) ); ?></span>
							<span class="sawerna-blog-post__recent-heading"><?php echo esc_html( get_the_title( $recent_post ) ); ?></span>
						</span>
					</a>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
