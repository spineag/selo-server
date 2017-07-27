<?php
include_once('../library/Application.php');

$mainDb = Application::getInstance()->getMainDb(2);
$socialNetwork = Application::getInstance()->getSocialNetwork(2);

$twoWeeks = time() - 1296000;
$result = $mainDb->query("SELECT social_id FROM users WHERE last_visit_date > ".$twoWeeks);
$ar = $result->fetchAll();
$ids = [];
foreach ($ar as $key => $value) {
    if ($value['social_id'] && $value['social_id'] != 'null' && $value['social_id'] != '1') {
        $ids[] = $value['social_id'];
    }
}

$txt = 'Помогите собрать побольше молока для Ниточки и обменять их на уникальные призы!';

while (count($ids) > 1) {
    $arr = array_splice($ids,0,100);
    $sArr = implode(",", $arr);
    $result = $socialNetwork->sendNotification($sArr, $txt);
}

