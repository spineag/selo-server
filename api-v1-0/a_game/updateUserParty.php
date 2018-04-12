<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/Application.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/selo-project/php/api-v1-0/library/defaultResponseJSON.php');

if (isset($_POST['userId']) && !empty($_POST['userId'])) {
    $app = Application::getInstance();
    $userId = filter_var($_POST['userId']);
    $channelId = (int)$_POST['channelId'];
    $shardDb = $app->getShardDb($userId, $channelId);
    try {
    if ($app->checkSessionKey($userId, $_POST['sessionKey'], $channelId)) {
        $m = md5($_POST['userId'].$app->md5Secret());
        if ($m != $_POST['hash']) {
            $json_data['id'] = 6;
            $json_data['status'] = 's397';
            $json_data['message'] = 'wrong hash';
            echo json_encode($json_data);
        } else {
            try {
                $result = $shardDb->query('UPDATE user_party SET friend_id ="'.$_POST["friendId"].'", friend_count ="'.$_POST["friendCount"].'", took_gift ="'.$_POST["tookGift"].'", count_resource ='.$_POST["countResource"].', show_window ='.$_POST["showWindow"].', id_party ='.$_POST["idPartyNew"].' WHERE user_id ='.$userId. ' AND id_party=' . $_POST['idPartyOld']);
                if (!$result) {
                    $json_data['id'] = 2;
                    $json_data['status'] = 's340';
                    throw new Exception("Bad request to DB!");
                }

                $json_data['message'] = '';
                echo json_encode($json_data);
            } catch (Exception $e) {
                $json_data['status'] = 's180';
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
catch (Exception $e)
{
    $json_data['status'] = 's098';
    $json_data['message'] = $e->getMessage();
    echo json_encode($json_data);
}
}
else
{
    $json_data['id'] = 1;
    $json_data['status'] = 's181';
    $json_data['message'] = 'bad POST[userId]';
    echo json_encode($json_data);
}
