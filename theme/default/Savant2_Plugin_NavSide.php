<?php

require_once 'Savant2/Plugin.php';

class Savant2_Plugin_NavSide extends Savant2_Plugin {

	function plugin($menu, $unselect, $selected)
	{
		$text = '';
		$text .= "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"4\">\n";
		foreach ($menu as $key => $val) {
			$text .="<tr>";
			$cell = ($key == 0) ? 'th' : 'td';
			if ($val['selected'] == 'selected') {
				$text .= "<$cell class=\"$selected\">{$val['text']}</$cell>";
			} else {
				$text .= "<$cell class=\"$unselect\"><a class=\"$unselect\" href=\"{$val['href']}\">{$val['text']}</a></$cell>";
			}
			$text .="</tr>\n";
		}
		$text .= "</table>";
		return $text;
	}
	
}
?>