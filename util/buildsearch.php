<?php

// re-build the search table as needed

require_once 'Yawp.php';
Yawp::start();

$Savant->display('header.tpl.php');

if ($yawiki->userIsWikiAdmin()) {
	
	echo "<p>Re-creating the search table from current page list...</p>\n<p>";
	
	// set up the yawiki parser
	$yawiki->parse->disableRule('Htmlentities');
	
	// loop through each area
	foreach ($yawiki->store->getAreaList() as $area) {
	
		// loop through each page
		foreach ($yawiki->store->getPageList($area) as $page) {
			
			// get the page, convert to plain text, store in search table
			$data = $yawiki->store->getPage($area, $page);
			
			// delete any current instance of the search entry
			$yawiki->search->delete($area, $page);
			
			// insert the new search entry
			$yawiki->search->insert(
				array(
					'area' => $area,
					'page' => $page,
					'dt'   => $data['dt'],
					'title' => $data['title'],
					'body' => $yawiki->parse->transform($data['body'], 'Plain')
				)
			);
			
			echo "$area:$page<br />\n";
			flush();
		}
	}
	
	echo "</p>\n<p>Done.</p>";

} else {

	echo "You are not signed in as a wiki admin.";
	
}

$Savant->display('footer.tpl.php');

Yawp::stop();
?>