<?php

	/**
	 * Elgg search listing
	 * 
	 * @package Elgg
	 * @subpackage Core

	 * @author Curverider Ltd

	 * @link http://elgg.org/
	 */

	if (isset($vars['search_viewtype']) && $vars['search_viewtype'] == "gallery") {
		
		echo elgg_view("search/gallery_listing",$vars);
		
	} else {

?>

	<div class="search_listing">
	
<?php 
		
	    echo $vars['entity_view'];

	    if ($vars['search_types'] && is_array($vars['search_types'])) {
		echo '<div class="searchtypes">Matched: ';
		foreach ($vars['search_types'] as $st) {
		    echo '<span class="searchtype">' . $st . '</span> ';
		}
		echo '</div>';
		
	    }
	    
	    
	    

?>
	</div>
	
<?php

	}

?>