<?php
/**
 * The template for displaying comments
 *
 * This is the template that displays the area of the page that contains both the current comments
 * and the comment form.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Sawerna
 */

/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */
if ( post_password_required() ) {
	return;
}
?>

<div id="comments" class="comments-area">

	<?php if ( have_comments() || comments_open() ) : ?>
		<h2 class="comments-title"><?php esc_html_e( 'Kommentaarid', 'sawerna' ); ?></h2>
	<?php endif; ?>

	<?php
	if ( have_comments() ) :
		?>
		<?php the_comments_navigation(); ?>

		<ol class="comment-list">
			<?php
			wp_list_comments(
				array(
					'style'      => 'ol',
					'short_ping' => true,
				)
			);
			?>
		</ol><!-- .comment-list -->

		<?php
		the_comments_navigation();

		// If comments are closed and there are comments, let's leave a little note, shall we?
		if ( ! comments_open() ) :
			?>
			<p class="no-comments"><?php esc_html_e( 'Comments are closed.', 'sawerna' ); ?></p>
			<?php
		endif;

	endif; // Check for have_comments().

	$commenter = wp_get_current_commenter();
	$req       = get_option( 'require_name_email' );
	$aria_req  = $req ? " aria-required='true'" : '';

	comment_form(
		array(
			'title_reply'          => esc_html__( 'Kirjuta kommentaar', 'sawerna' ),
			'title_reply_before'   => '<h3 id="reply-title" class="comment-reply-title">',
			'title_reply_after'    => '</h3>',
			'label_submit'         => esc_html__( 'Lisa kommentaar', 'sawerna' ),
			'class_submit'         => 'submit sawerna-blog-post__comment-submit',
			'comment_notes_before' => '<p class="comment-notes">' . esc_html__( 'Kohustuslikud väljad *', 'sawerna' ) . '<br>' . esc_html__( 'NB! Sinu e-maili aadressi veebilehel ei avalikustata.', 'sawerna' ) . '</p>',
			'comment_notes_after'  => '',
			'comment_field'        => '<p class="comment-form-comment"><label for="comment">' . esc_html__( 'Kommentaar', 'sawerna' ) . ' <span class="required">*</span></label><textarea id="comment" name="comment" cols="45" rows="6" maxlength="65525" required="required"></textarea></p>',
			'fields'               => array(
				'author' => '<p class="comment-form-author"><label for="author">' . esc_html__( 'Nimi', 'sawerna' ) . ( $req ? ' <span class="required">*</span>' : '' ) . '</label><input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30" maxlength="245"' . $aria_req . ( $req ? ' required="required"' : '' ) . ' /></p>',
				'email'  => '<p class="comment-form-email"><label for="email">' . esc_html__( 'E-post', 'sawerna' ) . ( $req ? ' <span class="required">*</span>' : '' ) . '</label><input id="email" name="email" type="email" value="' . esc_attr( $commenter['comment_author_email'] ) . '" size="30" maxlength="100" aria-describedby="email-notes"' . $aria_req . ( $req ? ' required="required"' : '' ) . ' /></p>',
				'url'    => '<p class="comment-form-url"><label for="url">' . esc_html__( 'Veebileht', 'sawerna' ) . '</label><input id="url" name="url" type="url" value="' . esc_attr( $commenter['comment_author_url'] ) . '" size="30" maxlength="200" /></p>',
			),
		)
	);
	?>

</div><!-- #comments -->
