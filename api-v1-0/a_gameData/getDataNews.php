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
$channelId = (int)$_POST['channelId'];
$mainDb = $app->getMainDb($channelId);

try {
    $result = $mainDb->query("SELECT * FROM data_news");
    if ($result) {
        $newsALL = $result->fetchAll();
    } else {
        $json_data['id'] = 1;
        $json_data['status'] = 's291';
        throw new Exception("Bad request to DB!");
    }
    $resp = [];
    if (!empty($newsALL)) {
        foreach ($newsALL as $key => $recipe) {
            $resp[] = $recipe;
        }
    } else {
        $json_data['id'] = 2;
        $json_data['status'] = 's292';
        throw new Exception("Bad request to DB!");
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