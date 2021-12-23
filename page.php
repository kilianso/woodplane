<?php

$context = Timber::context();

$post = new Timber\Post();
$context['post'] = $post;

Timber::render( array( 'page-' . $post->post_name . '.twig', 'page.twig' ), $context );