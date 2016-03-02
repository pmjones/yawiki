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
// $Id: history.tpl.php,v 1.3 2005/07/07 21:05:33 pmjones Exp $
//
// =====================================================================

?>

<?php include $this->loadTemplate('header.tpl.php') ?>

<h1><?php $this->plugin('ahref', $this->yawiki->getViewLink($this->yawiki->area, $this->yawiki->page), "{$this->yawiki->area}:{$this->yawiki->page}") ?></h1>
<h2>History</h2>

<p>To see the differences between two versions...</p>

<ol>
	<li>Pick a "from" version by selecting the radio button for that version.</li>
	<li>Pick a "to" version by clicking on its submit button.</li>
</ol>


<form action="index.php" method="get">

	<?php $this->plugin('input', 'hidden', 'area', $this->yawiki->area) ?>
	<?php $this->plugin('input', 'hidden', 'page', $this->yawiki->page) ?>
	<?php $this->plugin('input', 'hidden', 'view', 'diff') ?>
	
<table border="0" cellspacing="1" cellpadding="4">
		<tr>
			<th valign="top">Version</th>
			<th valign="top">Date-Time (Saved By)<br />Change Note</th>
			<th valign="top">Diff From</th>
			<th valign="top">Diff To</th>
		</tr>
		<?php $i = count($this->list) - 1; foreach ($this->list as $key => $val): ?>
		<tr>
			<td valign="top"><?php
				$this->plugin(
					'ahref',
					$this->yawiki->getViewLink($val['area'], $val['page'], $val['dt']),
					$i --
				);
			?></td>
			<td valign="top"><?php
				$this->plugin('dateformat', $val['dt'], Yawp::getConfElem('yawiki', 'dateformat', '%c'));
				echo ' (' . htmlspecialchars($val['username']) . ')';
				if (trim($val['note']) != '') {
					echo '<br /><i>' . htmlspecialchars($val['note']) . '</i>';
				}
			?></td>
			<td valign="top" align="center">
				<?php if ($key == 1) {
					$this->plugin('input', 'radio', 'from', $val['dt'], 'checked="checked"');
				} else {
					$this->plugin('input', 'radio', 'from', $val['dt']);
				} ?>
			</td>
			<td valign="top">
				<?php $this->plugin('input', 'submit', 'to', $val['dt']) ?>
			</td>
		</tr>
	<?php endforeach ?>
	</table>

</form>


<?php include $this->loadTemplate('footer.tpl.php') ?>