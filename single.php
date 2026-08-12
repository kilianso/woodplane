<?php

$context = Timber::context();

$post = Timber::get_post();
$context['post'] = $post;

Timber::render( array( 'single-' . $post->post_name . '.twig', 'single.twig' ), $context );
