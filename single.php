<?php

$context = Timber::context();

$post = new Timber\Post();
$context['post'] = $post;

Timber::render( array( 'single-' . $post->post_name . '.twig', 'single.twig' ), $context );