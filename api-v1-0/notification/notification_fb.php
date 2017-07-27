<?php
//session_start();
//
//require_once $_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/fb-php-graph-sdk-5.5/src/Facebook/autoload.php';
//include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
//
//$d = date("w");
//if ($d == 1) exit;          // monday
//
//$curHour = gmdate('G');
//
//$t1 = 8 - $curHour; // curHour = 8 - UTC - > first notif
//if ($t1 < -12) $t1 = $t1 + 12;
//if ($t1 > 12) $t1 = $t1 - 12;
//$t2 = 14 - $curHour; // curHour = 14 - UTC - > second notif
//if ($t2 < -12) $t2 = $t2 + 12;
//if ($t2 > 12) $t2 = $t2 - 12;
//
//$app = Application::getInstance();
//$app_id = "1936104599955682";
//$app_secret = "dd3c1b11a323f01a3ac23a3482724c49";
//$app_token = "1936104599955682|BJ5JAYUV8FSdztyc3MW2lHVbXoU";
//
//$mainDb = $app->getMainDb(4);
//
//$fb = new Facebook\Facebook([
//    'app_id' => $app_id ,
//    'app_secret' => $app_secret,
//    'default_graph_version' => 'v2.9',
//]);
//
//$lastTime1 = time() - 2419200; // 28 days
//$lastTime2 = time() - 172800; // 2 days
//$result = $mainDb->query("SELECT social_id FROM users WHERE last_visit_date > ".$lastTime1." AND last_visit_date < ".$lastTime2." AND (timezone = ".$t1." || timezone = ".$t2.")");
//$ar = $result->fetchAll();
//
//$r = rand(1, 5);
//if ($r == 1) {
//    $text = 'Where have you been for so long? We have missed you so much here in Woolly Valley!';
//} elseif ($r == 2) {
//    $text = 'Hello! We\'ve missed you so much here in the Woolly Valley!';
//} elseif ($r == 3) {
//    $text = 'Visit the Handicraft Land now! We do need you help!';
//} elseif ($r == 4) {
//    $text = 'Visit the Woolly Valley! There are new and profitable orders in the Market this time!';
//} else {
//    $text = 'We haven\'t seen you for so long! All the citizens of the Handicraft Land are waiting for you to come around for a visit!';
//}
//
//if ($ar) {
//    $ids = [];
//    foreach ($ar as $key => $value) {
//        $ids[] = $value['social_id'];
//    }
//    foreach ($ids as $key => $value) {
//        try {
//            if ($value && $value != 'null' && $value != '1') {
//                $sendNotif = $fb->post('/' . $value . '/notifications', array('href' => '?notif', 'template' => $text), $app_token);
//            }
//        } catch (Exception $e) {
//            echo $e;
//        }
//    }
//}

