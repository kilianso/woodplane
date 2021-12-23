<?php

function query($type, $limit) {
	return array('post_type' => $type, 'post_status' => 'publish', 'posts_per_page' => $limit);
}

$context = Timber::context();

$context['posts'] = Timber::get_posts();
Timber::render('home.twig', $context);

// Timber::render('home.twig', $context, 600); // Cache Data and Templates

// Timber is using Wordpress Transients Caching by default. By adding an $expires argument to the render function, the template and data will be cached for X Seconds (e.g. 600 / 60 = 10 Minutes). 
// In addition, Timber hashes the fields in the view context. This means that as soon as the data changes, the cache is automatically invalidated.
// This method is very effective, but crude - the whole template is cached. So if you have any context dependent sub-views (eg. current user), this mode won’t do.
// More about performance: https://timber.github.io/docs/guides/performance/

// For production a Plugin that caches everything to static HTML should be considered.