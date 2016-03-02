<?php

// =====================================================================
// 
// This program is part of Yet Another Wiki (Yawiki).  For more
// information, please visit http://yawiki.com/ at your convenience.
// 
// Copyright (C) 2004 Del <del@babel.com.au>
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
// $Id: changes.php,v 1.4 2005/07/07 21:36:26 pmjones Exp $
//
// =====================================================================

// simple change viewer

require_once 'Yawp.php';
Yawp::start();

$area = Yawp::getGet('area', false);

$Savant->display('header.tpl.php');

echo '<h1>Recent Changes</h1>';

if ($area) {
	echo '<h2>' . htmlspecialchars($area) . '</h2>';
}

echo '<table class="admin">
	<tr class="admin">
		<th class="admin">area</th>
		<th class="admin">page</th>
		<th class="admin">timestamp</th>
		<th class="admin">editor</th>
		<th class="admin">note</th>
	</tr>
';

$type = Yawp::getGet('type', 'edits');
$amt = (int) Yawp::getGet('amt', 50);

if ($area) {
	$data = $yawiki->store->getChanges($type, $amt, $area);
} else {
	$data = $yawiki->store->getChanges($type, $amt);
}

// print "<pre>\n";
// print_r($data);
// print "</pre>\n";

foreach ($data as $key => $val) {
	
	array_walk($val, array('yawiki', 'htmlspecialchars')); // escape for output
	echo '<tr class="admin">';
	echo "<td class=\"admin\"><a href=\"changes.php?area={$val['area']}\">" . $val['area'] . '</a></td>';
	echo "<td class=\"admin\"><a href=\"index.php?area={$val['area']}&page={$val['page']}\">" . $val['page'] . '</a></td>';
	echo '<td class="admin">' . $val['dt'] . '</td>';
	echo '<td class="admin">' . $val['username'] . '</td>';
	echo '<td class="admin">' . $val['note'] . '</td>';
	echo '</tr>';
}

echo '</table>';

$Savant->display('footer.tpl.php');
Yawp::stop();

?>