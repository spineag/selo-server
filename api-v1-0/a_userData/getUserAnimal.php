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
            $result = $shardDb->query("SELECT * FROM user_animal WHERE user_id =" . $userId);
            if ($result) {
                $arr = $result->fetchAll();
                foreach ($arr as $value => $dict) {
                    $res = [];
                    $res['id'] = $dict['id'];
                    $res['animal_id'] = $dict['animal_id'];
                    $res['user_db_building_id'] = $dict['user_db_building_id'];
                    if ($dict['raw_time_start'] == 0) {
                        $res['time_work'] = 0;
                    } else {
                        $res['time_work'] = time() - $dict['raw_time_start'];
                    }
                    $resp[] = $res;
                }
            } else {
                $json_data['id'] = 2;
                $json_data['status'] = 's298';
                throw new Exception("Bad request to DB!");
            }

            $json_data['message'] = $resp;
            echo json_encode($json_data);
        }
        catch (Exception $e)
        {
            $json_data['status'] = 's086';
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
    $json_data['status'] = 's087';
    $json_data['message'] = 'bad POST[userId]';
    echo json_encode($json_data);
}
