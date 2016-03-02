<?php

// simple hits tracker

require_once 'Yawp.php';
Yawp::start();

$area = Yawp::getGet('area', false);

$days = Yawp::getGet('days', null);

$Savant->display('header.tpl.php');

echo '<h1>Hits</h1>';

if ($area) {
	echo '<h2>' . htmlspecialchars($area) . '</h2>';
}

echo '<table class="admin">
	<tr class="admin">
		<th class="admin">area</th>
		';

if ($area) {
	echo '<th class="admin">page</th>';
}

echo '
		<th class="admin">first</th>
		<th class="admin">latest</th>
		<th class="admin">total days</th>
		<th class="admin">total hits</th>
		<th class="admin">avg daily rate</th>
	</tr>
';

if ($area) {
	$data = $yawiki->hits->getPagesInArea($area);
} else {
	$data = $yawiki->hits->getAreaOverview($days);
}

foreach ($data as $key => $val) {
	
	$val['days'] = (time() - $val['min']) / (60 * 60 * 24);
	$val['rate'] = $val['hits'] / $val['days'];
	
	array_walk($val, array('yawiki', 'htmlspecialchars')); // escape for output	
	
	echo '<tr class="admin">';
	echo "<td class=\"admin\"><a href=\"hits.php?area={$val['area']}\">" . $val['area'] . '</a></td>';
	if ($area) {
		echo '<td class="admin">' . $val['page'] . '</td>';
	}
	echo '<td class="admin">' . strftime("%c", $val['min']) . '</td>';
	echo '<td class="admin">' . strftime("%c", $val['max']) . '</td>';
	echo '<td class="admin">' . sprintf("%10.2f", $val['days']) . '</td>';
	echo '<td class="admin">' . $val['hits'] . '</td>';
	echo '<td class="admin">' . sprintf("%10.2f", $val['rate']) . '</td>';
	echo '</tr>';
}

echo '</table>';

$Savant->display('footer.tpl.php');
Yawp::stop();

?>