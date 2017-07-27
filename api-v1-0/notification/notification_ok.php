<?php
include_once('../library/Application.php');

$curHour = gmdate('G');
$curHour = $curHour + 3;  // Moscow UTC +3
if ($curHour == 8 || $curHour == 14) {

    $mainDb = Application::getInstance()->getMainDb(3);
    echo $socialNetwork = Application::getInstance()->test();
    try {
        $socialNetwork = Application::getInstance()->getSocialNetwork(3);
    } catch (Exception $e) {
        echo $e;
    }

    $time = time();
    $notif = array();

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

    $dLast1 = date("Y.m.d", $time - 30 * 60 * 60 * 24);
    $dLast2 = date("Y.m.d", $time - 2 * 60 * 60 * 24);

    $notif['date_end'] = $time + 60 * 60 * 24 * 5;
    $notif['message'] = $txt;
    $notif['last_access_range'] = $dLast1 . '-' . $dLast2;
    $result = $socialNetwork->sendNotification(array(), $notif);
}

