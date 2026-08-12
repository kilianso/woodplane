<?php

$context = Timber::context();

function query($type, $limit)
{
    return array('post_type' => $type, 'post_status' => 'publish', 'posts_per_page' => $limit);
}

$post = Timber::get_post();
$context['post'] = $post;

if (is_front_page()) {
    $context['posts'] = Timber::get_posts(query('post', 6));
    Timber::render('home.twig', $context);
} else {
    Timber::render(array('page-' . $post->post_name . '.twig', 'page.twig'), $context);
}
