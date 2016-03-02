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
// $Id: links.tpl.php,v 1.4 2005/07/29 14:34:34 pmjones Exp $
//
// =====================================================================

?>

<?php include $this->loadTemplate('header.tpl.php') ?>

<h1><?php $this->plugin('ahref', $this->yawiki->getViewLink($this->yawiki->area, $this->yawiki->page), "{$this->yawiki->area}:{$this->yawiki->page}") ?></h1>

<h2>Links</h2>

<p>These links reflect the most recent version of the wiki, and do not include external Interwiki or URL links.</p>

<h3>Inbound</h3>
<ul>
<?php if (! is_array($this->inbound) || count($this->inbound) == 0): ?>
	<li>No inbound links (orphan)</li>
<?php else: ?>
	<?php foreach ($this->inbound as $val): ?>
	<li><?php $this->plugin(
		'ahref',
		$this->yawiki->getViewLink($val['area'], $val['page']),
		"{$val['area']}:{$val['page']}"
	) ?></li>
	<?php endforeach; ?>
<?php endif; ?>
</ul>

<h3>Outbound</h3>
<ul>
<?php if (! is_array($this->outbound) || count($this->outbound) == 0): ?>
	<li>No outbound links (terminal)</li>
<?php else: ?>
	<?php foreach ($this->outbound as $val): ?>
		<li><?php $this->plugin(
			'ahref',
			$this->yawiki->getViewLink($val['area'], $val['page']),
			"{$val['area']}:{$val['page']}"
		) ?></li>
	<?php endforeach; ?>
<?php endif; ?>
</ul>

<?php include $this->loadTemplate('footer.tpl.php') ?>