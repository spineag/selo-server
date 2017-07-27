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
        $m = md5($_POST['userId'].$_POST['buildingId'].$_POST['posX'].$_POST['posY'].$_POST['countCell'].$app->md5Secret());
        if ($m != $_POST['hash']) {
            $json_data['id'] = 6;
            $json_data['status'] = 's354';
            $json_data['message'] = 'wrong hash';
            echo json_encode($json_data);
        } else {
            try {
                $result = $shardDb->queryWithAnswerId('INSERT INTO user_building SET user_id=' . $userId . ', building_id=' . $_POST['buildingId'] . ', in_inventory=0, pos_x=' . $_POST['posX'] . ', pos_y=' . $_POST['posY'] . ', count_cell=' . $_POST['countCell']);
                if ($result) {
                    $json_data['message'] = $result[1];
                } else {
                    $json_data['id'] = 2;
                    $json_data['status'] = 's011';
                    $json_data['message'] = 'bad query';
                }

                echo json_encode($json_data);
            } catch (Exception $e) {
                $json_data['status'] = 's012';
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
}
else
{
    $json_data['id'] = 1;
    $json_data['status'] = 's013';
    $json_data['message'] = 'bad POST[userId]';
    echo json_encode($json_data);
}