<?php

$context = Timber::context();
$context['results'] = Timber::get_posts();
$context['popular'] = isset($_GET['popular']);
$context['query'] = get_search_query();

Timber::render('search.twig', $context);