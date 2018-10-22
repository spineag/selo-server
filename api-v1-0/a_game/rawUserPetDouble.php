<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

if (isset($_POST['userId']) && !empty($_POST['userId'])) {
    $app = Application::getInstance();
    $channelId = (int)$_POST['channelId'];

    if ($app->checkSessionKey($_POST['userId'], $_POST['sessionKey'], $channelId)) {
        $m = md5($_POST['userId'].$_POST['petDbId'].$app->md5Secret());
        if ($m != $_POST['hash']) {
            $json_data['id'] = 6;
            $json_data['status'] = 's...';
            $json_data['message'] = 'wrong hash';
            echo json_encode($json_data);
        } else {
            $userId = filter_var($_POST['userId']);
            $shardDb = $app->getShardDb($userId, $channelId);
            try {
                $result = $shardDb->query('UPDATE user_pet SET has_new_eat=1, has_craft=0 WHERE id='.$_POST['petDbId']);
                if ($result) {
                    $json_data['message'] = '';
                    echo json_encode($json_data);
                } else {
                    $json_data['id'] = 2;
                    $json_data['status'] = 's...';
                    $json_data['message'] = 'bad query';
                }

            } catch (Exception $e) {
                $json_data['status'] = 's...';
                $json_data['message'] = $e->getMessage();
                echo json_encode($json_data);
            }
        }
    } else {
        $json_data['id'] = 13;
        $json_data['status'] = 's...';
        $json_data['message'] = 'bad sessionKey';
        echo json_encode($json_data);
    }
}
else
{
    $json_data['id'] = 1;
    $json_data['status'] = 's...';
    $json_data['message'] = 'bad POST[userId]';
    echo json_encode($json_data);
}