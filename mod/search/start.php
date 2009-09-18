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

	/**
	 * Test search function, adds all known entities to the display with a 
	 * 'test' search data source.
	 */
	function search_test_search_hook($hook, $type, $returnvalue, $params) {
		$ents = get_entities(); // return all entities on the site

		foreach ($ents as $e) {
			$s = $e->getVolatileData('search');
			if (!$s) {
				$s = array();
			}
			$s[] = 'test';
			$e->setVolatileData('search', $s);
		}

		//print_r($ents);

		$returnvalue->entities = array_merge($returnvalue->entities, $ents);

		return $returnvalue;

	}

	/**
	 * Core search hook.
	 * Checks the core index table for possible matches against the 'string' column.
	 * Returns an object with two parts:
	 *    ->entities: an array of instantiated entities that have been decorated with 
	 *                volatile "search" data indicating what they matched. These are
	 *                the entities to be displayed to the user on this page.
	 *    ->table: a data structure keyed on GUID's and contain an array of matching
	 *                search types. Plugins can use this data to see if they need to
	 *                add an entity to the search results or decorate an existing result.
	 */
	function search_core_hook($hook, $type, $returnvalue, $params) {

	    $tag = $params['tag'];
	    $page = $params['page']; // replacement for offset
	    

	    // SELECT guid, GROUP_CONCAT(search_type) FROM search_index GROUP BY guid ORDER BY count(search_type) ??


	    return $returnvalue;
	}

	/**
	 * indexes an entity to be searchable by the core search system
	 */
        function search_index_entity($entity, $type, $string) {

	}

	/** Register init system event **/
	register_elgg_event_handler('init','system','search_init');

?>