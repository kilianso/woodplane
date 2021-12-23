<?php

namespace Woodplane\Theme\Package;
// use Timber\Timber;

/**
 * Comments stuff
 */
class Comments
{
	public function run()
	{
		add_action('comment_form_before', [$this, 'enqueueReplyScript']);
		add_filter('comment_form_default_fields',[$this, 'disableCommentUrl']);

		add_filter('next_comments_link_attributes', [$this, 'commentsLinkAttributes']);
		add_filter('previous_comments_link_attributes', [$this, 'commentsLinkAttributes']);

		add_filter( 'comment_form_defaults', [$this, 'textareaPlaceholder'] );
		add_filter( 'comment_form_default_fields', [$this, 'formFields'] );
	}

	public function enqueueReplyScript()
	{
		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}
	}

	/**
	* Disable Comments URL field
	**/
	public function disableCommentUrl($fields) {
		unset($fields['url']);
		return $fields;
	}

	/**
	* Outputs a modified comment markup.
	*
	*
	* @see wp_list_comments()
	*
	* @param WP_Comment $comment Comment to display.
	* @param int        $depth   Depth of the current comment.
	* @param array      $args    An array of arguments.
	*/
	public function betterComments($comment, $args, $depth) {

	?>
		<li <?php comment_class(); ?> id="comment-<?php comment_ID() ?>">
		<div class="comment__inner">
				<div class="comment__header">
					<div class="comment__header__left">
						<span class="comment__avatar">
							<?php echo get_avatar($comment,$size='40'); ?>
						</span>
						<span class="comment__by">
							<strong><?php echo get_comment_author() ?></strong>
							<span class="date">
								<?php
								printf( _x( '%s ago', '%s = human-readable time difference', 'wdpln' ), human_time_diff( get_comment_time( 'U' ), current_time( 'timestamp' ) ) );
								?>
							</span>
						</span>
					</div>
					<div class="comment__header__right">
						<span class="comment__reply">
							<?php comment_reply_link(array_merge( $args, array('depth' => $depth, 'max_depth' => $args['max_depth']))) ?>
						</span>
					</div>
				</div>
		<div class="comment__block">
			<?php if ($comment->comment_approved == '0') : ?>
				<em><?php esc_html_e('Your comment is awaiting moderation.','wdpln') ?></em>
				<br />
			<?php endif; ?>
			<?php comment_text() ?>
					</div>
		</div>
		<?php
	}

	public function commentsLinkAttributes() {
		return 'class="btn btn-rounded btn-outline margin--small"';
	}

	/**
	 * Change comment form textarea to use placeholder
	 *
	 * @since  1.0.0
	 * @param  array $args
	 * @return array
	 */
	public function textareaPlaceholder( $args ) {
		$args['comment_field'] = str_replace( 'textarea', 'textarea placeholder="Leave a nice message."', $args['comment_field'] );
		return $args;
	}

	/**
	 * Comment Form Fields Placeholder
	 */
	public function formFields( $fields ) {
		foreach( $fields as &$field ) {
			$field = str_replace( 'id="author"', 'id="author" placeholder="Your name"', $field );
			$field = str_replace( 'id="email"', 'id="email" placeholder="Your email"', $field );
			$field = str_replace( 'id="url"', 'id="url" placeholder="Your website"', $field );
		}
		return $fields;
	}
}
