<?php
/**
 * Created by IntelliJ IDEA.
 * User: user
 * Date: 7/14/15
 * Time: 4:01 PM
 */

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

$app = Application::getInstance();
$channelId = (int)$_POST['channelId'];
$mainDb = $app->getMainDb($channelId);
$memcache = $app->getMemcache();

try {
    $resp = $memcache->get('selo'.'getDataBuyMoney3'.$channelId);
    if (!$resp) {
        $result = $mainDb->query("SELECT * FROM data_buy_money");
        if ($result) {
            $dataMoney = $result->fetchAll();
        } else {
            $json_data['id'] = 6;
            $json_data['status'] = 's282';
            throw new Exception("Bad request to DB!");
        }
        $resp = [];
        if (!empty($dataMoney)) {
            foreach ($dataMoney as $key => $m) {
                $resp[] = $m;
            }
        } else {
            $json_data['id'] = 1;
            $json_data['status'] = 's283';
            throw new Exception("Bad request to DB!");
        }
        $memcache->set('selo'.'getDataBuyMoney3'.$channelId, $resp, MEMCACHED_DICT_TIME);
    }

    $json_data['message'] = $resp;
    echo json_encode($json_data);
}
catch (Exception $e)
{
    $json_data['status'] = 's206';
    $json_data['message'] = $e;
    echo json_encode($json_data);
}

