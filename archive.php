<?php

function filterTaxonomies ($haystack) {
	$needle = 'category';
	return(strtolower(strpos($haystack, $needle)));
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

if (strpos($topLevelTax, '-')) {
	$context['posttype'] = preg_replace('~ *-.*~', '', $topLevelTax);
} else {
	$context['posttype'] = strtolower(property_exists($subject, 'slug') ? $subject->slug : $subject->name);
}

if ($topLevelTax != 'post_tag') {
		
	$context['topLevelCats'] = Timber::get_terms($topLevelTax, array(
		'parent' => 0,
		'hide_empty' => true
	));

	if(is_tax() || is_category()){
		$term = new Timber\Term();
		$context['term'] = $term;	
		$context['parent'] = new Timber\Term($term->parent);
	}
}

Timber::render('archive.twig', $context);
