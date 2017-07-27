<?php
//include_once($_SERVER['DOCUMENT_ROOT'] . '/php/api-v1-0/library/Application.php');
include_once('../library/Application.php');
//include_once('http://505.ninja/php/api-v1-0/library/Application.php');

$mainDb = Application::getInstance()->getMainDb(2);
$socialNetwork = Application::getInstance()->getSocialNetwork(2);

//$vkTimeRestriction = time() - 2592000; // 1 Month
//$vkTimeOffline = time () - 86400; // 1 day
//$db = $mainDb->query("SELECT social_id FROM users WHERE last_visit_date > ".$vkTimeRestriction." AND last_visit_date < ".$vkTimeOffline." ORDER BY RAND() DESC LIMIT 10000");
////$db = $mainDb->query("SELECT social_id FROM users WHERE last_visit_date > ".$vkTimeRestriction." ORDER BY RAND() DESC LIMIT 10000");
//while ($r = $db->fetch())
//{
//    $users[] = $r['social_id'];
//}

//$r = rand(1, 5);
//if ($r == 1) {
//    $txt = 'В Умелых Лапках все по тебе соскучились. Скорее возвращайся в игру.';
//} elseif ($r == 2) {
//    $txt = 'Долина Рукоделия ждет вас. Заходите в игру!';
//} elseif ($r == 3) {
//    $txt = 'Чего бы еще такого произвести? Заходите в Умелые Лапки!';
//} elseif ($r == 4) {
//    $txt = 'Все желают приобрести ваши продукты. Заходите в Умелые Лапки!';
//} else {
//    $txt = 'Долина Рукоделия ждет вас. Заходите в игру!';
//}

$txt = 'Ты где пропадаешь? Жители Долины по тебе сильно соскучились!';

//while (count($users) > 1) {
//    $arr = array_splice($_POST["userSocialId"],0,100);
//    $sArr = implode(",", $arr);
    $result = $socialNetwork->sendNotification($_POST["userSocialId"], $txt);
//}