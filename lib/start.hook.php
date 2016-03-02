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
// $Id: start.hook.php,v 1.1 2004/10/01 18:28:50 pmjones Exp $
//
// =====================================================================


// build a global Savant instance
require_once 'Savant2.php';
$GLOBALS['Savant'] =& new Savant2(Yawp::getConfGroup('Savant'));

// turn on variable extraction for BC with Savant1 templates
//$GLOBALS['Savant']->setExtract(true);

// add default template and plugin paths relative to 
// this header file location
$base = dirname(__FILE__);
$GLOBALS['Savant']->addPath('template', $base . '/../theme/default/');
$GLOBALS['Savant']->addPath('resource', $base . '/../theme/default/');
unset($base);

// add user-defined template, plugin, and filter paths,
// if specified, to Savant
$dir = Yawp::getConfElem('yawiki', 'theme_dir', false);
if ($dir) {
	$GLOBALS['Savant']->addPath('template', $dir);
	$GLOBALS['Savant']->addPath('resource', $dir);
}
unset($dir);

// build a global Yawiki instance and assign to Savant
require_once 'yawiki.class.php';
$GLOBALS['yawiki'] =& new yawiki();
$GLOBALS['Savant']->assignRef('yawiki', $GLOBALS['yawiki']);

?>