<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/php/api-v1-0/library/Application.php');
header("Content-Type: application/json; encoding=utf-8");

session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/php/fb-php-graph-sdk-5.5/src/Facebook/autoload.php';

$verify_token = "kapusta";
$app_id = "1936104599955682";
$app_secret = "dd3c1b11a323f01a3ac23a3482724c49";
$app_token = "1936104599955682|BJ5JAYUV8FSdztyc3MW2lHVbXoU";
$server_url = "https://505.ninja/php/api-v1-0/payment/fb_5/";

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
    $server_url.'pack12.html' => 12
];

if ($_SERVER['REQUEST_METHOD'] == 'GET' && $_GET['hub_verify_token'] === $verify_token) {
    echo $_GET['hub_challenge'];
} else {
    $data = file_get_contents("php://input");
    $json = json_decode($data, true);

    if( $json["object"] && $json["object"] == "payments" ) {
        $payment_id = $json["entry"][0]["id"];
        try {
//            $mainDb = Application::getInstance()->getMainDb(4);
//            $fb = new Facebook\Facebook([
//                'app_id'                => $app_id,
//                'app_secret'            => $app_secret,
//                'default_graph_version' => 'v2.9',
//            ]);
//
//            $response = $fb->get('/'.$payment_id.'?fields=actions,items', $app_token); // fuck
//
//            $result = $response->getGraphObject();
//            $actions = $result->getPropertyAsArray('actions');
//
//            if( $actions[0]->getProperty('status') == 'completed' ){
//                $items = $result->getPropertyAsArray('items');
//                $product = $items[0]->getProperty('product');
//                $packId = $pack_id_for_product[$product];
//                if (!$user) $user = -1;
//                if (!$packId) $packId = -1;
//
//                $time = date("Y-m-d H:i:s");
//                $t = time();
//                $mainDb->query('INSERT INTO transactions SET uid='.$userSocialId.', product_code='.$packId.', time_try="'.$time.'", unitime='.$t);
//                $mainDb->query('INSERT INTO transaction_lost SET uid='.$userSocialId.', product_code='.$packId.', time_buy="'.$time.'", unitime='.$t);
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
