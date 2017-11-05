<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

$app = Application::getInstance();
$channelId = (int)$_POST['channelId'];
$mainDb = $app->getMainDb($channelId);
$memcache = $app->getMemcache();


try {
    $resp = $memcache->get('selo'.'getDataPet'.$channelId);
    if (!$resp) {
        $result = $mainDb->query("SELECT * FROM data_pet");
        if ($result) {
            $cats = $result->fetchAll();
        } else {
            $json_data['id'] = 1;
            $json_data['status'] = 's...';
            throw new Exception("Bad request to DB!");
        }
        $resp = [];
        if (!empty($cats)) {
            foreach ($cats as $key => $dict) {
                $item = [];
                $item['id'] = $dict['id'];
                $item['pet_type'] = $dict['pet_type'];
                $item['id_house'] = $dict['id_house'];
                $item['id_eat'] = $dict['id_eat'];
                $item['name'] = $dict['name'];
                $item['name2'] = $dict['name2'];
                $item['cost'] = $dict['cost'];
                $item['cost_hard'] = $dict['cost_hard'];
                $item['max_count'] = $dict['max_count'];
                $item['build_time'] = $dict['build_time'];
                $item['block_by_level'] = $dict['block_by_level'];
                $item['xp'] = $dict['xp'];
                $item['currency'] = $dict['currency_type'];
                $item['shop_icon'] = $dict['shop_icon'];
                $item['id_craft'] = $dict['id_craft'];
                $item['url'] = $dict['url'];
                $item['image'] = $dict['image'];
                $resp[] = $item;
            }
        } else {
            $json_data['id'] = 1;
            $json_data['status'] = 's285';
            throw new Exception("Bad request to DB!");
        }
        $memcache->set('selo'.'getDataPet'.$channelId, $resp, MEMCACHED_DICT_TIME);
    }

    $json_data['message'] = $resp;
    echo json_encode($json_data);
}
catch (Exception $e)
{
    $json_data['status'] = 's...';
    $json_data['message'] = $e;
    echo json_encode($json_data);
}


