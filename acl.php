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
// $Id: acl.php,v 1.3 2005/01/18 15:19:07 pmjones Exp $
//
// =====================================================================

require_once 'Yawp.php';
Yawp::start();

// security check
if (! $yawiki->userIsWikiAdmin()) {
	$err = "You are not signed in as a system administrator.";
	$Savant->assign('error', array($err));
	$Savant->display('error.tpl.php');
	die();
}

$op = Yawp::getPost('op');

$submit = array(
	'save'   => Yawp::getConfElem('yawiki', 'op_save',   'Save'),
	'delete' => Yawp::getConfElem('yawiki', 'op_delete', 'Delete')
);
	
// OP: Save new control
if ($op == $submit['save']) {
	$add = Yawp::getPost('add');
	$yawiki->acl->recast($add);
	$result = $yawiki->acl->insert($add);
}

// OP: delete control
if ($op == $submit['delete']) {
	$id = Yawp::getPost('del_id');
	$result = $yawiki->acl->delete($id);
}

// get the current list of privs
$Savant->assign('list', $yawiki->acl->getList());

// force a selection list on the yawiki_acl class
// 'area' column for QuickForm
$area_names = array('' => '', '*' => '*');
$tmp = $yawiki->areas->getNames();
foreach ($tmp as $val) {
	$area_names[$val] = $val;
}

// force the DB_Table object to validate against our list of names
$yawiki->acl->col['area']['qf_vals'] = $area_names;

// build the 'new item' form
$form =& $yawiki->acl->getForm(null, 'add');
$form->addElement('submit', 'op', $submit['save']);
$Savant->assignRef('form', $form);
$Savant->assign('submit', $submit); // i18n submit-button strings

// display
$Savant->display('acl.tpl.php');

Yawp::stop();
?>