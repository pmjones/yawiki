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
// $Id: edit.php,v 1.7 2006/02/21 04:30:35 delatbabel Exp $
//
// =====================================================================

require_once 'Yawp.php';
Yawp::start();


// -----------------------------------------------------------------
//
// preliminary checks
//

$err = array();

if (trim($yawiki->area) == '') {
	$err[] = 'No area selected.';
}

if (trim($yawiki->page) == '') {
	$err[] = 'No page selected.';
}

if (! $yawiki->userCanEditPage()) {

	$err[] = 'You are not allowed to edit this page.';
	
} else {
	
	// is the page locked by another user?
	$tmp = $yawiki->locks->authUsername($yawiki->area, $yawiki->page);
	if (($tmp) && $tmp != $yawiki->username) {
		$err[] = "This page is being edited by $tmp.";
	}
}


if (count($err) > 0) {
	$Savant->assign('error', $err);
	$Savant->display('error.tpl.php');
	Yawp::stop();
	die();
}


// figure out if the page exists or not
$pageExists = in_array(
	$yawiki->page,
	$yawiki->parse->getRenderConf('Xhtml', 'Wikilink', 'pages')
);


// -----------------------------------------------------------------
//
// operations
//
$op = Yawp::getPost('op');

$submit = array(
	'cancel'  => Yawp::getConfElem('yawiki', 'op_cancel',  'Cancel'),
	'save'    => Yawp::getConfElem('yawiki', 'op_save',    'Save'),
	'preview' => Yawp::getConfElem('yawiki', 'op_preview', 'Preview'),
	'revert'  => Yawp::getConfElem('yawiki', 'op_revert',  'Revert')
);
	
switch ($op) {

case $submit['cancel']:
	// Cancel the edit; unlock the page and redirect to view
	$yawiki->locks->release($yawiki->area, $yawiki->page);
	$href = $yawiki->parse->getRenderConf('xhtml', 'wikilink', 'view_url');
	header("Location: " . sprintf($href, $yawiki->page));
	exit;
	break;

case $submit['save']:

	if (!Yawp::authUsername() && !YAWP::checkCaptcha(Yawp::getPost('captcha', ''))) {
		$op = $submit['preview'];
		$item['captcha_error'] = true;
	} else {
		// the save happens in these stages.
		// 0. if new page, clear cached HTML for other pages referring to this one
		// 1. insert the new page data in storage
		// 2. update the links
		// 3. update the searchable text
		
		// STEP 0:
		// clear the cached html for pages that refer to any newly-created
		// page. this forces those pages to re-generate HTML, clearing the
		// "create-this-page" link.
		$write = !$pageExists;
		if (! $pageExists) {
			$list = $yawiki->links->inbound($yawiki->area, $yawiki->page);
			if (count($list) > 0) {
				$yawiki->store->clearHtmlBatch($list);
			}
		} else {
			$item = $yawiki->getPage();
			if ($item['title'] !== Yawp::getPost('title', '')
				|| $item['body'] !== Yawp::getPost('body', '')
			) {
				$write = true;
			}
		}

		if ($write) {
		// STEP 1: Save the edit
			$yawiki->store->insert(
				array(
					'area' => $yawiki->area,
					'page' => $yawiki->page,
					'title' => Yawp::getPost('title', ''),
					'body' => Yawp::getPost('body', ''),
					'note' => Yawp::getPost('note', ''),
					'dt'   => date('Y-m-d H:i:s'),
					'username' => $yawiki->username
				)
			);
			
			// STEP 2: Update the links
			
			// get the parsed outbound links
			$yawiki->parse->parse(Yawp::getPost('body'));
			$tmp = $yawiki->parse->getTokens(array('Wikilink', 'Interwiki', 'Freelink'));
			
			$targets = array();
			
			// we're only going to track local namearea outbounds
			$areas = array_keys($yawiki->getAreaInfo($yawiki->area));
			
			foreach ($tmp as $key => $val) {
				
				$rule = $val[0];
				$opt  = $val[1];
				
				if ($rule == 'Wikilink' || $rule == 'Freelink') {
					$targets[] = array(
						'area' => $yawiki->area,
						'page' => $opt['page']
					);
				}
				
				if ($rule == 'Interwiki' && in_array($opt['site'], $areas)) {
					$targets[] = array(
						'area' => $opt['site'],
						'page' => $opt['page']
					);
				}
			}
			
			// update the outbound links
			$yawiki->links->refresh($yawiki->area, $yawiki->page, $targets);
			
			
			// STEP 3: update the searchable text
			$yawiki->parse->disableRule('Htmlentities');
			$yawiki->search->delete($yawiki->area, $yawiki->page);
			$yawiki->search->insert(
				array(
					'area' => $yawiki->area,
					'page' => $yawiki->page,
					'title' => Yawp::getPost('title', ''),
					'body' => $yawiki->parse->transform(Yawp::getPost('body'), 'Plain'),
					'dt'   => date('Y-m-d H:i:s'),
				)
			);
		}
		// release the page lock
		$yawiki->locks->release($yawiki->area, $yawiki->page);
		
		// redirect to view the page
		$href = $yawiki->parse->getRenderConf('xhtml', 'wikilink', 'view_url');
		header("Location: " . sprintf($href, $yawiki->page));
		exit;
		break;
	}
case $submit['preview']:
	// Preview the edit; show the edited source
	$item['title'] = Yawp::getPost('title');
	$item['body'] = Yawp::getPost('body');
	$item['note'] = Yawp::getPost('note');
	$item['dt'] = null;
	break;

case $submit['revert']:
	// if $dt is blank, convert it to null (this reverts to
	// the last-saved version).
	$dt = Yawp::getPost('dt');
	if (! $dt) {
		$dt = null;
	}
	
	// get the prior version.
	$item = $yawiki->store->getPage(
		$yawiki->area, $yawiki->page, $dt
	);
	
	// clear the change note.
	$item['note'] = '';
	break;
	
default:
	// Show the unedited source...
	if ($pageExists) {
		// ... from an existing page
		$item = $yawiki->getPage();
		$item['note'] = '';
		$item['dt'] = '';
	} else {
		// page is brand new, use blanks.
		$item = $yawiki->store->getBlankRow();
	}
	
	break;
}


// -----------------------------------------------------------------
//
// display
//

// start or refresh an existing lock...
$yawiki->locks->capture($yawiki->area, $yawiki->page, $yawiki->username);
	
// get the list of past versions as an option list
$dt_options = array('' => '');
foreach ($yawiki->getVersionList() as $key => $val) {
	$dt_options[$val['dt']] = $val['dt'] . ' (' . $val['username'] . ')';
}

// assign vars and display template
$Savant->assign($item);
$Savant->assign('submit', $submit); // the i18n strings for submit buttons
$Savant->assign('html', $yawiki->transform($item['body'], $yawiki->page));
$Savant->assign('lock_timeout', $yawiki->locks->conf['timeout']);
$Savant->assign('dt_options', $dt_options);
$Savant->display('edit.tpl.php');

Yawp::stop();
?>
