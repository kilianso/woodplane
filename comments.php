<?php
/**
 * The template for displaying comments
 *
 * This is the template that displays the area of the page that contains both the current comments
 * and the comment form.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Woodplane
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

<section id="comments" class="post_comments">
	<div class="container is-narrow">
		<div class="row">
			<div class="col-sm-12">
				<?php
				// You can start editing here -- including this comment!
				if ( have_comments() ) : ?>
				<h2 class="comments-title">
					<?php
					$comment_count = get_comments_number();
					if ( '1' === $comment_count ) {
						printf(
							/* translators: 1: title. */
							esc_html__($comment_count . ' Comment', 'wdpln' )
						);
					} else {
						printf( // WPCS: XSS OK.
							/* translators: 1: comment count number, 2: title. */
							esc_html__($comment_count . ' Comments', 'wdpln' )
							// number_format_i18n( $comment_count )
						);
					}
					?>
				</h2><!-- .comments-title -->

				<ol class="comment-list">
					<?php
					wp_list_comments( array(
						'style'      => 'ul',
						'short_ping' => true,
						'callback' => [wdpln_theme()->Package->Comments, 'betterComments'],
						'type' => 'comment',
						'args' => false
					) );
					?>
				</ol><!-- .comment-list -->

				<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) :  ?>
					<div class="col-sm-12 text-center">
						<?php previous_comments_link( 'More comments', 0 ); ?>
					</div>
        		<?php endif; // check for comment navigation ?>

				<?php
					// If comments are closed and there are comments, let's leave a little note, shall we?
					if ( ! comments_open() ) : ?>
						<p class="no-comments"><?php esc_html_e( 'Comments are closed.', 'wdpln' ); ?></p>
						<?php
					endif;

				endif; // Check for have_comments().

				$comments_args = array(
				   'submit_button' => '<div class="comment-form-submit text-center margin--small">
		            								<input name="%1$s" type="submit" id="%2$s" class="%3$s btn btn-outline btn-rounded" value="%4$s" />
	        										</div>'
				);
				comment_form($comments_args);
				?>
			</div>
		</div>
	<div>
</section><!-- #comments -->
