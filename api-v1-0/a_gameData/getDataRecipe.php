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
    $resp = $memcache->get('selo'.'getDataRecipe3'.$channelId);
    if (!$resp) {
        $result = $mainDb->query("SELECT * FROM data_recipe");
        if ($result) {
            $recipeALL = $result->fetchAll();
        } else {
            $json_data['id'] = 1;
            $json_data['status'] = 's291';
            throw new Exception("Bad request to DB!");
        }
        $resp = [];
        if (!empty($recipeALL)) {
            foreach ($recipeALL as $key => $recipe) {
                $resp[] = $recipe;
            }
        } else {
            $json_data['id'] = 2;
            $json_data['status'] = 's292';
            throw new Exception("Bad request to DB!");
        }
        $memcache->set('selo'.'getDataRecipe3'.$channelId, $resp, MEMCACHED_DICT_TIME);
    }

    $json_data['message'] = $resp;
    echo json_encode($json_data);
}
catch (Exception $e)
{
    $json_data['status'] = 's080';
    $json_data['message'] = $e;
    echo json_encode($json_data);
}