<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

$app = Application::getInstance();
$channelId = (int)$_POST['channelId'];
$memcache = $app->getMemcache();
$mainDb = $app->getMainDb($channelId);

try {
    $res = $memcache->get('selo'.'getDataSalePack'.$channelId);
    if (!$res) {
        $result = $mainDb->query("SELECT * FROM data_sale_pack");
        if ($result) {
            $res = $result->fetch();
            $memcache->set('selo'.'getDataSalePack'.$channelId, $res, MEMCACHED_DICT_TIME);
        } else {
            $json_data['id'] = 2;
            $json_data['status'] = 's307';
            throw new Exception("Bad request to DB!");
        }
    }

    $json_data['message'] = $res;
    echo json_encode($json_data);
}
catch (Exception $e)
{
    $json_data['status'] = 's098';
    $json_data['message'] = $e->getMessage();
    echo json_encode($json_data);
}

