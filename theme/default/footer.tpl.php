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
// $Id: footer.tpl.php,v 1.1 2004/10/01 18:28:50 pmjones Exp $
//
// =====================================================================

?>

				</td>
				<td valign="top" width="15%">
					<?php
						if ($this->yawiki->isContentPage &&
							basename($this->_script) != 'error.tpl.php' &&
							count($this->yawiki->getNav(2)) > 1) {
							echo '<div style="padding: 4px; border: 1px dotted gray;">';
							$this->plugin('NavSide', $this->yawiki->getNav(2), 'tall_unselect', 'tall_selected');
							echo '</div>';
						}
					?>
				</td>
			</tr>
		</table>
	</body>
</html>