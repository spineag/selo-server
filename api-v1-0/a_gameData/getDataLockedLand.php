<?php
/**
 * Created by IntelliJ IDEA.
 * User: user
 * Date: 7/15/15
 * Time: 11:57 AM
 */

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

$app = Application::getInstance();
if (isset($_POST['channelId'])) {
    $channelId = (int)$_POST['channelId'];
} else $channelId = 2; // VK
$mainDb = $app->getMainDb($channelId);
$memcache = $app->getMemcache();

try {
    $lands = $memcache->get('getDataLockedLand'.$channelId);
    if (!$lands) {
        $result = $mainDb->query("SELECT * FROM data_locked_land");
        if ($result) {
            $lands = $result->fetchAll();
        } else {
            $json_data['id'] = 1;
            $json_data['status'] = 's288';
            throw new Exception("Bad request to DB!");
        }
        $memcache->set('getDataLockedLand'.$channelId, $lands, MEMCACHED_DICT_TIME);
    }
    $resp = [];
    if (!empty($lands)) {
        foreach ($lands as $key => $land) {
            $resp[] = $land;
        }
    } else {
        $json_data['id'] = 2;
        $json_data['status'] = 's289';
        throw new Exception("Bad request to DB!");
    }

    $json_data['message'] = $resp;
    echo json_encode($json_data);
}
catch (Exception $e)
{
    $json_data['status'] = 's078';
    $json_data['message'] = $e;
    echo json_encode($json_data);
}