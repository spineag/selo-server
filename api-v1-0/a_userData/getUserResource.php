<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

if (isset($_POST['userId']) && !empty($_POST['userId'])) {
    $app = Application::getInstance();
    if (isset($_POST['channelId'])) {
        $channelId = (int)$_POST['channelId'];
    } else $channelId = 2; // VK

    if ($app->checkSessionKey($_POST['userId'], $_POST['sessionKey'], $channelId)) {
        $m = md5($_POST['userId'].$app->md5Secret());
        if ($m != $_POST['hash']) {
            $json_data['id'] = 6;
            $json_data['status'] = 's416';
            $json_data['message'] = 'wrong hash';
            echo json_encode($json_data);
        } else {
            $userId = filter_var($_POST['userId']);
            $shardDb = $app->getShardDb($userId, $channelId);
            try {
                $resp = [];
                $result = $shardDb->query("SELECT * FROM user_resource WHERE user_id =" . $userId);
                if ($result) {
                    $arr = $result->fetchAll();
                    foreach ($arr as $value => $dict) {
                        $res = [];
                        $res['resource_id'] = $dict['resource_id'];
                        $res['count'] = $dict['count'];
                        $resp[] = $res;
                    }
                } else {
                    $json_data['id'] = 2;
                    $json_data['status'] = 's310';
                    throw new Exception("Bad request to DB!");
                }

                $json_data['message'] = $resp;
                echo json_encode($json_data);
            } catch (Exception $e) {
                $json_data['status'] = 's104';
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
    $json_data['status'] = 's105';
    $json_data['message'] = 'bad POST[userId]';
    echo json_encode($json_data);
}
