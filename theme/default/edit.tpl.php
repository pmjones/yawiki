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
// $Id: edit.tpl.php,v 1.8 2006/02/21 04:32:00 delatbabel Exp $
//
// =====================================================================

?>

<?php include $this->loadTemplate('header.tpl.php') ?>

<div style="padding: 8px; border: 1px solid black; background: #dddddd;">

	<p><strong><span style="color: red;">Editing</span>
	<?php $this->_($this->yawiki->area . ':' . $this->yawiki->page) ?></strong></p>
	
	<?php if (isset($this->captcha_error) && $this->captcha_error) { ?>
		<strong><span style="color: red;">You must solve the CAPTCHA properly!</span></strong>
	<?php } ?>

	<p>You are <strong><?php $this->_($this->yawiki->username) ?></strong>;
	you have an edit lock on this page for the next
	<?php $this->_($this->lock_timeout) ?> minutes.</p>
	
	<?php
		$this->plugin('form', 'set', 'class', null);
		$this->plugin('form', 'set', 'float', null);
		$this->plugin('form', 'set', 'clear', null);
		$this->plugin('form', 'start');
		$this->plugin('form', 'hidden', 'op', 'Preview'); // the default action
		$this->plugin('form', 'text', 'title', $this->title, 'Title', 'size="80" maxlength="255"');
		$this->plugin('form', 'textarea', 'body', $this->body, 'Body', 'rows="24" cols="80"');
		$this->plugin('form', 'text', 'note', $this->note, 'Note', 'size="80" maxlength="255"');
if (!Yawp::authUsername()) {
		$this->plugin('form', 'text', 'captcha', '', YAWP::generateCaptcha(), 'size="80" maxlength="255"');
}
		$this->plugin('form', 'group', 'start');
		$this->plugin('form', 'submit', 'op', $this->submit['preview']);
		$this->plugin('form', 'submit', 'op', $this->submit['cancel']);
		$this->plugin('form', 'submit', 'op', $this->submit['save']);
		$this->plugin('form', 'group', 'end');
		$this->plugin('form', 'group', 'start', 'Revert...');
		$this->plugin('form', 'select', 'dt', $this->dt, null, $this->dt_options);
		$this->plugin('form', 'submit', 'op', $this->submit['revert']);
		$this->plugin('form', 'group', 'end');
		$this->plugin('form', 'end');
	?>

</div>
	
<?php if (trim($this->title) != '') {
	echo "<h1>";
	$this->_($this->title);
	echo "</h1>";
} ?>

<?php echo $this->html ?>


<?php include $this->loadTemplate('footer.tpl.php') ?>