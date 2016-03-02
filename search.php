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
// $Id: search.php,v 1.1 2004/10/01 18:28:50 pmjones Exp $
//
// =====================================================================

require_once 'Yawp.php';
Yawp::start();

// ---------------------------------------------------------------------
// 
// build the search form
// 

require_once 'HTML/QuickForm.php';
$form =& new HTML_QuickForm(null, 'get');

// words to search for
$form->addElement('text', 'qword', 'Look for:');
$form->addRule('qword', 'Please enter one or more words to search for.', 'required');

// how to search (any, all, exact)
$form->addElement('select', 'qtype', '',
	array(
		'any' => 'Any of the words',
		'exact' => 'The exact phrase',
		'all' => 'All of the words'
	)
);

// the list of allowed areas to search.
// if only one area is allowed, don't show any.
if (! $yawiki->conf['only_area']) {

	$areaList = array_keys($yawiki->areaInfo);
	foreach ($areaList as $key => $val) {
		$areaList[$val] = $val;
		unset($areaList[$key]);
	}
	
	$form->addElement('select', 'qarea', 'In areas:', $areaList,
		array(
			'multiple' => 'multiple',
			'size' => (count($areaList) > 9) ? '9' : count($areaList)
		)
	);
}

// submit button
$form->addElement('submit', 'op', 'Search');


// ---------------------------------------------------------------------
// 
// perform a search, if requested
// 

if (Yawp::getGet('op') == 'Search' && $form->validate()) {
	$result = $yawiki->getSearchResult(
		Yawp::getGet('qword'),
		Yawp::getGet('qarea'),
		Yawp::getGet('qtype')
	);
} else {
	$result = null;
}

// ---------------------------------------------------------------------
// 
// display the form and results
// 

$Savant->assign('result', $result);
$Savant->assignRef('form', $form);
$Savant->display('search.tpl.php');

Yawp::stop();
?>