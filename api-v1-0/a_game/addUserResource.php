<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

if (isset($_POST['userId']) && !empty($_POST['userId'])) {
    $app = Application::getInstance();
    $userId = filter_var($_POST['userId']);
    if (isset($_POST['channelId'])) {
        $channelId = (int)$_POST['channelId'];
    } else $channelId = 2; // VK
    $shardDb = $app->getShardDb($userId, $channelId);

    if ($app->checkSessionKey($_POST['userId'], $_POST['sessionKey'], $channelId)) {
        $m = md5($_POST['userId'] . $_POST['resourceId'] . $_POST['countAll'] . $app->md5Secret());
        if ($m != $_POST['hash']) {
            $json_data['id'] = 6;
            $json_data['status'] = 's360';
            $json_data['message'] = 'wrong hash';
            echo json_encode($json_data);
        } else {

            try {
               $resourceId = filter_var($_POST['resourceId']);
               $resourceCount = filter_var($_POST['countAll']);
                $result = $shardDb->query("INSERT INTO user_resource (user_id, resource_id, count) 
                                          VALUES ('" . $userId . "','" . $resourceId . "','" . $resourceCount . "') 
                                          ON DUPLICATE KEY UPDATE count = " . $resourceCount);
                if ($result) {
                    $json_data['message'] = '';
                    echo json_encode($json_data);
                } else {
                    $json_data['id'] = 2;
                    $json_data['status'] = 's024';
                    $json_data['message'] = 'bad query:: ' . $text;
                    echo json_encode($json_data);
                }
            } catch (Exception $e) {
                $json_data['status'] = 's025';
                $json_data['message'] = $e->getMessage();
                echo json_encode($json_data);
            }
        }
    } else {
        $json_data['id'] = 13;
        $json_data['status'] = 's221';
        $json_data['message'] = 'bad sessionKey';
        echo json_encode($json_data);
    }
} else {
    $json_data['id'] = 1;
    $json_data['status'] = 's026';
    $json_data['message'] = 'bad POST[userId]';
    echo json_encode($json_data);
}