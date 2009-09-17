<?php

  /** Main search page */

$tag = get_input('tag');

// blank the results to start off
$results = new stdClass();
$results->entities = array();
$results->table = array();

$title = sprintf(elgg_echo('searchtitle'), $tag); 

$body = '';
if (!empty($tag)) {

    $results = trigger_plugin_hook('searchentities', '', $tag, $results);

    //print_r($results);

    $body .= elgg_view_title($title); // elgg_view_title(sprintf(elgg_echo('searchtitle'),$tag));
    $body .= elgg_view('search/startblurb',array('tag' => $tag));
    $body .= elgg_view('search/entity_list',array('entities' => $results->entities,
						  'count' => count($results->entities),
						  'offset' => 0,
						  'limit' => 10,
						  'baseurl' => $_SERVER['REQUEST_URI'],
						  'fullview' => false,
						  'context' => 'search', 
						  'viewtypetoggle' => true,
						  'viewtype' => get_input('search_viewtype','list'), 
						  'pagination' => true
						  ));




elgg_view_entity_list($results->entities, count($results->entities), 0, count($results->entities), false);
}
$layout = elgg_view_layout('two_column_left_sidebar','',$body);


page_draw($title, $layout);


?>