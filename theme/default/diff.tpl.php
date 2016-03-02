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
// $Id: diff.tpl.php,v 1.5 2005/07/29 14:34:34 pmjones Exp $
//
// =====================================================================

?>

<?php include $this->loadTemplate('header.tpl.php') ?>

<h1><?php $this->plugin('ahref', "index.php?area={$this->yawiki->area}&page={$this->yawiki->page}", "{$this->yawiki->area}:{$this->yawiki->page}") ?></h1>
<h2>Differences</h2>
<p>[ <?php $this->plugin('ahref', $this->yawiki->nav['history'], 'Back to History') ?> ]</p>

<table border="0" cellspacing="1" cellpadding="4">
	<tr>
		<th>
			From <?php $this->_($this->info['from']['username']) ?>
			<br /><?php $this->plugin('dateformat', $this->info['from']['dt'], Yawp::getConfElem('yawiki', 'dateformat', '%c')) ?>
		</th>
		<th>Action</th>
		<th>
			To <?php $this->_($this->info['to']['username']) ?>
			<br /><?php $this->plugin('dateformat', $this->info['to']['dt'], Yawp::getConfElem('yawiki', 'dateformat', '%c')) ?>
		</th>
	</tr>
	<?php foreach ($this->diff as $block): ?>
	<tr>
		<td valign="top"><?php
			if (is_array($block->orig)) {
				echo '<pre>';
				echo wordwrap(
					implode("\n", $block->orig),
					Yawp::getConfElem('yawiki', 'diff_wrap', 40)
				);
				echo '</pre>';
			}
		?></pre></td>
		<td valign="top" align="center"><?php
			echo "<br />" . substr(get_class($block), 13);
		?></td>
		<td valign="top"><?php
			if (is_array($block->final)) {
				echo '<pre>';
				echo wordwrap(
					implode("\n", $block->final),
					Yawp::getConfElem('yawiki', 'diff_wrap', 40)
				);
				echo '</pre>';
			}
		?></td>
	</tr>
	<?php endforeach; ?>
</table>

<?php include $this->loadTemplate('footer.tpl.php') ?>