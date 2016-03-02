<?php

// simple referrals tracker

require_once 'Yawp.php';
Yawp::start();

$area   = Yawp::getGet('area', false);
$days   = (int) Yawp::getGet('days', 1);
$ignore = Yawp::getGet('ignore', true);
$wrap   = Yawp::getConfElem('yawiki', 'diff_wrap', 40);
$skip = 0;
$subdomain = '.' . HTTP_HOST;
$pos = strlen($subdomain) * -1;

$data = $yawiki->hits->getReferrals($days, $area);
$hits = count($data);	

$Savant->display('header.tpl.php');

echo '<h1>Referrals</h1>';

if ($area) {
	echo '<h2>' . htmlspecialchars($area) . '</h2>';
}

echo "<p>Covering $hits hits over $days days.</p>";

echo '<table class="admin">
	<tr class="admin">
		<th class="admin">area</th>
		<th class="admin">page</th>
		<th class="admin">when</th>
		<th class="admin">from</th>
	</tr>
';

foreach ($data as $key => $val) {
	
	// ignore self-references and blanks?
	if ($ignore) {
	
		// ignore both the host and subdomains of the host,
		// as well as blanks
		if ($val['referer_host'] == HTTP_HOST ||
			substr($val['referer_host'], $pos) == $subdomain ||
			trim($val['referer_host']) == '') {
			$skip ++;
			continue;
		}
	}
	
	$val['when'] = date("Y-m-d H:i:s", $val['u']);
	
	array_walk($val, array('yawiki', 'htmlspecialchars')); // escape for output	
	
	if ($val['referer_host']) {
		$href = 'http://' . $val['referer_host'] . $val['referer_path'];
		if ($val['referer_qstr']) {
			$href .= '?' . $val['referer_qstr'];
		}
		$link = "<a href=\"$href\">";
		$link .= wordwrap($href, $wrap, '<br />', 1) . "</a>";
	} else {
		$link = '&nbsp;';
	}
	
	
	echo '<tr class="admin">';
	echo "<td class=\"admin\"><a href=\"referrals.php?area={$val['area']}\">" . $val['area'] . '</a></td>';
	echo '<td class="admin">' . $val['page'] . '</td>';
	echo '<td class="admin">' . $val['when'] . '</td>';
	echo '<td class="admin">' . $link . '</td>';
	echo '</tr>';
}

echo '</table>';

echo "<p>ignored $skip self-references to '" . HTTP_HOST . "' and/or blanks</p>";
$Savant->display('footer.tpl.php');
Yawp::stop();

?>