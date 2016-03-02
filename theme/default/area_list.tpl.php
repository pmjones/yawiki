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
// $Id: area_list.tpl.php,v 1.4 2005/07/29 14:34:34 pmjones Exp $
//
// =====================================================================

?>

<?php include $this->loadTemplate('header.tpl.php') ?>

<h1>Area List</h1>

<table class="admin">
	<tr class="admin">
		<th class="admin">Name</th>
		<th class="admin">Title</th>
		<th class="admin">Tagline</th>
		<th class="admin">Comments?</th>
		<th class="admin">Comments Email</th>
		<th class="admin">Caching HTML?</th>
		<th class="admin">Delete?</th>
	</tr>
	<?php foreach ($this->list as $key => $val): ?>
			<tr class="admin">
				<td class="admin">
					<?php $this->plugin('ahref', "area_pages.php?area={$val['name']}", $val['name']) ?>&nbsp;<span style="font-size: 80%">[<?php $this->plugin('ahref', "area_settings.php?area={$val['name']}", 'Settings') ?>]</span>
				</td>
				<td class="admin"><?php $this->_($val['title']) ?></td>
				<td class="admin"><?php $this->_($val['tagline']) ?></td>
				<td class="admin"><?php $this->_($val['comments'] ? 'yes' : 'no') ?></td>
				<td class="admin"><?php $this->_($val['email']) ?></td>
				<td class="admin"><?php $this->_($val['cache_html'] ? 'yes' : 'no')  ?></td>
				<td class="admin">
					<?php if ($val['name'] != Yawp::getConfElem('yawiki', 'default_area', 'Main')) { ?>
						<form action="<?php $this->_($_SERVER['REQUEST_URI']) ?>" method="post">
							<?php $this->plugin('input', 'hidden', 'name', $val['name']) ?>
							<?php $this->plugin(
								'input', 'submit', 'op', $this->submit['delete'], array(
									'onClick' => "return confirm('This will permanently delete the entire \"{$val['name']}\" area and all its pages.');"
								)
							); ?>
						</form>
					<?php } else { echo '&nbsp'; } ?>
				</td>
			</tr>
	<?php endforeach ?>
</table>

<br />

<?php if ($this->yawiki->userIsWikiAdmin()): ?>
	<p>Add a new area:</p>
	<?php $this->form->display() ?>
<?php endif ?>

<?php include $this->loadTemplate('footer.tpl.php') ?>