<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

if (isset($_POST['userId']) && !empty($_POST['userId'])) {
    $app = Application::getInstance();
    $channelId = (int)$_POST['channelId'];

    if ($app->checkSessionKey($_POST['userId'], $_POST['sessionKey'], $channelId)) {
        $m = md5($_POST['userId'].$_POST['id'].$app->md5Secret());
        if ($m != $_POST['hash']) {
            $json_data['id'] = 6;
            $json_data['status'] = 's380';
            $json_data['message'] = 'wrong hash';
            echo json_encode($json_data);
        } else {
            $userId = filter_var($_POST['userId']);
            $shardDb = $app->getShardDb($userId, $channelId);
            try {
                $result = $shardDb->query('DELETE FROM user_train_pack WHERE user_id=' . $_POST['userId']);
                $result2 = $shardDb->query('DELETE FROM user_train_pack_item WHERE user_id=' . $_POST['userId']);
                if ($result && $result2) {
                    $json_data['message'] = '';
                    echo json_encode($json_data);
                } else {
                    $json_data['id'] = 2;
                    $json_data['status'] = 's317';
                    throw new Exception("Bad request to DB!");
                }
            } catch (Exception $e) {
                $json_data['status'] = 's142';
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
    $json_data['status'] = 's143';
    $json_data['message'] = 'bad POST[userId]';
    echo json_encode($json_data);
}