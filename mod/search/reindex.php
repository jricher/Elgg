<?php

	require_once(dirname(dirname(dirname(__FILE__))) . '/engine/start.php');

	admin_gatekeeper();
	set_context('admin');
	// Set admin user for user block
	set_page_owner($_SESSION['guid']);
	
        $title = elgg_view_title(elgg_echo('search:rebuild:title'));
	$totalEntities = search_get_entity_count();	
	$indexSize = search_get_index_size();
	$progressBar = $CONFIG->wwwroot . 'action/search/progress?offset=0&limit=10';

// stolen from the admin views, might want to go into its own thing?
// really, those need to be made available elsewhere...

        $body = '<div class="admin_statistics">
    <h3>' . elgg_echo('search:statistics') . '</h3>
    <table>
    	<tr class="odd">
            <td class="column_one"><b>' . elgg_echo('search:statistics:totalentities') . ':</b></td>
            <td>' . $totalEntities . ' ' . elgg_echo('search:statistics:entries') . '</td>
        </tr>
        <tr class="even">
            <td class="column_one"><b>' . elgg_echo('search:statistics:indexsize') . ' :</b></td>
            <td>' . $indexSize . ' ' . elgg_echo('search:statistics:entries') . '</td>
        </tr>

    </table> 
</div>';


	$content .= '<input type="submit" id="reindex-button" value="' . elgg_echo('search:rebuild:go') . '" /><BR>' . "\n";
	$content .= '<span id="reindex-working" class="cancel_button">' . elgg_echo('search:rebuild:working') . '</span>' . "\n";
	$content .= '<span id="reindex-progressBar" class="progressBar"></span><BR>' . "\n";
	$content .= '<span id="reindex-progress"></span> ';
	$content .= '<script type="text/javascript" src="' . $CONFIG->wwwroot . 'mod/search/jquery.progressbar.js"></script><script type="text/javascript">
		
		function search_AjaxLoader(event) {
			event.preventDefault();
			$("#reindex-button").slideUp("slow");
			$("#reindex-working").slideDown("slow");
            $("#reindex-progressBar").progressBar(0);
			$("#reindex-progress").html(\'' . elgg_view('ajax/loader') . '\');
			$("#reindex-progress").load("' . $progressBar . '");
		}
	
		$(document).ready(function() {
			$("#reindex-button").click(search_AjaxLoader);
			$("#reindex-working").hide();
			$("#reindex-progressBar").progressBar({max: ' . $totalEntities . ', textFormat: \'fraction\' });
		});

	</script>';


	$body .= elgg_view('page_elements/contentwrapper', array('body' => $content));
	
	page_draw(elgg_echo('search:rebuild:title'), elgg_view_layout("two_column_left_sidebar", '', $title . $body));

?>
