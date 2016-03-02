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
// $Id: header.tpl.php,v 1.6 2006/02/22 07:40:58 delatbabel Exp $
//
// =====================================================================

?>

<?php
	// this echo stops short_tags from choking on the XML declaration.
	// split it up so that text editors don't stop highlighting code
	// at the ending tags.
	echo '<' . '?xml version="1.0" encoding="iso-8859-1" ?' . '>';
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
		"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

	<head>
		<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
		<title><?php $this->_($this->yawiki->getHtmlTitle()) ?></title>
		<link rel="alternate" type="application/rss+xml" title="RSS feed" href="<?php echo $this->yawiki->nav['rss'] ?>" />
		<?php include $this->loadTemplate('stylesheet.tpl.php') ?>
	</head>
	
	<body>
		
		<!-- top span: title section -->
		<table width="100%" border="0" cellspacing="4" cellpadding="4">
			<tr>
				<td valign="top" align="left">
					<h1><?php $this->_($this->yawiki->getAreaInfo(null, 'title')) ?></h1>
					<p><em><?php $this->_($this->yawiki->getAreaInfo(null, 'tagline')) ?></em></p>
				</td>
				<td valign="top" align="right">
					<p>[&nbsp;<?php $this->plugin('ahref', 'search.php', 'AdvancedSearch') ?>&nbsp;|&nbsp;<?php $this->plugin('ahref', $this->yawiki->nav['map'], 'AreaMap') ?>&nbsp;]</p>
					<p><?php include $this->loadTemplate('search_form.tpl.php') ?></p>
				</td>
			</tr>
		</table>
		
		<!-- top span: nav section -->
		<?php
			if ($this->yawiki->isContentPage &&
				basename($this->_script) != 'error.tpl.php') {
				
				if (count($this->yawiki->getNav(0)) > 1) {
					
					$this->plugin('NavTop', 'tabs', $this->yawiki->getNav(0), 'tabs_marginal', 'tabs_unselect', 'tabs_selected');
					
					if (count($this->yawiki->getNav(1)) > 1) {
						// at least two elements
						$this->plugin('NavTop', 'wide', $this->yawiki->getNav(1), 'wide_marginal', 'wide_unselect', 'wide_selected');
					} else {
						// only one element, the parent, so show nothing
						$this->plugin('NavTop', 'wide', array(), 'wide_marginal', 'wide_unselect', 'wide_selected');
					}
				}
			}
		?>
		
		<!-- main body -->
		<table width="100%" border="0" cellspacing="12" cellpadding="0">
			<tr>
			
				<!-- left span -->
				<td valign="top" width="15%" align="center">
				
					<?php if (Yawp::getConfGroup('Yawp', 'auth')) {
						echo '<div style="padding: 4px; border: 1px dotted gray;">';
						include $this->loadTemplate('auth.tpl.php');
						echo '</div><br />';
					} ?>
					
					<?php if (! Yawp::getConfElem('yawiki', 'only_area')): ?>
					<div style="padding: 4px; border: 1px dotted gray;">
						<p><b>Areas In<br />This Wiki</b></p>
						
						<?php
							// de-linkify the current area
							foreach (array_keys($this->yawiki->areaInfo) as $val) {
								echo "<p>";
								if ($this->yawiki->area == $val &&
									$this->yawiki->isContentPage) {
									$this->_($val);
								} else {
									$this->plugin('ahref', $this->yawiki->conf['absolute_url']."$val/", $val);
								}
								echo "</p>\n";
							}
						?>
					</div>
					<?php endif ?>
					
				</td>
				
				<!-- middle span -->
				<td valign="top" width="70%">
