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
// $Id: area_list.php,v 1.3 2005/07/10 18:11:23 pmjones Exp $
//
// =====================================================================

require_once 'Yawp.php';
Yawp::start();


require_once 'HTML/QuickForm.php';

// security check
if (! $yawiki->userIsWikiAdmin()) {
	$err = "You are not signed in as the wiki administrator.";
	$Savant->assign('error', array($err));
	$Savant->display('error.tpl.php');
	Yawp::stop();
	die();
}

// acceptable submissions
$submit = array(
	'save' => Yawp::getConfElem('yawiki', 'op_save', 'Save'),
	'delete' => Yawp::getConfElem('yawiki', 'op_delete', 'Delete')
);
	
	
$op = Yawp::getPost('op');
$form =& $yawiki->areas->getForm(array('name'));
$form->addElement('submit', 'op', $submit['save']);

$area = Yawp::getPost('name');

// OP: delete an area, but only if not the default (Main) area
if ($op == $submit['delete'] &&
	$area != Yawp::getConfElem('yawiki', 'default_area', 'Main')) {
	
	$yawiki->areas->delete($area);
	$yawiki->acl->deleteArea($area);
	$yawiki->links->deleteArea($area);
	$yawiki->search->deleteArea($area);
	$yawiki->store->deleteArea($area);
	header('Location: ' . htmlspecialchars(strip_tags($_SERVER['REQUEST_URI'])));
	exit();
}


// OP: Save new area
if ($op == $submit['save'] && $form->validate()) {
	
	$area = Yawp::getPost('name');
	$page = Yawp::getConfElem('yawiki', 'default_page', 'HomePage');
	
	if (in_array($area, array_keys($yawiki->areaInfo))) {
		$err = "The area name '$area' already exists in the wiki.";
		$Savant->assign('error', array($err));
		$Savant->display('error.tpl.php');
		Yawp::stop();
		die();
	}
	
	
	// add the area
	$yawiki->areas->insert(
		array(
			'name' => $area,
			'title' => $area
		)
	);
	
	// add its HomePage
	$yawiki->store->insert(
		array(
			'area' => $area,
			'page' => $page,
			'body' => "Welcome to $area:$page!",
			'dt'   => date('Y-m-d H:i:s'),
			'username' => $yawiki->username
		)
	);
	
	// add its AreaMap
	$yawiki->store->insert(
		array(
			'area' => $area,
			'page' => 'AreaMap',
			'body' => $page,
			'dt'   => date('Y-m-d H:i:s'),
			'username' => $yawiki->username
		)
	);
	
	// forcibly reload the areaInfo
	$yawiki->loadAreaInfo(true);
}

// add the i18n submit values
$Savant->assign('submit', $submit);

// get the current list of areas
$Savant->assign('list', $yawiki->areaInfo);

// add the form
$Savant->assignRef('form', $form);

// display
$Savant->display('area_list.tpl.php');

Yawp::stop();
?>