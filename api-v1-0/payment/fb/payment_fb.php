<?php
define('SN', 'fb');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
header("Content-Type: application/json; encoding=utf-8");

// Skip these two lines if you're using Composer
define('FACEBOOK_SDK_V4_SRC_DIR', 'facebook-php-sdk/src/Facebook/');
require __DIR__ . '/facebook-php-sdk/autoload.php';

use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\GraphObject;
use Facebook\FacebookRequestException;

$verify_token = "kapusta";
$app_id = "105089583507105";
$app_secret = "2b62f8a1aed1b7a677a215949d071bcd";
$app_token = "105089583507105|2b62f8a1aed1b7a677a215949d071bcd";
$server_url = "https://505.ninja/selo-project/php/api-v1-0/payment/fb/";

$pack_id_for_product = [
    $server_url.'pack1.html' => 1,
    $server_url.'pack2.html' => 2,
    $server_url.'pack3.html' => 3,
    $server_url.'pack4.html' => 4,
    $server_url.'pack5.html' => 5,
    $server_url.'pack6.html' => 6,
    $server_url.'pack7.html' => 7,
    $server_url.'pack8.html' => 8,
    $server_url.'pack9.html' => 9,
    $server_url.'pack10.html' => 10,
    $server_url.'pack11.html' => 11,
    $server_url.'pack12.html' => 12,
    $server_url.'pack13.html' => 13
];

FacebookSession::setDefaultApplication(
    $app_id,
    $app_secret);


$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'GET' && $_GET['hub_verify_token'] === $verify_token) {
    echo $_GET['hub_challenge'];
} else {
    $data = file_get_contents("php://input");
    $json = json_decode($data, true);

    if( $json["object"] && $json["object"] == "payments" ) {
        $payment_id = $json["entry"][0]["id"];
        try {
//            $mainDb = Application::getInstance()->getMainDb(4);
//            $session = new FacebookSession($app_token);
//            $request = new FacebookRequest(
//                $session,
//                'GET',
//                '/'.$payment_id . '?fields=user,actions,items'
//            );
//            $response = $request->execute();
//            $result = $response->getGraphObject(GraphObject::className());
//            $actions = $result->getPropertyAsArray('actions');
//            if( $actions[0]->getProperty('status') == 'completed' ){
//                $user = $result->getProperty('user')['id'];
//                $items = $result->getPropertyAsArray('items');
//                $product = $items[0]->getProperty('product');
//                $packId = $pack_id_for_product[$product];
//                if (!$user) $user = -1;
//                if (!$packId) $packId = -1;
//
//                $time = date("Y-m-d H:i:s");
//                $t = time();
//            }
        } catch (FacebookRequestException $e) {
            error_log($e->getRawResponse());
//            $time = date("Y-m-d H:i:s");
//            $mainDb->query('INSERT INTO trans_error SET message ='.$e->getRawResponse().', time_try="'.$time.'"');
        } catch (\Exception $e) {
            error_log($e);
//            $time = date("Y-m-d H:i:s");
//            $mainDb->query('INSERT INTO trans_error SET message ='.$e->getRawResponse().', time_try="'.$time.'"');
        }
    }
}
