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
// $Id: area_settings.php,v 1.2 2005/01/18 15:19:07 pmjones Exp $
//
// =====================================================================

require_once 'Yawp.php';
Yawp::start();

if (! $yawiki->userIsAreaAdmin()) {
	
	$msg = 'You are not authorized to view this page.';
	$Savant->assign('error', array($msg));
	$Savant->display('error.tpl.php');
	
} else {
	
	// acceptable submissions
	$submit = array(
		'save' => Yawp::getConfElem('yawiki', 'op_save', 'Save')
	);
	
	// get the requested operation, if any
	$op = Yawp::getPost('op');
	
	// get the area item from the database, remove the
	// name and map because we should not edit them
	$item = $yawiki->areas->getItem($yawiki->area);
	unset($item['name']);
	
	// create the editing form, pre-populated with the
	// database entry, or the POST vars if they exist
	$form =& $yawiki->areas->getForm($item, 'edit');
	$form->addElement('submit', 'op', $submit['save']);
	
	// attempt to save changes
	if ($form->validate() && $op == $submit['save']) {
	
		// save the changes
		$data = Yawp::getPost('edit');
		$result = $yawiki->areas->update($data, $yawiki->area);
		
		// re-do the form so that it populates from the database,
		// not the POST vars
		unset($_POST['edit']);
		
		// get the area item from the database, remove the
		// name and map because we should not edit them
		$item = $yawiki->areas->getItem($yawiki->area);
		unset($item['name']);
		
		$form =& $yawiki->areas->getForm($item, 'edit');
		$form->addElement('submit', 'op', $submit['save']);
		
		// forcibly reload the area info
		$yawiki->loadAreaInfo(true);
		
	}
	
	$Savant->assignRef('form', $form);
	$Savant->display('area_settings.tpl.php');
}

Yawp::stop();
?>