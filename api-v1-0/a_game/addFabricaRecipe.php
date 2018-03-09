<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

if (isset($_POST['userId']) && !empty($_POST['userId'])) {
    $app = Application::getInstance();
    $userId = filter_var($_POST['userId']);
    $channelId = (int)$_POST['channelId'];
    $shardDb = $app->getShardDb($userId, $channelId);

    if ($app->checkSessionKey($_POST['userId'], $_POST['sessionKey'], $channelId)) {
        $m = md5($_POST['userId'].$_POST['dbId'].$_POST['recipeId'].$_POST['delay'].$app->md5Secret());
        if ($m != $_POST['hash']) {
            $json_data['id'] = 6;
            $json_data['status'] = 's351';
            $json_data['message'] = 'wrong hash';
            echo json_encode($json_data);
        } else {
            try {
                $time = time();
                $result = $shardDb->queryLastId('INSERT INTO user_recipe_fabrica 
                                                 SET user_id=' . $userId . ', user_db_building_id=' .
                                                 $_POST['dbId'] . ', recipe_id=' . $_POST['recipeId'] . ', delay_time=' .
                                                 $_POST['delay'] . ', time_start=' . $time);
                if ($result) {
                    $json_data['message'] = $result;
                    echo json_encode($json_data);
                } else {
                    $json_data['id'] = 2;
                    $json_data['status'] = 's003';
                    $json_data['message'] = 'bad query';
                }

            } catch (Exception $e) {
                $json_data['status'] = 's004';
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
    $json_data['status'] = 's005';
    $json_data['message'] = 'bad POST[userId]';
    echo json_encode($json_data);
}