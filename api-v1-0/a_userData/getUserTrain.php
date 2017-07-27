<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

if (isset($_POST['userId']) && !empty($_POST['userId'])) {
    $app = Application::getInstance();
    if (isset($_POST['channelId'])) {
        $channelId = (int)$_POST['channelId'];
    } else $channelId = 2; // VK

    if ($app->checkSessionKey($_POST['userId'], $_POST['sessionKey'], $channelId)) {
        $userId = filter_var($_POST['userId']);
        $shardDb = $app->getShardDb($userId, $channelId);
        try {
            $result = $shardDb->query("SELECT * FROM user_train WHERE user_id =" . $userId);
            if ($result) {
                $arr = $result->fetch();
                $res = [];
                $res['id'] = $arr['id'];
                $res['state'] = $arr['state'];
                $res['time_work'] = time() - $arr['time_start'];

            } else {
                $json_data['id'] = 2;
                $json_data['status'] = 's311';
                throw new Exception("Bad request to DB!");
            }

            $json_data['message'] = $res;
            echo json_encode($json_data);
        }
        catch (Exception $e)
        {
            $json_data['status'] = 's106';
            $json_data['message'] = $e->getMessage();
            echo json_encode($json_data);
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
    $json_data['status'] = 's107';
    $json_data['message'] = 'bad POST[userId]';
    echo json_encode($json_data);
}
