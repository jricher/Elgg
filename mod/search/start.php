<?php

	/**
	 * Elgg core search.
	 * 
	 * @package Elgg
	 * @subpackage Core
	 * @author Curverider Ltd <info@elgg.com>
	 * @link http://elgg.org/
	 */

	/**
	 * Initialise search helper functions.
	 *
	 */
	function search_init() {
		register_page_handler('search','search_page_handler');

		register_plugin_hook('searchentities', 'all', 'search_test_search_hook');

		extend_view('css', 'search/css');
	}
	
	/**
	 * Page handler for search
	 *
	 * @param array $page Page elements from pain page handler
	 */
	function search_page_handler($page) {
		global $CONFIG;
		
		if(!get_input('tag')) {
			set_input('tag', $page[0]);	
		}

		include_once($CONFIG->path . "mod/search/index.php");
	}

	function search_test_search_hook($hook, $type, $returnvalue, $params) {
		$ents = get_entities();

		foreach ($ents as $e) {
			$s = $e->getVolatileData('search');
			if (!$s) {
				$s = array();
			}
			$s[] = 'test';
			$e->setVolatileData('search', $s);
		}

		print_r($ents);

		$returnvalue->entities = array_merge($returnvalue->entities, $ents); // return all entities on the site

		return $returnvalue;

	}


	/** Register init system event **/
	register_elgg_event_handler('init','system','search_init');

?>