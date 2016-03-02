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
// $Id: rss.php,v 1.4 2006/02/27 00:31:37 delatbabel Exp $
//
// =====================================================================

require_once 'Yawp.php';
Yawp::start();

// type can be 'days', 'hours', or 'edits', defaults to days if the
// Yawiki conf element is not set
$type = Yawp::getGet(
    'type',
    Yawp::getConfElem('yawiki', 'rss_type', 'days')
);

// amt is the number of days, hours, or edits
// to look at for changes; defaults to 3 if the
// Yawiki conf element is not set
$amt = Yawp::getGet(
    'amt',
    Yawp::getConfElem('yawiki', 'rss_amt', '3')
);

// area is the area we want, or null for all areas
$area = Yawp::getGet('area', null);

// page is the page we want, or null for all pages
$page = Yawp::getGet('page', null);

// get the list of all requested changes
$list = $yawiki->store->getChanges($type, $amt, $area, $page);

// now check the permissions on each page in the list.
// it has to be publicly available to be shown.
foreach ($list as $key => $val) {
    if (! $yawiki->acl->pageView('*', $val['area'], $val['page'])) {
        unset($list[$key]);
    }
    $versions = $yawiki->store->getVersionList($val['area'], $val['page'], 1, $val['dt']);
    $list[$key]['from'] = !empty($versions) ? $versions[0]['dt'] : $list[$key]['dt'];
}

// build the base viewing link on the current directory.
// force an ending slash on it
$link = 'http://' . HTTP_HOST . dirname($_SERVER['PHP_SELF']);
if (substr($link, -1) != '/') {
    $link .= '/';
}

// assign and display
$Savant->assign('list', $list);
$Savant->assign('link', $link);
$Savant->assign('info', $yawiki->getAreaInfo());
$Savant->display('rss.tpl.php');

// done!
Yawp::stop();
?>