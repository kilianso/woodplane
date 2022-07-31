<?php

wp_list_comments( array(
    'short_ping' => true,
    'type' => 'comment',
    'callback' => 'betterComments',
));

function betterComments($comment, $args, $depth) {
    $context = Timber::context();
    $context['comment'] = $comment;
    $context["commentReplyArgs"] = array('reply_text' => "Reply", 'depth' => 1, 'max_depth' => 2);
    Timber::render( 'partials/Comment/Comment.twig', $context );
}

// The wrapper to output comments properly can be found in components/Comments/Comments.twig and will be included in single.twig
