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
// $Id: auth.tpl.php,v 1.6 2005/07/29 14:34:34 pmjones Exp $
//
// =====================================================================

?>

<script language="JavaScript">
function createNewPage(link) {
	var newpage = prompt("New page name?");
	if (newpage == null || newpage == "") return false;
	link.href = "edit.php?area=<?php $this->_($this->yawiki->area) ?>&page="+newpage;
	return true;
}
</script>

<?php if (! is_null(Yawp::authUsername())): ?>

	<?php if (Yawp::authUsername()): ?>
	
		<p>Signed In As<br /><b><?php $this->_(Yawp::authUsername()) ?></b></p>
		<form action="<?php $this->_($_SERVER['REQUEST_URI']) ?>" method="post">
			<input type="hidden" name="LOGOUT" />
			<input type="submit" value="Sign Out" />
		</form>
		
	<?php else: ?>

		<form method="post" action="<?php $this->_($_SERVER['REQUEST_URI']) ?>">
			<input type="hidden" name="LOGIN" />
			<p>Username:<br /><input type="text" name="username" size="10" /></p>
			<p>Password:<br /><input type="password" name="password" size="10" /></p>
			<p><input type="submit" value="Sign In" /></p>
			
			<?php if (Yawp::getAuthErr()): ?>
				<p><span style="color: red;">
					<?php $this->_(Yawp::getConfElem('Auth', (string) Yawp::clearAuthErr(), 'Authentication error; please sign in again.')) ?>
				</span></p>
			<?php endif; ?>
		</form>

	<?php endif; ?>
	
<?php endif; ?>

<?php
	// the "if userIsEditing" bit refers to the fact that this might not
	// be the index page.
	if ($this->yawiki->isContentPage &&
		$this->yawiki->userCanEditPage() &&
		isset($this->userIsEditing)): ?>
	<p><?php
		if ($this->userIsEditing) $this->_("Edit ($this->userIsEditing)");
		else $this->plugin('ahref', $this->yawiki->nav['edit'], 'Edit This Page')
	?></p>
<?php endif; ?>

<?php if ($this->yawiki->acl->pageEdit($this->yawiki->username, $this->yawiki->area, '*')): ?>
	<p><a href="#" onClick="return createNewPage(this)"; >Create New Page</a></p>
<?php endif; ?>

<?php if ($this->yawiki->userIsAreaAdmin()): ?>
	<hr />
	<p><b>Area Administrator</b></p>
	<p><?php $this->plugin('ahref', 'area_pages.php?area=' . $this->yawiki->area, 'Pages') ?></p>
	<p><?php $this->plugin('ahref', 'area_settings.php?area=' . $this->yawiki->area, 'Settings') ?></p>
<?php endif ?>

<?php if ($this->yawiki->userIsWikiAdmin()): ?>
	<hr />
	<p><b>Wiki Administrator</b></p>
	<p><?php $this->plugin('ahref', 'acl.php', 'Access Control List') ?></p>
	<?php if (! Yawp::getConfElem('yawiki', 'only_area')): ?>
		<p><?php $this->plugin('ahref', 'area_list.php', 'Area List') ?></p>
	<?php endif ?>
<?php endif ?>