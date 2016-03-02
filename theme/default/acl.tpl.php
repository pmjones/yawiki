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
// $Id: acl.tpl.php,v 1.4 2005/07/29 14:34:34 pmjones Exp $
//
// =====================================================================

?>

<?php include $this->loadTemplate('header.tpl.php') ?>

<h1>Access Control List</h1>

<table class="admin">
	<tr>
		<th class="admin">seq</th>
		<th class="admin">flag</th>
		<th class="admin">username</th>
		<th class="admin">priv</th>
		<th class="admin">area</th>
		<th class="admin">page</th>
		<th class="admin">action</th>
	</tr>
	<?php foreach ($this->list as $key => $val): $color = ($val['flag']) ? 'black' : 'red'; ?>
	<tr>
		<td class="admin" style="color: <?php echo $color ?>;"><?php $this->_($val['seq']) ?></td>
		<td class="admin" style="color: <?php echo $color ?>;"><?php $this->_($val['flag'] ? 'allow' : 'deny') ?></td>
		<td class="admin" style="color: <?php echo $color ?>;"><?php $this->_($val['username']) ?></td>
		<td class="admin" style="color: <?php echo $color ?>;"><?php $this->_($val['priv']) ?></td>
		<td class="admin" style="color: <?php echo $color ?>;"><?php $this->_($val['area']) ?></td>
		<td class="admin" style="color: <?php echo $color ?>;"><?php $this->_($val['page']) ?></td>
		<td class="admin">
			<form method="post" action="<?php $this->_($_SERVER['REQUEST_URI']) ?>">
				<input type="hidden" name="del_id" value="<?php $this->_($val['id']) ?>" />
				<input type="submit" name="op" value="<?php $this->_($this->submit['delete']) ?>" onClick="return confirm('Are you sure you want to delete this control?');" />
			</form>
		</td>
	</tr>
	<?php endforeach ?>
</table>

<p>Notes:</p>

<ul>
	<li>The list covers all areas in this wiki.</li>
	<li>All privilege controls are denied until explicitly allowed.</li>
	<li>All privilege controls are processed in sequence order; the last one that applies is the one used.</li>
	<li>In <tt>username</tt>, the wildcard <tt>+</tt> means any authenticated user, and the wildcard <tt>*</tt> means all users (including anonymous users).</li>
	<li>In <tt>area</tt> (or <tt>page</tt>), the wildcard <tt>*</tt> means any area (or page).</li>
</ul>

<fieldset>
<legend><b>Add New Control</b></legend>
<?php $this->form->display() ?>
</fieldset>

<?php include $this->loadTemplate('footer.tpl.php') ?>