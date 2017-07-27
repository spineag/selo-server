<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/fb-php-graph-sdk-5.5/src/Facebook/autoload.php';
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');

$app = Application::getInstance();
$app_id = "1936104599955682";
$app_secret = "dd3c1b11a323f01a3ac23a3482724c49";
$app_token = "1936104599955682|BJ5JAYUV8FSdztyc3MW2lHVbXoU";

$mainDb = $app->getMainDb(4);

$fb = new Facebook\Facebook([
    'app_id' => $app_id ,
    'app_secret' => $app_secret,
    'default_graph_version' => 'v2.9',
]);

$lastTime = time() - 259200; // 3 days
$txt = 'Molly needs lots of milk! Hurry up to collect as much milk as you can. Eventually you can exchange it for unique prizes.';

$arAll = [];
$result = $mainDb->query('SELECT social_id FROM users WHERE last_visit_date >'.$lastTime.' AND level >= 6');
$ar = $result->fetchAll();
$arAll = $ar;

$result = $mainDb->query('SELECT social_id FROM users WHERE timezone >= -10 AND timezone <= -4');
$ar = $result->fetchAll();
$arAll = array_merge($arAll, $ar);

$result = $mainDb->query('SELECT social_id FROM users WHERE sale_pack = 1 OR starter_pack = 1');
$ar = $result->fetchAll();
$arAll = array_merge($arAll, $ar);

$countSend = 0;
$errors = 0;
while (count($arAll) > 1) {
    usleep(200000);
    $arr = array_splice($arAll,0,50);
    if ($arr) {
        foreach ($arr as $key => $value) {
            try {
                if ($value['social_id'] && $value['social_id'] != 'null' && $value['social_id'] != '1') {
                    $sendNotif = $fb->post('/' . $value['social_id'] . '/notifications', array('href' => '?notif', 'template' => $txt), $app_token);
                    $countSend++;
                }
            } catch (Exception $e) {
                    $errors++;
            }
        }
    }
}
echo 'count:'.$countSend.'  errors:'.$errors;

//$result = $mainDb->query("SELECT COUNT(social_id) as c FROM users");
//$ar = $result->fetch();
//$countAll = (int)$ar['c'];
//
//$idStart = 2;
//$idFinish = 102;
//while ($countAll > 0) {
//
//    $result = $mainDb->query("SELECT social_id FROM users WHERE id > ".$idStart." AND id <= $idFinish AND last_visit_date >".$lastTime);
//    $ar = $result->fetchAll();
//    if ($ar) {
//        foreach ($ar as $key => $value) {
//            try {
//                if ($value['social_id'] && $value['social_id'] != 'null') {
//                    $sendNotif = $fb->post('/' . $value['social_id'] . '/notifications', array('href' => '?notif', 'template' => $txt), $app_token);
//                }
//            } catch (Exception $e) {
//
//            }
//        }
//    }
//    $countAll = $countAll - 100;
//    $idStart = $idStart + 100;
//    $idFinish = $idFinish + 100;
//}


