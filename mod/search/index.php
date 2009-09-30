<?php

  /** Main search page */

$tag = get_input('tag');
$offset = get_input('offset', 0);
$limit = get_input('limit', 10);
$viewtype = get_input('search_viewtype','list');
$searchtype = get_input('searchtype', 'all');

// blank the results to start off
$results = new stdClass();
$results->entities = array();
$results->total = 0;

$title = sprintf(elgg_echo('searchtitle'), $tag); 

$body = '';
if (!empty($tag)) {

    $results = trigger_plugin_hook('searchentities', '', array('tag' => $tag,
							       'offset' => $offset,
							       'limit' => $limit,
							       'searchtype' => $searchtype),
				   $results);

    $searchtypes = trigger_plugin_hook('searchtypes', '', NULL, array());
    add_submenu_item(elgg_echo('searchtype:all'),
		     $CONFIG->wwwroot . "pg/search/?tag=". urlencode($tag) ."&searchtype=all");
		     
    /*
    foreach ($searchtypes as $st) {
	add_submenu_item(elgg_echo('searchtype:' . $st),
			 $CONFIG->wwwroot . "pg/search/?tag=". urlencode($tag) ."&searchtype=" . $st);
    }
    */
    

    //print_r($results);

    $body .= elgg_view_title($title); // elgg_view_title(sprintf(elgg_echo('searchtitle'),$tag));
    $body .= elgg_view('search/startblurb',array('tag' => $tag));
    

    $body .= elgg_view('search/entity_list',array('entities' => $results->entities,
						  'count' => $results->total,
						  'offset' => $offset,
						  'limit' => $limit,
						  'baseurl' => $_SERVER['REQUEST_URI'],
						  'fullview' => false,
						  'context' => 'search', 
						  'viewtypetoggle' => true,
						  'viewtype' => $viewtype,
						  'pagination' => true
						  ));




elgg_view_entity_list($results->entities, count($results->entities), 0, count($results->entities), false);
} else {

    $body .= elgg_view_title(elgg_echo('search:enterterm'));
    $body .= elgg_view('page_elements/contentwrapper', array('body' => '<div>' . elgg_view('page_elements/searchbox') . '</div>'));


}
$layout = elgg_view_layout('two_column_left_sidebar','',$body);


page_draw($title, $layout);


?>