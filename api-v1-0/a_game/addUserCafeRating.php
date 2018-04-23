<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

if (isset($_POST['userId']) && !empty($_POST['userId'])) {
    $app = Application::getInstance();
    $userId = filter_var($_POST['userId']);
    $channelId = (int)$_POST['channelId'];
    $shardDb = $app->getShardDb($userId, $channelId);
    try {
        $result = $shardDb->queryWithAnswerId('INSERT INTO user_cafe_rating SET user_id=' . $userId . ', count = 0');
        if ($result) {
            $json_data['message'] = $result[1];
            echo json_encode($json_data);
        } else {
            $json_data['id'] = 2;
            $json_data['status'] = 's021';
            $json_data['message'] = 'bad query';
        }
    } catch (Exception $e) {
        $json_data['status'] = 's098';
        $json_data['message'] = $e->getMessage();
        echo json_encode($json_data);
    }
} else {
    $json_data['id'] = 1;
    $json_data['status'] = 's023';
    $json_data['message'] = 'bad POST[userId]';
    echo json_encode($json_data);
}