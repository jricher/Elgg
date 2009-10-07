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


		//register_plugin_hook('searchentities', 'all', 'search_test_search_hook');

		register_plugin_hook('search:entities', 'all', 'search_original_hook');

		// TODO: handle deleted entities and clean out the index?

		// pipe object creation and update through our indexer
		register_elgg_event_handler('create', 'all', 'search_call_index_entity');
		register_elgg_event_handler('update', 'all', 'search_call_index_entity');

		// basic indexes for users, groups, and objects, handled by the core
		register_elgg_event_handler('index', 'user', 'search_index_user_entity');
		//register_elgg_event_handler('index', 'group', 'search_index_group_entity'); // this is handled by the group plugin
		register_elgg_event_handler('index', 'object', 'search_index_object_entity');

		// list of available search types should include our base parts
		register_plugin_hook('searchtypes', 'all', 'search_base_search_types_hook');

		// hook to actually index an entity
		register_plugin_hook('indexentity', 'all', 'search_index_entity_hook');

		extend_view('css', 'search/css');
	}
	
	function search_pagesetup() {
		global $CONFIG;
		if (get_context() == 'admin' && isadminloggedin()) {
		    add_submenu_item(elgg_echo('search:rebuild:menu'), $CONFIG->wwwroot . 'mod/search/reindex.php');
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
		$count = $returnvalue->total; // current count
		$limit = $params['limit'];
		$offset = $params['offset'];
		$searchtype = $params['searchtype'];

		$ents = get_entities(); // return all entities on the site

		foreach ($ents as $e) {
			$s = $e->getVolatileData('search');
			if (!$s) {
				$s = array();
			}
			$s[] = 'test';
			$e->setVolatileData('search', $s);
		}

		// stretch the display if needed
		if (count($ents) > $count) {
			$returnvalue->total = count($ents);
		}

		if (count($ents) > $limit) {
			$ents = array_slice($ents, $offset, $limit);
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

	    //print 'foo';
	    $count = get_entities_by_keyword($tag, $limit, $offset, true); 
	    $ents = get_entities_by_keyword($tag, $limit, $offset, false);

	    //print 'count:' . $count;

	    $returnvalue->entities = array_merge($returnvalue->entities, $ents);
	    if ($count > $returnvalue->total) {
		$returnvalue->total = $count;
	    }

	    return $returnvalue;
	}
	
	function search_original_hook($hook, $type, $returnvalue, $params) {
	    $tag = $params['tag'];
	    $offset = $params['offset']; // starting page
	    $limit = $params['limit']; // number per page
	    $searchtype = $params['searchtype']; // the search type we're looking for
		$object_type = $params['object_type'];
		$subtype = $params['subtype'];
		$owner_guid = $params['owner_guid'];
		$tagtype = $params['tagtype'];
		
		$count = get_entities_from_metadata($tagtype, elgg_strtolower($tag), $object_type, $subtype, $owner_guid, $limit, $offset, "", 0, true); 
		$ents =  get_entities_from_metadata($tagtype, elgg_strtolower($tag), $object_type, $subtype, $owner_guid, $limit, $offset, "", 0, false);	


		## Foreach entity
		#	get the metadata keys
		#	If the value matches, hang onto the key
		#	add all the matched keys to VolatileData
		foreach ($ents as $ent) {
			$metadata = get_metadata_for_entity($ent->guid);
			if ($metadata) {
				$matched = array();
				foreach ($metadata as $tuple) {
					if ($tuple->value == $tag) {
						# This is one of the matching elements
						$matched[] = $tuple->name;
					}
				}
				$ent->setVolatileData("search", $matched);
			}
		}
		
	    $returnvalue->entities = array_merge($returnvalue->entities, $ents);
	    if ($count > $returnvalue->total) {
			$returnvalue->total = $count;
	    }

	    return $returnvalue;
	}

	/**
	 * Clear out the search index
	 */
	function search_clear_index() {
	    global $CONFIG; 
	    $sql = "TRUNCATE {$CONFIG->dbprefix}search_index";
	    
	    update_data($sql);
	}


        /*
         * get entities by search term
         *
         * returns a list of entities that match the keyword
         * or the # of entities in the table if $count == true
         *
         * note, if count == true, limit and offset are ignored
         * this was done on purpose
         */
        function get_entities_by_keyword($keyword,$limit=10,$offset=0,$count=false){

            global $CONFIG;
            $term = sanitise_string($keyword);
	    //print 'bar';
            //build the query
            if($count){
                //we don't care about limit...
                //$query ="SELECT COUNT(*) from {$CONFIG->dbprefix}search_index WHERE MATCH (subtype,string) AGAINST ('{$term}')";
                $query = "SELECT COUNT(*) as count ";
		$query .= "FROM {$CONFIG->dbprefix}search_index as s, {$CONFIG->dbprefix}entities as e ";
                $query .= "WHERE s.guid = e.guid ";
                //$query .= "AND MATCH(s.string) AGAINST('{$term}')";
		$query .= "AND s.string LIKE '%{$term}%' ";
		//$query .= "GROUP BY s.guid";
            }else{
                //return stuff
                $query = "SELECT s.guid as guid,GROUP_CONCAT(s.subtype) as subtype ";
                $query .= "FROM {$CONFIG->dbprefix}search_index as s, {$CONFIG->dbprefix}entities as e ";
                $query .= "WHERE s.guid = e.guid ";
                //$query .= "AND MATCH(s.string) AGAINST('{$term}')";
		$query .= "AND s.string LIKE '%{$term}%' ";
		//$query .= "GROUP BY s.guid";
            }
	    //print 'foo';
            $access = get_access_sql_suffix("e");
	    //print ":::" . $access . ":::";
            $query .= 'AND ' . $access;
            
	    $query .= ' GROUP BY s.guid';

	    if (!$count) {
		if($limit){
		    $query .= " LIMIT {$limit} ";
		}
		if($offset){
		    $query .= " OFFSET {$offset} ";
		}
	    }
	    //print $query;

 	    if ($count) {
		//print_r($results);
		$total = get_data($query);
		return count($total); // I DISLIKE THIS!
	    } else {
		$results = get_data($query);

		//we don't need to worry about permissions, because get_entity takes care of it for us!


		$retVal = array();
		//there's probably a cooler way to do this $x bit, but I'm not that cool.
		$x = 0;
		foreach($results as $result){
		    //print_r($result);
		    $t[$x] = get_entity($result->guid);
		    //print '::' . $result->guid . ':' . $result->subtype;
		    $s = explode(',', $result->subtype);
		    //print_r($t[$x]);
		    $t[$x]->setVolatileData('search', $s);
		    $x += 1;
		}

		return $t;
	    }

        }

        /**
	 * listens for object creation and update events and causes an index event to fire on the same object
	 */
        function search_call_index_entity($event, $object_type, $object) {
	    trigger_elgg_event('index', $object_type, $object);
	}


        /**
	 * indexes users based on name, username, and email address
	 */
        function search_index_user_entity($event, $object_type, $object) {
	    // index name
	    trigger_plugin_hook('indexentity', '', array('entity' => $object,
							 'searchtype' => 'name',
							 'searchstring' => $object->name));
	    trigger_plugin_hook('indexentity', '', array('entity' => $object,
							 'searchtype' => 'email',
							 'searchstring' => $object->email));
	    trigger_plugin_hook('indexentity', '', array('entity' => $object,
							 'searchtype' => 'username',
							 'searchstring' => $object->username));
	    return true;
        }

        /**
	 * indexes groups based on name and description
	 */
	function search_index_group_entity($event, $object_type, $object) {
	    // index name
	    trigger_plugin_hook('indexentity', '', array('entity' => $object,
							 'searchtype' => 'name',
							 'searchstring' => $object->name));
	    trigger_plugin_hook('indexentity', '', array('entity' => $object,
							 'searchtype' => 'description',
							 'searchstring' => $object->description));
	    return true;
        }

        /**
	 * indexes objects based on title and description
	 */
	function search_index_object_entity($event, $object_type, $object) {
	    // index name
	    trigger_plugin_hook('indexentity', '', array('entity' => $object,
							 'searchtype' => 'title',
							 'searchstring' => $object->title));
	    trigger_plugin_hook('indexentity', '', array('entity' => $object,
							 'searchtype' => 'description',
							 'searchstring' => $object->description));
	    return true;
        }

	/**
	 * indexes an entity to be searchable by the core search system
	 */
	function search_index_entity_hook($hook, $type, $returnvalue, $params) {
	    global $CONFIG;

	    $entity = $params['entity'];
	    $searchtype = $params['searchtype'];
	    $string = $params['searchstring'];


            $guid = $entity->guid;
            if($guid){
                $query = "INSERT INTO {$CONFIG->dbprefix}search_index (guid,subtype,string) VALUES({$guid},\"{$searchtype}\",\"{$string}\") ON DUPLICATE KEY UPDATE string=\"{$string}\"";
                $result = get_data($query);
            }

	    /*******************************************
		// Debug printing while we wait for a DB to put this in
		
		print "<BR>Entity: \n<BR>";
		print_r($entity);
		print "\n<BR>Searchtype: " . $searchtype;
		// We need to check that the search string is just a string and not something else (like an array)
		if (is_array($string)) {
			print '<BR>ImplodeSearchstring: ' . implode(",", $string) . "\n<BR>";
		} else {
			print '<BR>Searchstring: ' . $string . "\n<BR>";
		}
		/************************************************************/
		
	    // INSERT INTO es_search_index (guid, search_source, string) VALUES (1, 'bar', 'foo bar the bob the bbz') ON DUPLICATE KEY UPDATE string='pants?'

	    // DELETE FROM SEARCH TABLE WHERE FOO BAR
	    // INSERT INTO SEARCH TABLE FOO BAR

	    //usleep(1000);

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
