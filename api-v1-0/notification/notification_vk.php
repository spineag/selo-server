<?php
include_once('../library/Application.php');

$d = date("w");
if ($d == 1) exit;          // monday

$curHour = gmdate('G');
$curHour = $curHour + 3;  // Moscow UTC +3
if ($curHour == 8 || $curHour == 14) {
    $mainDb = Application::getInstance()->getMainDb(2);
    $socialNetwork = Application::getInstance()->getSocialNetwork(2);

    $vkTimeRestriction = time() - 2592000; // 1 Month
    $vkTimeOffline = time() - 172800; // 2 days
    $db = $mainDb->query("SELECT social_id FROM users WHERE last_visit_date > " . $vkTimeRestriction . " AND last_visit_date < " . $vkTimeOffline);
    $ar = $db->fetchAll();
    foreach ($ar as $key => $value) {
        $users[] = $value['social_id'];
    }

    $r = rand(1, 5);
    if ($r == 1) {
        $txt = 'Ты где пропадаешь? В Умелых Лапках без тебя очень скучно!';
    } elseif ($r == 2) {
        $txt = 'Привет! А мы уже в Умелых Лапках  успели по тебе соскучится!';
    } elseif ($r == 3) {
        $txt = 'Загляни в Долину Рукоделия! Там очень нужна твоя помощь!';
    } elseif ($r == 4) {
        $txt = 'Заходи в Умелые Лапки! В Лавке появились выгодные заказы!';
    } else {
        $txt = 'Давно не виделись! Все жители Долины Рукоделия давно ждут тебя в гости!';
    }

    while (count($users) > 1) {
        $arr = array_splice($users, 0, 100);
        $sArr = implode(",", $arr);
        $result = $socialNetwork->sendNotification($sArr, $txt);
    }
}