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
		global $CONFIG;
		register_page_handler('search_indicies', 'search_page_handler');	

		register_action('search/progress', false, $CONFIG->pluginspath . 'search/actions/progress.php');

		register_page_handler('search','search_page_handler');


		register_plugin_hook('searchentities', 'all', 'search_test_search_hook');

		// TODO: handle deleted entities and clean out the index?

		register_plugin_hook('create', 'user', 'search_index_user_entity');
		register_plugin_hook('update', 'user', 'search_index_user_entity');

		register_plugin_hook('create', 'group', 'search_index_group_entity');
		register_plugin_hook('update', 'group', 'search_index_group_entity');

		register_plugin_hook('create', 'object', 'search_index_object_entity');
		register_plugin_hook('update', 'object', 'search_index_object_entity');

		register_plugin_hook('searchtypes', 'all', 'search_base_search_types_hook');

		register_plugin_hook('indexentity', 'all', 'search_index_entity_hook');

		extend_view('css', 'search/css');
	}
	
	function search_pagesetup() {
		global $CONFIG;
		if (get_context() == 'admin' && isadminloggedin()) {
		    add_submenu_item(elgg_echo('search:rebuildmenu'), $CONFIG->wwwroot . 'mod/search/reindex.php');
		}
	}

	function search_get_entity_count() {
		global $CONFIG;
		$query = "SELECT COUNT(*) AS count FROM {$CONFIG->dbprefix}entities";
		$result = get_data_row($query)->count;
		return $result;
	}

	function search_get_index_size() {
		global $CONFIG;
		$query = "SELECT COUNT(*) AS count FROM {$CONFIG->dbprefix}TODO";
		//$result = get_data_row($query)->count;
		$result = 42;// Sample data till the table exists;
		return $result;
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
	    $offset = $params['offset']; // starting page
	    $limit = $params['limit']; // number per page
	    $searchtype = $params['searchtype']; // the search type we're looking for

	    // SELECT guid, GROUP_CONCAT(search_type) FROM search_index GROUP BY guid ORDER BY count(search_type) ??


	    return $returnvalue;
	}

        /**
	 * indexes users based on name, username, and email address
	 */
        function search_index_user_entity($hook, $type, $returnvalue, $params) {
	    $entity = $params['entity'];

	    // index name
	    trigger_plugin_hook('indexentity', '', array('entity' => $entity,
							 'searchtype' => 'name',
							 'searchstring' => $entity->name));
	    trigger_plugin_hook('indexentity', '', array('entity' => $entity,
							 'searchtype' => 'email',
							 'searchstring' => $entity->email));
	    trigger_plugin_hook('indexentity', '', array('entity' => $entity,
							 'searchtype' => 'username',
							 'searchstring' => $entity->username));
	    return $returnvalue;
        }

        /**
	 * indexes groups based on name and description
	 */
        function search_index_group_entity($hook, $type, $returnvalue, $params) {
	    $entity = $params['entity'];

	    // index name
	    trigger_plugin_hook('indexentity', '', array('entity' => $entity,
							 'searchtype' => 'name',
							 'searchstring' => $entity->name));
	    trigger_plugin_hook('indexentity', '', array('entity' => $entity,
							 'searchtype' => 'description',
							 'searchstring' => $entity->description));
	    return $returnvalue;
        }

        /**
	 * indexes objects based on title and description
	 */
        function search_index_object_entity($hook, $type, $returnvalue, $params) {
	    $entity = $params['entity'];

	    // index name
	    trigger_plugin_hook('indexentity', '', array('entity' => $entity,
							 'searchtype' => 'title',
							 'searchstring' => $entity->title));
	    trigger_plugin_hook('indexentity', '', array('entity' => $entity,
							 'searchtype' => 'description',
							 'searchstring' => $entity->description));
	    return $returnvalue;
        }

	/**
	 * indexes an entity to be searchable by the core search system
	 */
	function search_index_entity_hook($hook, $type, $returnvalue, $params) {
	    $entity = $params['entity'];
	    $searchtype = $params['searchtype'];
	    $string = $params['searchstring'];

	    // DELETE FROM SEARCH TABLE WHERE FOO BAR
	    // INSERT INTO SEARCH TABLE FOO BAR

	    return $returnvalue;
	}


        /**
	 * return our base search types
	 */
        function search_base_search_types_hook($hook, $type, $returnvalue, $params) {
	    if (!is_array($returnvalue)) {
		$returnvalue = array();
	    }

	    $returnvalue[] = 'name';
	    $returnvalue[] = 'email';
	    $returnvalue[] = 'description';
	    $returnvalue[] = 'title';
	    $returnvalue[] = 'username';


	    return $returnvalue;
	}

	/** Register init system event **/

	register_elgg_event_handler('init','system','search_init');

	register_elgg_event_handler('pagesetup', 'system', 'search_pagesetup');
	
?>