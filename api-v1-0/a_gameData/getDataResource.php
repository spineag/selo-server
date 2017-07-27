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
    $resp = $memcache->get('getDataResource3'.$channelId);
    if (!$resp) {
        $result = $mainDb->query("SELECT * FROM resource");
        if ($result) {
            $resourcesALL = $result->fetchAll();
        } else {
            $json_data['id'] = 1;
            $json_data['status'] = 's293';
            throw new Exception("Bad request to DB!");
        }
        $resp = [];
        if (!empty($resourcesALL)) {
            foreach ($resourcesALL as $key => $dict) {
                $resourceItem = [];
                $resourceItem['id'] = $dict['id'];
                $resourceItem['name'] = $dict['name'];
                $resourceItem['resource_type'] = $dict['resource_type'];
                $resourceItem['resource_place'] = $dict['resource_place'];
                $resourceItem['url'] = $dict['url'];
                $resourceItem['image_shop'] = $dict['image_shop'];
                $resourceItem['currency'] = $dict['currency'];
                $resourceItem['cost_default'] = $dict['cost_default'];
                $resourceItem['cost_max'] = $dict['cost_max'];
                $resourceItem['cost_hard'] = $dict['cost_hard'];
                $resourceItem['visitor_price'] = $dict['visitor_price'];
                $resourceItem['order_price'] = $dict['order_price'];
                $resourceItem['order_xp'] = $dict['order_xp'];
                $resourceItem['block_by_level'] = $dict['block_by_level'];
                $resourceItem['order_type'] = $dict['order_type'];
                $resourceItem['descript'] = $dict['descript'];
                $resourceItem['text_id_name'] = $dict['text_id_name'];
                $resourceItem['text_id_description'] = $dict['text_id_description'];
                switch ($dict['resource_type']) {
                    case 5: // PLANT
                        //$result = $mainDb->select("data_plant", "*", "resource_id='".$dict['id']."'");
                        $result = $mainDb->query("SELECT * FROM data_plant WHERE resource_id ='" . $dict['id'] . "'");
                        $plant = $result->fetch();
                        if (empty($plant)) {
                            $json_data['id'] = 2;
                            $json_data['status'] = 's294';
                            throw new Exception("Bad request to DB!");
                        }
                        $resourceItem['build_time'] = $plant['build_time'];
                        $resourceItem['craft_xp'] = $plant['craft_xp'];
                        $resourceItem['cost_skip'] = $plant['cost_skip'];
                        break;
                    case 7: // INSTRUMENT
                        break;
                    case 8: // RESOURCE
//                    $result = $mainDb->select("data_resource", "*", "resource_id='".$dict['id']."'");
                        $result = $mainDb->query("SELECT * FROM data_resource WHERE resource_id ='" . $dict['id'] . "'");
                        $resource = $result->fetch();
                        if (empty($resource)) {
                            $json_data['id'] = 3;
                            $json_data['status'] = 's295';
                            throw new Exception("Bad request to DB!");
                        }
                        $resourceItem['build_time'] = $resource['build_time'];
                        $resourceItem['craft_xp'] = $resource['craft_xp'];
                        $resourceItem['cost_skip'] = $resource['cost_skip'];
                        break;
                    default:
                        break;
                }
                $resp[] = $resourceItem;
            }
        } else {
            $json_data['id'] = 4;
            $json_data['status'] = 's296';
            throw new Exception("Bad request to DB!");
        }
        $memcache->set('getDataResource3'.$channelId, $resp, MEMCACHED_DICT_TIME);
    }

    $json_data['message'] = $resp;
    echo json_encode($json_data);
}
catch (Exception $e)
{
    $json_data['status'] = 's081';
    $json_data['message'] = $e;
    echo json_encode($json_data);
}


