<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

$app = Application::getInstance();
if (isset($_POST['channelId'])) {
    $channelId = (int)$_POST['channelId'];
} else $channelId = 2; // VK
$mainDb = $app->getMainDb($channelId);
$memcache = $app->getMemcache();


try {
    $resp = $memcache->get('selo'.'getDataCats3'.$channelId);
    if (!$resp) {
        $result = $mainDb->query("SELECT * FROM data_cat");
        if ($result) {
            $cats = $result->fetchAll();
        } else {
            $json_data['id'] = 1;
            $json_data['status'] = 's284';
            throw new Exception("Bad request to DB!");
        }
        $resp = [];
        if (!empty($cats)) {
            foreach ($cats as $key => $dict) {
                $item = [];
                $item['id'] = $dict['id'];
                $item['cost'] = $dict['cost'];
                $item['block_by_level'] = $dict['block_by_level'];
                $resp[] = $item;
            }
        } else {
            $json_data['id'] = 1;
            $json_data['status'] = 's285';
            throw new Exception("Bad request to DB!");
        }
        $memcache->set('selo'.'getDataCats3'.$channelId, $resp, MEMCACHED_DICT_TIME);
    }

    $json_data['message'] = $resp;
    echo json_encode($json_data);
}
catch (Exception $e)
{
    $json_data['status'] = 's076';
    $json_data['message'] = $e;
    echo json_encode($json_data);
}


