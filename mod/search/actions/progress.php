<?php
	global $CONFIG;
	$offset = get_input('offset', 0);
	$limit = get_input('limit', 10);
	$count = search_get_entity_count();
	if ($offset == '') { $offset = 0; }
	$newOffset = $offset + $limit;

	$nextRound = $CONFIG->wwwroot . 'action/search/progress?offset=' . $newOffset;
	$content = elgg_echo('search:rebuilding');
//$content .= "<BR>\nProcessing entity: " . $offset;

        $entities = get_entities('', '', 0, '', $limit, $offset);
        foreach ($entities as $entity) {
	    //print 'Processing entity ' . $entity->getGUID() . "<br />\n";
	}


	$reloader = '<script type="text/javascript">
		$(document).ready(function() {
			$("#reindex-progress").load("' . $nextRound . '");
			$("#reindex-progressBar").progressBar("' . $offset . '");
		});
	</script>';
	if ($offset <= $count) {
		$content .=  $reloader;	
	} else {
	    $content = elgg_echo('search:donerebuild');
		$content .= '<script type="text/javascript">
			$(document).ready(function() {
				$("#reindex-working").slideUp("slow");
				$("#reindex-button").slideDown("slow");
				$("#reindex-progressBar").progressBar("' . $count . '");
			});	</script>';
	}
	
	print $content;
	exit;
?>
