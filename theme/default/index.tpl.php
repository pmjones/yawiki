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
// $Id: index.tpl.php,v 1.5 2005/07/29 14:34:34 pmjones Exp $
//
// =====================================================================

?>

<?php include $this->loadTemplate('header.tpl.php') ?>

<?php if (Yawp::getGet('dt')) {
	echo "<p style=\"color: red; text-align: center;\">Previous version dated<br />" .
		$this->splugin('dateformat', $this->dt, Yawp::getConfElem('yawiki', 'dateformat', '%c')) . "</p>\n";
} ?>

<?php if (trim($this->title) != '') {
	echo '<h1>';
	$this->_($this->title);
	echo '</h1>';
} ?>

<?php echo $this->html ?>

<table width="100%" border="0" cellspacing="0" cellpadding="4" style="border: 1px dotted gray; margin-top: 12px;">
	<tr>
		<td valign="top">
			<i><?php $this->_("$this->area:$this->page ($this->username)") ?><br />
			<?php $this->plugin('dateformat', $this->dt, Yawp::getConfElem('yawiki', 'dateformat', '%c')) ?></i>
		</td>
		<td align="right" valign="top">
			[ <?php $this->plugin('ahref', $this->yawiki->nav['links'], 'Links') ?>
			| <?php $this->plugin('ahref', $this->yawiki->nav['source'], 'Source') ?>
			| <?php $this->plugin('ahref', $this->yawiki->nav['history'], 'History') ?>
			<?php if ($this->yawiki->acl->pageView('*', $this->yawiki->area, $this->yawiki->page)): ?>
			| <?php $this->plugin('ahref', $this->yawiki->nav['rss'], 'RSS') ?>
			<?php endif; if ($this->yawiki->userCanEditPage()): ?>
			|
				<?php
					if ($this->userIsEditing) $this->_("Edit ($this->userIsEditing)");
					else $this->plugin('ahref', $this->yawiki->nav['edit'], 'Edit')
				?>
			<?php endif; ?>
			]
		</td>
	</tr>
</table>

<p style="font-size: 80%; text-align: center;">This site powered by <?php $this->plugin('ahref', 'http://yawiki.com/', trim('YaWiki ' . $this->yawiki->apiVersion)) ?>.</p>

<?php if ($this->yawiki->getAreaInfo($this->yawiki->area, 'comments')) {
	include $this->loadTemplate('comments.tpl.php');
} ?>

<?php include $this->loadTemplate('footer.tpl.php') ?>