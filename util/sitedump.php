<?php

/*
dumps the wiki source of all pages into one file,
adjusts headings on the fly according to the area map.
*/

require_once 'Yawp.php';
Yawp::start();

if (! $yawiki->userIsWikiAdmin()) {
	die ('Not a wiki admin.');
}

// $filename = '/tmp/' . uniqid(md5($yawiki->area . time()));

//$fh = fopen($filename, 'w');

$map = $yawiki->store->getPage($yawiki->area, 'AreaMap');

$lines = explode("\n", $map['body']);

header('Content-Type: text/plain');

echo '+ ' . $yawiki->area . "\n\n";

foreach ($lines as $page) {
	
	// count number of spaces at the beginning of the line
	// and set the level based on the count
	preg_match('/^( {0,})(.*)/', $page, $matches);
	$level = strlen($matches[1]) + 2;
	$pad = str_pad('', $level, '+');
	
	// trim off the depth-level spaces
	$page = trim($page);
	
	// remove the title portion
	$pos = strpos($page, ' ');
	$page = substr($page, 0, $pos);
	
	$info = $yawiki->store->getPage($yawiki->area, $page);
	
	$body = "\n\n" . trim($info['body']) . "\n\n";
	
	$body = preg_replace('/\n(\+)+ /', "\n$pad" . '\1 ', $body);
	
	if (! empty($info['title'])) {
		$body = "\n\n$pad " . $info['title'] . $body;
	}
	
	echo $body;
}

//fclose($fh);

/*
open tmp file
get area map as-is
loop through map lines
	count leading spaces, get page name
	load page into memory from DB
	add newline to head and tail
	for every leading space, add 1 to each heading tag (2 spaces means + becomes +++, etc)
	append converted to tmp file
close tmp file, send to browser
delete tmp file
*/

Yawp::stop();

?>