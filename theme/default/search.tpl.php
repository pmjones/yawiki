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
// $Id: search.tpl.php,v 1.3 2005/07/29 14:34:34 pmjones Exp $
//
// =====================================================================

?>

<?php include $this->loadTemplate('header.tpl.php') ?>

<h1>Search</h1>

<?php $this->form->display() ?>

<?php if (isset($this->result) && is_array($this->result)): ?>

	<hr />
	
	<?php if (count($this->result) == 0): ?>
		<p>No results found; please try another search.</p>
	<?php else: ?>
		
		<p>Search results are in order of most-recent page to least-recent.</p>
		
		<ol>
		
		<?php foreach ($this->result as $key => $val): ?>
			<li><?php $this->plugin(
				'ahref',
				"index.php?area={$val['area']}&page={$val['page']}",
				(trim($val['title']) == '' ? $val['area'] . ':' . $val['page'] : $val['title'])
			) ?> <br />
			<span style="font-size: 80%">
			<?php
				$this->_($val['area'] . ':');
				foreach ((array) $val['path'] as $tmp) {
					echo ' &rarr; ';
					$this->_($tmp);
				}
			?>
			(<?php $this->plugin(
				'dateformat', $val['dt'], Yawp::getConfElem('yawiki', 'dateformat', '%c')
			) ?>)</span></li>
		<?php endforeach ?>
		
		</ol>
		
	<?php endif ?>
<?php endif ?>

<?php include $this->loadTemplate('footer.tpl.php') ?>