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
// $Id: comments.tpl.php,v 1.5 2005/07/29 14:34:34 pmjones Exp $
//
// =====================================================================

?>

<br />

<div style="background-color: AliceBlue; padding: 8px; border: 1px solid;">

<table width="100%" border="0" cellspacing="0" cellpadding="4">
	
	<?php if ($this->comments->numRows() == 0): ?>
		<tr>
			<td>No user comments exist for this page.</td>
		</tr>
	<?php else: ?>
		<?php while ($row = $this->comments->fetchRow()): ?>
			<tr>
				<td>

<pre><b><?php $this->_($row['username']) ?></b>
<i><?php $this->_($row['dt']) ?> (<?php $this->_($row['id']) ?>)</i><br />
<code><?php $this->_(wordwrap($row['body'])) ?></code></pre>

<?php if ($this->showDelete): ?>
	<form method="post" action="<?php $this->_($_SERVER['REQUEST_URI']) ?>">
		<?php $this->plugin('input', 'hidden', 'comment_id', $row['id']) ?>
		<?php $this->plugin('input', 'submit', 'op', $this->submit['delete'], 'onClick="return confirm(\'Are you sure you want to delete this comment?\');"') ?>
	</form>
<?php endif ?>

				</td>
			</tr>
		<?php endwhile; ?>
	<?php endif; ?>
</table>

<hr />

<h6>Add A Comment</h6>
<?php $this->comment_form->display() ?>
<p><i>Your comment will be added immediately; please check your comments, and be nice.  :-)</i></p>
<p><i>Please use plain text only; HTML and PHP code will be converted to entities.</i></p>
<p><i>Your email address will be obfuscated with [AT] and [DOT] after you post your comment.</i></p>

</div>
