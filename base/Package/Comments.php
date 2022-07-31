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

	// Disable Comments URL field
	public function disableCommentUrl($fields) {
		unset($fields['url']);
		return $fields;
	}

	public function commentsLinkAttributes() {
		return 'class="btn btn-rounded btn-outline margin--small"';
	}

	// Change comment form textarea to use placeholder
	public function textareaPlaceholder( $args ) {
		$args['comment_field'] = str_replace( 'textarea', 'textarea placeholder="Leave a nice message."', $args['comment_field'] );
		return $args;
	}

	// Comment Form Fields Placeholder
	public function formFields( $fields ) {
		foreach( $fields as &$field ) {
			$field = str_replace( 'id="author"', 'id="author" placeholder="Your name"', $field );
			$field = str_replace( 'id="email"', 'id="email" placeholder="Your email"', $field );
			$field = str_replace( 'id="url"', 'id="url" placeholder="Your website"', $field );
		}
		return $fields;
	}
}
