<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

if (isset($_POST['userId']) && !empty($_POST['userId'])) {
    $app = Application::getInstance();
    $channelId = (int)$_POST['channelId'];

    if ($app->checkSessionKey($_POST['userId'], $_POST['sessionKey'], $channelId)) {
        $mainDb = $app->getMainDb($channelId);
        $userId = filter_var($_POST['userId']);
        $shardDb = $app->getShardDb($userId, $channelId);
        try {
            $arrRemoved=[];
            $result = $shardDb->query("SELECT wild_db_id FROM user_removed_wild WHERE user_id = " . $userId);
            $u = $result->fetchAll();
            foreach ($u as $value => $dict) {
                $arrRemoved[] = $dict['wild_db_id'];
            }

            $resp = [];
            $result = $mainDb->query("SELECT * FROM data_map_wild");
            if ($result) {
                $arr = $result->fetchAll();
                foreach ($arr as $value => $dict) {
                     if ( in_array($dict['id'], $arrRemoved) ) continue;
                    $build = [];
                    $build['id'] = $dict['id'];
                    $build['building_id'] = $dict['wild_id'];
                    $build['pos_x'] = $dict['pos_x'];
                    $build['pos_y'] = $dict['pos_y'];
                    $build['is_flip'] = $dict['is_flip'];
                    $build['chest_id'] = $dict['chest_id'];
                    $resp[] = $build;
                }
            } else {
                $json_data['id'] = 2;
                $json_data['status'] = 's313';
                throw new Exception("Bad request to DB!");
            }

            $json_data['message'] = $resp;
            echo json_encode($json_data);
        }
        catch (Exception $e)
        {
            $json_data['status'] = 's113';
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
    $json_data['status'] = 's114';
    $json_data['message'] = 'bad POST[userId]';
    echo json_encode($json_data);
}
