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
    $resp = $memcache->get('selo'.'getDataAnimal3'.$channelId);
    if (!$resp) {
        $result = $mainDb->query("SELECT * FROM data_animal");
        if ($result) {
            $animalALL = $result->fetchAll();
        } else {
            $json_data['id'] = 1;
            $json_data['status'] = 's259';
            throw new Exception("Bad request to DB!");
        }
        $resp = [];
        if (!empty($animalALL)) {
            foreach ($animalALL as $key => $recipe) {
                $animalALL['id'] = $recipe['id'];
                $animalALL['name'] = $recipe['name'];
                $animalALL['url'] = $recipe['url'];
                $animalALL['image'] = $recipe['image'];
                $animalALL['cost'] = $recipe['cost'];
                $animalALL['cost2'] = $recipe['cost2'];
                $animalALL['cost3'] = $recipe['cost3'];
                $animalALL['build_id'] = $recipe['build_id'];
//                $animalALL['time_craft'] = $recipe['time_craft'];
                $animalALL['craft_resource_id'] = $recipe['craft_resource_id'];
                $animalALL['raw_resource_id'] = $recipe['raw_resource_id'];
//                $animalALL['cost_force'] = $recipe['cost_force'];
                $animalALL['cost_new'] = $recipe['cost_new'];
                $animalALL['text_id'] = $recipe['text_id'];
                $resp[] = $animalALL;
                //new new new
            }
        } else {
            $json_data['id'] = 2;
            $json_data['status'] = 's260';
            throw new Exception("Bad request to DB!");
        }
        $memcache->set('selo'.'getDataAnimal3'.$channelId, $resp, MEMCACHED_DICT_TIME);
    }
    $json_data['message'] = $resp;
    echo json_encode($json_data);
    
} catch (Exception $e) {
    $json_data['status'] = 's074';
    $json_data['message'] = $e;
    echo json_encode($json_data);
}

