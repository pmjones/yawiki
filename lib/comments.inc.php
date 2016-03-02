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
// $Id: comments.inc.php,v 1.7 2006/02/21 04:31:43 delatbabel Exp $
//
// =====================================================================


// are comments turned on in the area settings?
if (! $yawiki->getAreaInfo($yawiki->area, 'comments')) {
	return;
}

require_once 'lib/yacs.class.php';

$yacs =& new yacs();

// allowed submit values
$submit = array(
	'save' => Yawp::getConfElem('yawiki', 'op_save', 'Save'),
	'delete' => Yawp::getConfElem('yawiki', 'op_delete', 'Delete')
);

if (PEAR::isError($yacs)) {
	Yawp::dump($yacs, 'yacs error');
	die();
}

$op = Yawp::getPost('op');
$comment = Yawp::getPost('comment');
$parent = $yawiki->area . ':' . $yawiki->page;
$request_uri = strip_tags($_SERVER['REQUEST_URI']);
// ADD COMMENTS
if ($op == $submit['save'] && trim($comment['body']) != '' && YAWP::checkCaptcha($comment['captcha'])) {
	$comment['username'] = $comment['email'];
	$comment['parent'] = $parent;
	$comment['subj'] = '';
	
	// blank email address?
	if (trim($comment['email']) == '') {
		$comment['email'] = 'anonymous';
		$comment['username'] = 'anonymous';
	} else {
		// make 'username' the obfuscated email address
		$comment['username'] = str_replace('@', ' [AT] ',  $comment['username']);
		$comment['username'] = str_replace('.', ' [DOT] ', $comment['username']);
	}
	
	// insert the comment
	unset($comment['captcha']);
	$result = $yacs->insert($comment);
	
	// send a notification email?
	$email = $yawiki->getAreaInfo($yawiki->area, 'email');
	
	if ($email) {
	
		$subj = $yawiki->getAreaInfo($yawiki->area, 'email_subj');
		if (! $subj) {
			$subj = '[New comment at http://' . HTTP_HOST . HREF_BASE . ']';
		}
		
		$subj .= ' ' . $yawiki->area . ':' . $yawiki->page;
		
		$body = 'http://' . HTTP_HOST . $request_uri . "\n\n";
			
		$body .= $comment['email'] . " writes:\n\n";
		$body .= $comment['body'] . "\n\n-- ";
		
		mail($email, $subj, $body);
		
	}
	
	// redirect back to the page without the comment posting.
	// this helps avoid the back/refresh double-posting of comments,
	// pointed out by Noah Botimer.
	header('Location: ' . $request_uri);
	exit();
}

// DELETE COMMENT
if ($op == $submit['delete'] && $yawiki->userIsAreaAdmin()) {
	$id = Yawp::getPost('comment_id');
	$yacs->delete($id);
}

// SHOW COMMENTS

// build a comment form and assign it
$fields = array('email', 'body');
$form =& $yacs->getForm($fields, 'comment');
$form->addElement('text', 'comment[captcha]', YAWP::generateCaptcha(), array('size' => 30, 'maxlength' => 255));
$form->addRule('comment[captcha]', 'Please solve the little math problem', 'required');
$form->addElement('submit', 'op', $submit['save']);
$Savant->assignRef('comment_form', $form);

// assign all current comments
$result = $yacs->getListResult($parent, 'dt DESC');
$Savant->assignRef('comments', $result);

// show the delete button on comments?
$Savant->assign('showDelete', $yawiki->userIsPageAdmin());

// show the allowed submit values
$Savant->assign('submit', $submit);

// done!  the calling script should now output its template,
// and the comment assignments will propogate through it.
?>