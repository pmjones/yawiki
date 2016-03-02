<?php

require_once 'Savant2/Plugin.php';

class Savant2_Plugin_NavTop extends Savant2_Plugin {

	function plugin($type, $menu, $marginal, $unselect, $selected)
	{
		if ($type == 'tabs') {
			return $this->tabs($menu, $marginal, $unselect, $selected);
		} elseif ($type == 'wide') {
			return $this->wide($menu, $marginal, $unselect, $selected);
		} else {
			return null;
		}
	}
	
	function tabs($menu, $marginal, $unselect, $selected)
	{
		$text = '';
		$pct = (int) ((1 / (count($menu) + 2)) * 100);
		$text .= "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"4\"><tr>\n";
		$text .= "<td width=\"$pct%\" class=\"$marginal\">&nbsp;</td>\n";
		$text .= $this->cells($menu, $marginal, $unselect, $selected);
		$text .= "<td width=\"$pct%\" class=\"$marginal\">&nbsp;</td>\n";
		$text .= "</tr></table>\n";
		return $text;
	}
	
	function wide($menu, $marginal, $unselect, $selected)
	{
		$text = '';
		$pct = (int) ((1 / (count($menu) + 2)) * 100);
		$text .= "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"4\"><tr>\n";
		$text .= "<td width=\"$pct%\" class=\"$marginal\">&nbsp;</td>\n";
		$text .= $this->cells($menu, $marginal, $unselect, $selected);
		$text .= "<td width=\"$pct%\" class=\"$marginal\">&nbsp;</td>\n";
		$text .= "</tr></table>\n";
		return $text;
	}
	
	function cells($menu, $marginal, $unselect, $selected)
	{
		$text = '';
		$last = count($menu) - 1;
		$pct = (int) ((1 / (count($menu) + 2)) * 100);
		
		foreach ($menu as $key => $val) {
			if ($val['selected'] == 'selected') {
				$text .= "<td width=\"$pct%\" class=\"$selected\">{$val['text']}</td>\n";
			} else {
				$text .= "<td width=\"$pct%\" class=\"$unselect\"><a class=\"$unselect\" href=\"{$val['href']}\">{$val['text']}</a></td>\n";
			}
			if ($key != $last) {
				$text .= "<td class=\"$marginal\">&nbsp;</td>\n";
			}
		}
		
		return $text;
	}
}
?>