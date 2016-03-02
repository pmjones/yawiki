<?php

// =====================================================================
// 
// This program is part of Yet Another Wiki (Yawiki).  For more
// information, please visit http://yawiki.com/ at your convenience.
// 
// Copyright (C) 2004 Paul M. Jones. <pmjones@ciaweb.net>
// 
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or (at
// your option) any later version.
// 
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
// General Public License for more details.
//
// http://www.gnu.org/copyleft/gpl.html
//
// ---------------------------------------------------------------------
//
// $Id: index.php,v 1.6 2005/07/10 18:11:23 pmjones Exp $
//
// =====================================================================

require_once 'Yawp.php';
Yawp::start('/Users/pmjones/etc/Yawp.conf.php');


// is the user OK to view this page?
if (! $yawiki->userCanViewPage() ) {

	// user may not view pages
	$msg = 'You are not authorized to view this page.';
	$Savant->assign('error', array($msg));
	$Savant->display('error.tpl.php');
	
} else {
	
	// user may view pages.  does the requested page exist?
	$found = in_array(
		$yawiki->page,
		$yawiki->parse->getRenderConf('xhtml', 'wikilink', 'pages')
	);
	
	if (! $found) {
	
		// page not found
		$msg = array(
			"Area '" . htmlspecialchars($yawiki->area) . "' page named '" .
			htmlspecialchars($yawiki->page) . "' (version '" .
			htmlspecialchars($yawiki->dt) . "') does not exist."
		);
		
		// is the user allowed to edit pages in the entire area?
		if ($yawiki->acl->pageEdit(Yawp::authUsername(), $yawiki->area, '*')) {
			// add a link for creating pages.
			// suggested by Lukas Smith.
			$msg[] = '<a href="edit.php?area=' . $yawiki->area .
				'&page=' . $yawiki->page.'">Create it.</a>';
		}
		
		// output errors
		$Savant->assign('error', $msg);
		$Savant->display('error.tpl.php');
		
	} else {
		
		// found the page.  what kind of view?
		switch (strtolower(Yawp::getGet('view'))) {
		
		case 'links':
			$Savant->assign(
				'inbound',
				$yawiki->links->inbound($yawiki->area, $yawiki->page)
			);
			$Savant->assign(
				'outbound',
				$yawiki->links->outbound($yawiki->area, $yawiki->page)
			);
			$Savant->display('links.tpl.php');
			break;
		
		case 'history':
			$Savant->assign('list', $yawiki->getVersionList());
			$Savant->display('history.tpl.php');
			break;
		
		case 'diff':
			
			// get the diff data array
			$data = $yawiki->getDiff(
				Yawp::getGet('from'),
				Yawp::getGet('to')
			);
			
			// assigns 'info' and 'diff' as arrays
			$Savant->assign($data);
			$Savant->display('diff.tpl.php');
			break;
		
		case 'source':
			$item = $yawiki->getPage();
			header("Content-Type: text/plain");
			echo $item['body'];
			break;
		
		default:
			
			// allow for comments processing
			if ($yawiki->getAreaInfo($yawiki->area, 'comments')) {
				include 'lib/comments.inc.php';
			}
			
			// get the page item (this transforms HTML on the fly as
			// needed) and display the page.
			$item = $yawiki->getPage();
			$Savant->assign($item);
			
			// find out if the page is in edit lock ...
			$userIsEditing = $yawiki->locks->authUsername(
				$yawiki->area, $yawiki->page);
			
			// is it locked by the current user?
			if ($userIsEditing == $yawiki->username) {
				// yes, treat it as  not locked.
				$userIsEditing = false;
			}
			$Savant->assign('userIsEditing', $userIsEditing);
			
			// display the page
			$result = $Savant->display('index.tpl.php');
			
			// log the page hit
			$yawiki->hits->log(
				$item['area'],
				$item['page'],
				$item['dt'],
				$yawiki->username
			);
			
			break;
		
		}
	}
}

Yawp::stop();
?>