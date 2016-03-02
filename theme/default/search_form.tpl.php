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
// $Id: search_form.tpl.php,v 1.1 2004/10/01 18:28:50 pmjones Exp $
//
// =====================================================================

?>

<form action="search.php" method="get">
	<?php $this->plugin('input', 'hidden', 'qtype', 'any') ?>
	<?php $this->plugin('input', 'hidden', 'op', 'Search') ?> 
	Search: <?php $this->plugin('input', 'text', 'qword', '', 'size="10"') ?>
	<?php $this->plugin('input', 'submit', '', 'Go!') ?>
</form>