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


//$result = $mainDb->query("SELECT social_id FROM users ORDER BY RAND() LIMIT 10");
////$result = $mainDb->query("SELECT social_id FROM users");
//$ar = $result->fetchAll();


if ($_POST["userSocialId"]) {
//    $ids = [];
//    foreach ($ar as $key => $value) {
//        $ids[] = $value['social_id'];
//    }
//    foreach ($ids as $key => $value) {
//        try {
//            if ($value && $value != 'null' && $value != '1') {
                $sendNotif = $fb->post('/' . $_POST["userSocialId"] . '/notifications', array('href' => '?notif', 'template' => 'Where are you!'), $app_token);
//            }
//        } catch (Exception $e) {
//            echo $e;
        }


