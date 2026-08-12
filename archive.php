<?php

function filterTaxonomies ($haystack) {
	return str_contains(strtolower($haystack), 'category');
}

$subject = get_queried_object();
$context = Timber::context();

$context['posts'] = Timber::get_posts();
$context['title'] = $subject->label ?: $subject->name;
$context['description'] = $subject->description;

$topLevelTax = strtolower($subject->label ?: $subject->name);

if (property_exists($subject, 'taxonomy')) {
	$topLevelTax = strtolower($subject->taxonomy);
} 

if (property_exists($subject, 'taxonomies')){
	$catByTax = array_filter($subject->taxonomies, 'filterTaxonomies');
	$topLevelTax = reset($catByTax);
}

$context['topLevelTax'] = $topLevelTax;

if (str_contains($topLevelTax, '-')) {
	$context['posttype'] = preg_replace('~ *-.*~', '', $topLevelTax);
} else {
	$context['posttype'] = strtolower(property_exists($subject, 'slug') ? $subject->slug : $subject->name);
}

if ($topLevelTax != 'post_tag') {
		
	$context['topLevelCats'] = Timber::get_terms([
		'taxonomy' => $topLevelTax,
		'parent' => 0,
		'hide_empty' => true,
	]);

	if(is_tax() || is_category()){
		$term = Timber::get_term();
		$context['term'] = $term;	
		$context['parent'] = $term->parent ? Timber::get_term($term->parent) : null;
	}
}

Timber::render('archive.twig', $context);

