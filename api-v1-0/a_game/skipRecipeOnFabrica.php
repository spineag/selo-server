<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

if (isset($_POST['userId']) && !empty($_POST['userId'])) {
    $app = Application::getInstance();
    $channelId = (int)$_POST['channelId'];

    if ($app->checkSessionKey($_POST['userId'], $_POST['sessionKey'], $channelId)) {
        $m = md5($_POST['userId'].$_POST['recipeDbId'].$_POST['leftTime'].$_POST['buildDbId'].$app->md5Secret());
        if ($m != $_POST['hash']) {
            $json_data['id'] = 6;
            $json_data['status'] = 's385';
            $json_data['message'] = 'wrong hash';
            echo json_encode($json_data);
        } else {
            $userId = filter_var($_POST['userId']);
            $shardDb = $app->getShardDb($userId, $channelId);
            try {
                $m = '';
                $result = $shardDb->query("SELECT * FROM user_recipe_fabrica WHERE user_id =".$userId . " AND user_db_building_id =" . $_POST['buildDbId']);
                if ($result) {
                    $arr = $result->fetchAll();
                    $d = 0;
                    foreach ($arr as $value => $dict) {
                        $d = (int)$dict['delay_time'] - (int)$_POST['leftTime'];
                        if ($d<0) { // it's normal!)) for first in list
//                            $m .= (string)$d.'_'.$dict['id'].'; ';
//                            $d = 0;
                        }
                        $result = $shardDb->query('UPDATE user_recipe_fabrica SET delay_time=' . $d . ' WHERE id=' . $dict['id']);
                    }
                } else {
                    $json_data['id'] = 2;
                    $json_data['status'] = 's321';
                    throw new Exception("Bad request to DB!");
                }

                $json_data['message'] = '';
                $json_data['warning'] = $m;
                echo json_encode($json_data);
            } catch (Exception $e) {
                $json_data['status'] = 's153';
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
    $json_data['status'] = 's154';
    $json_data['message'] = 'bad POST[userId]';
    echo json_encode($json_data);
}
