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
// $Id: area_pages.php,v 1.5 2005/07/10 18:11:23 pmjones Exp $
//
// =====================================================================

require_once 'Yawp.php';
Yawp::start();

if (! $yawiki->userIsAreaAdmin()) {
	
	$msg = 'You are not authorized to view this page.';
	$Savant->assign('error', array($msg));
	$Savant->display('error.tpl.php');
	
} else {
	
	// submissions we accept
	$submit = array(
		'delete' => Yawp::getConfElem('yawiki', 'op_delete', 'Delete'),
		'unlock' => Yawp::getConfElem('yawiki', 'op_unlock', 'Unlock')
	);
	
	$request_uri = htmlspecialchars(strip_tags($_SERVER['REQUEST_URI']));

	// ALLOW PAGE DELETION.
	$op = Yawp::getPost('op');
	if ($op == $submit['delete']) {
	
		$page = Yawp::getPost('page');
		
		// don't allow AreaMap or HomePage to be deleted
		if ($page != 'AreaMap' &&
			$page != $yawiki->conf['default_page']) {
			
			$yawiki->store->delete($yawiki->area, $page);
			$yawiki->search->delete($yawiki->area, $page);
			$yawiki->links->delete($yawiki->area, $page);
			header('Location: ' . $request_uri);
			exit();
		}
			
	}

	// ALLOW PAGE UNLOCKS
	if ($op == $submit['unlock']) {
		$page = Yawp::getPost('page');
		$yawiki->locks->release($yawiki->area, $page);
		header('Location: ' . $request_uri);
		exit();
	}
	
	// DISPLAY THE PAGE LIST.
	$pagelist = $yawiki->parse->getRenderConf('xhtml', 'wikilink', 'pages');
	$maplist = array_keys($yawiki->map[$yawiki->area]['path']);
	$data = array();
	
	foreach ($pagelist as $page) {
		$lockedby = $yawiki->locks->authUsername($yawiki->area, $page);
		if (! $lockedby) {
			$lockedby = "-";
		}
		$data[] = array(
			'page' => $page,
			'map' => (in_array($page, $maplist)),
			'lockedby' => $lockedby
		);
	}
	
	$Savant->assign('submit', $submit);
	$Savant->assign('list', $data);
	$Savant->display('area_pages.tpl.php');
	
}
Yawp::stop();
?>