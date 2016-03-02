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
// $Id: area_pages.tpl.php,v 1.7 2005/07/29 14:34:34 pmjones Exp $
//
// =====================================================================

?>

<?php include $this->loadTemplate('header.tpl.php') ?>

<h1>Pages in <?php $this->_($this->yawiki->area) ?></h1>

<table class="admin">
	<tr class="admin">
		<th class="admin">Page Name</th>
		<th class="admin">On AreaMap?</th>
		<th class="admin">View History</th>
		<th class="admin">View Links</th>
		<th class="admin">Locked By</th>
		<th class="admin">Delete?</th>
	</tr>
	<?php foreach ($this->list as $key => $val): ?>
	<?php $link = $this->yawiki->getViewLink($this->yawiki->area, $val['page']); ?>
	<tr class="admin">
		<td class="admin"><?php $this->plugin('ahref', $link, $val['page']) ?></td>
		<td class="admin"><?php $this->_($val['map'] ? 'yes' : 'NO') ?></td>
		<td class="admin"><?php $this->plugin('ahref', "$link&view=history", 'History') ?></td>
		<td class="admin"><?php $this->plugin('ahref', "$link&view=links", 'Links') ?></td>
		<td class="admin"><?php $this->_($val['lockedby']) ?>
			<?php if ($val['lockedby'] != '-'): ?>
				<form action="<?php $this->_($_SERVER['REQUEST_URI']) ?>" method="post">
					<?php $this->plugin('input', 'hidden', 'page', $val['page']) ?>
					<?php $this->plugin(
						'input', 'submit', 'op', $this->submit['unlock'], array(
							'onClick' => "return confirm('This will unlock \"{$val['page']}\"');"
						)
					); ?>
				</form>
			<?php endif ?>
		</td>
		<td class="admin">
			<?php if ($val['page'] != 'AreaMap' && $val['page'] != $this->yawiki->conf['default_page']): ?>
				<form action="<?php $this->_($_SERVER['REQUEST_URI']) ?>" method="post">
					<?php $this->plugin('input', 'hidden', 'page', $val['page']) ?>
					<?php $this->plugin(
						'input', 'submit', 'op', $this->submit['delete'], array(
							'onClick' => "return confirm('This will permanently delete \"{$val['page']}\" and its entire history.');"
						)
					); ?>
				</form>
			<?php endif ?>
		</td>
	</tr>
	<?php endforeach ?>
</table>

<?php include $this->loadTemplate('footer.tpl.php') ?>