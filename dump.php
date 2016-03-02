<?php
require_once 'Yawp.php';
Yawp::start();

$db =& Yawp::getObject('DB');
$db->setFetchmode(DB_FETCHMODE_ASSOC);
$sql = "SELECT DISTINCT area, page, max(dt) AS dt FROM yawiki_store GROUP BY area, page ORDER BY area, page";
$list = $db->getAll($sql);

foreach ($list as $val) {
    extract($val);
    $sql = "SELECT * FROM yawiki_store WHERE area = '$area' AND page = '$page' AND dt = '$dt'";
    $row = $db->getRow($sql);
    echo "----- $area:$page -----\n\n";
    echo $row['body'];
    echo "\n\n";
}

Yawp::stop();
?>