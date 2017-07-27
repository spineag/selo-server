<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');

$app = Application::getInstance();
$channelId = (int)$_POST['channelId'];
$itemName = (int)$_POST['item'];
$mainDb = $app->getMainDb($channelId);

$result = $mainDb->query('SELECT version FROM version WHERE name="'.$itemName.'"');
$v = $result->fetch();

if ($v) {
    echo $v['version'];
} else {
    echo 1;
}
