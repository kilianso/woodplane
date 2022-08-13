<?php

$context = Timber::context();

// calling the_post() here solve a lot of compatibility issues with plugins
// could probably be refactored once Timber hits a stable version of 2.0

if (have_posts()) {
  while(have_posts()) {
    the_post();

    $post = new Timber\Post();
    $context['post'] = $post;

    Timber::render( array( 'single-' . $post->post_name . '.twig', 'single.twig' ), $context);
  }
}
