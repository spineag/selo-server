<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

if (isset($_POST['userId']) && !empty($_POST['userId'])) {
    $app = Application::getInstance();
    $channelId = (int)$_POST['channelId'];

    if ($app->checkSessionKey($_POST['userId'], $_POST['sessionKey'], $channelId)) {
        $userId = filter_var($_POST['userId']);
        $shardDb = $app->getShardDb($userId, $channelId);
        try {
            $resp = [];
            $result = $shardDb->query("SELECT * FROM user_pet WHERE user_id =" . $userId);
            if ($result) {
                $arr = $result->fetchAll();
                foreach ($arr as $value => $dict) {
                    $res = [];
                    $res['id'] = $dict['id'];
                    $res['pet_id'] = $dict['pet_id'];
                    $res['house_db_id'] = $dict['house_db_id'];
                    $res['time_eat'] = $dict['time_eat'];
                    $res['has_new_eat'] = $dict['has_new_eat'];
                    $resp[] = $res;
                }
            } else {
                $json_data['id'] = 2;
                $json_data['status'] = 's...';
                throw new Exception("Bad request to DB!");
            }

            $json_data['message'] = $resp;
            echo json_encode($json_data);
        }
        catch (Exception $e)
        {
            $json_data['status'] = 's...';
            $json_data['message'] = $e->getMessage();
            echo json_encode($json_data);
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
