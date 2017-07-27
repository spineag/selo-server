<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

$app = Application::getInstance();
$channelId = 4;
$mainDb = $app->getMainDb($channelId);

$ids = $_POST['ids'];

$result = $mainDb->query("SELECT id,social_id,first_name,last_name,photo_url FROM users WHERE social_id IN (".$ids.")");
$arr = $result->fetchAll();
$resp = [];
if ($arr) {
    foreach ($arr as $value => $dict) {
        $res = [];
        $res['id'] = $dict['id'];
        $res['social_id'] = $dict['social_id'];
        $res['name'] = $dict['name'];
        $res['last_name'] = $dict['last_name'];
        $res['photo_url'] = $dict['photo_url'];
        if ($res['photo_url'] == '') $res['photo_url'] = 'unknown';
        $resp[] = $res;
    }
    $json_data['message'] = $resp;
    echo json_encode($json_data);
} else {
    $json_data['id'] = 2;
    $json_data['status'] = 's...';
    throw new Exception("Bad request to DB!");
}







